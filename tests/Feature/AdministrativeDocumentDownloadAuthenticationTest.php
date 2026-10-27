<?php

namespace Tests\Feature;

use App\Http\Controllers\BankCheckController;
use App\Http\Controllers\BankCheckPreviewController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\RequestController;
use App\Models\Payroll;
use App\Models\Request as DocumentRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

// Synthetic SQLite :memory: only; no application migrations or real documents.
// Real Sanctum tokens and route binding; PDF renderers stubbed except date errors.
class AdministrativeDocumentDownloadAuthenticationTest extends TestCase
{
    private const DOCUMENTS = [
        'payrolls' => [Payroll::class, PayrollController::class],
        'bank-checks' => [Payroll::class, BankCheckController::class],
        'requests' => [DocumentRequest::class, RequestController::class],
        'bank-checks-preview' => [DocumentRequest::class, BankCheckPreviewController::class],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'cache.default' => 'array',
            'session.driver' => 'array',
        ]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        foreach (['payrolls', 'requests'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->timestamps();
            });
        }
        foreach (self::DOCUMENTS as $route => [$model, $controller]) {
            $renderer = Mockery::mock($controller)->makePartial();
            $renderer->shouldReceive('pdf')->andReturnUsing(function ($document) use ($model, $route) {
                $this->assertInstanceOf($model, $document);
                return response('%PDF-1.4 synthetic ' . $route . '/' . $document->id, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="fixture.pdf"',
                ]);
            });
            $this->app->instance($controller, $renderer);
        }
    }

    private function user(string $name): User
    {
        return User::create([
            'full_name' => $name,
            'email' => strtolower($name) . '@example.invalid',
            'password' => bcrypt('synthetic-fixture-password'),
        ]);
    }

    public function test_anonymous_invalid_and_revoked_tokens_receive_json_401_before_binding(): void
    {
        $owner = $this->user('Owner');
        $revoked = $owner->createToken('fixture')->plainTextToken;
        $owner->tokens()->delete();
        foreach (self::DOCUMENTS as $route => [$model]) {
            $document = $model::create(['user_id' => $owner->id]);
            foreach ([null, 'invalid-synthetic-token', $revoked] as $token) {
                foreach (['application/pdf', 'text/html'] as $accept) {
                    foreach ([$document->id, 999] as $id) {
                        $this->app['auth']->forgetGuards();
                        $headers = ['Accept' => $accept];
                        if ($token) $headers['Authorization'] = 'Bearer ' . $token;
                        $response = $this->get('/api/' . $route . '/' . $id . '/download', $headers);
                        $response->assertStatus(401)
                            ->assertHeader('Content-Type', 'application/json')
                            ->assertJson(['message' => 'Unauthenticated.']);
                        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
                        $this->assertStringNotContainsString('%PDF-', $response->getContent());
                    }
                }
            }
        }
    }

    public function test_owner_and_another_authenticated_user_can_download_all_four_documents(): void
    {
        $owner = $this->user('Owner');
        $other = $this->user('Other');
        foreach (self::DOCUMENTS as $route => [$model]) {
            $document = $model::create(['user_id' => $owner->id]);
            foreach ([$owner, $other] as $user) {
                $this->app['auth']->forgetGuards();
                $response = $this->get('/api/' . $route . '/' . $document->id . '/download', [
                    'Accept' => 'application/pdf',
                    'Authorization' => 'Bearer ' . $user->createToken('fixture')->plainTextToken,
                ]);
                $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
                $this->assertSame('%PDF-1.4 synthetic ' . $route . '/' . $document->id, $response->getContent());
                $this->assertStringContainsString('inline;', $response->headers->get('Content-Disposition'));
            }
        }
    }

    public function test_authenticated_missing_documents_are_404(): void
    {
        $token = $this->user('Missing')->createToken('fixture')->plainTextToken;
        foreach (self::DOCUMENTS as $route => $definition) {
            $this->app['auth']->forgetGuards();
            $this->get('/api/' . $route . '/999/download', [
                'Accept' => 'application/pdf', 'Authorization' => 'Bearer ' . $token,
            ])->assertNotFound();
        }
    }

    public function test_real_date_range_errors_keep_the_existing_201_json_contract(): void
    {
        $user = $this->user('DateRange');
        $token = $user->createToken('fixture')->plainTextToken;
        foreach (['payrolls', 'requests'] as $route) {
            [$model, $controller] = self::DOCUMENTS[$route];
            // Use the real controller: this branch returns before querying invoices/rendering.
            $this->app->forgetInstance($controller);
            $document = $model::create([
                'user_id' => $user->id, 'start_date' => '2026-01-01', 'end_date' => '2026-02-28',
            ]);
            $this->app['auth']->forgetGuards();
            $response = $this->get('/api/' . $route . '/' . $document->id . '/download', [
                'Accept' => 'application/pdf', 'Authorization' => 'Bearer ' . $token,
            ]);
            $response->assertStatus(201)->assertHeader('Content-Type', 'application/json')
                ->assertJson(['success' => false]);
            $this->assertStringNotContainsString('%PDF-', $response->getContent());
        }
    }
}
