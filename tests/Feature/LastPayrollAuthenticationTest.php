<?php

namespace Tests\Feature;

use App\Http\Controllers\PayrollController;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PDF;
use Tests\TestCase;

// Real route/controller/Sanctum, synthetic SQLite :memory: only. No migrations.
class LastPayrollAuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'cache.default' => 'array', 'session.driver' => 'array',
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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('suffix_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('request_id')->nullable();
            $table->string('month');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('created');
            $table->decimal('total_amount', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    private function user(string $name): User
    {
        return User::create([
            'full_name' => $name, 'email' => strtolower($name) . '@example.invalid',
            'password' => bcrypt('synthetic-fixture-password'),
        ]);
    }

    private function payroll(User $owner, int $id, string $status = 'created', string $date = '2026-01-01'): void
    {
        DB::table('payrolls')->insert([
            'id' => $id, 'suffix_id' => 'SYNTHETIC-' . $id, 'user_id' => $owner->id,
            'request_id' => null, 'month' => 'Synthetic period', 'start_date' => $date,
            'end_date' => $date, 'status' => $status, 'total_amount' => '1.25',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
    }

    private function assertDenied(?string $token, string $accept): void
    {
        $this->app['auth']->forgetGuards();
        $headers = ['Accept' => $accept];
        if ($token !== null) $headers['Authorization'] = 'Bearer ' . $token;
        $response = $this->get('/api/last-payroll', $headers);
        $response->assertStatus(401)->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['message' => 'Unauthenticated.']);
        $this->assertFalse($response->headers->has('Location'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('total_amount', $response->getContent());
        $this->assertStringNotContainsString('SYNTHETIC-', $response->getContent());
    }

    private function assertUnauthenticatedCases(): void
    {
        $user = $this->user('Revoked');
        $revoked = $user->createToken('fixture')->plainTextToken;
        $user->tokens()->delete();
        foreach ([null, 'invalid-synthetic-token', $revoked] as $token) {
            foreach (['application/json', 'text/html', 'application/pdf'] as $accept) {
                $this->assertDenied($token, $accept);
            }
        }
    }

    public function test_no_payroll_is_disclosed_to_anonymous_invalid_or_revoked_tokens(): void
    {
        $this->payroll($this->user('Owner'), 1);
        $this->assertUnauthenticatedCases();
    }

    public function test_empty_table_still_requires_authentication_and_returns_json_401(): void
    {
        $this->assertSame(0, Payroll::count());
        $this->assertUnauthenticatedCases();
    }

    public function test_expired_token_is_rejected_under_test_only_expiration_policy(): void
    {
        config(['sanctum.expiration' => 1]);
        $user = $this->user('Expired');
        $this->payroll($user, 1);
        $token = $user->createToken('fixture');
        $token->accessToken->forceFill(['created_at' => now()->subMinutes(2)])->save();
        $this->assertDenied($token->plainTextToken, 'text/html');
    }

    public function test_owner_and_another_user_receive_same_global_selection_without_writes_or_pdf(): void
    {
        $owner = $this->user('Owner');
        $other = $this->user('Other');
        $this->payroll($owner, 1, 'created', '2030-01-01');
        $this->payroll($owner, 2, 'created', '2020-01-01');
        $this->payroll($owner, 3, 'cancelled', '2040-01-01');
        $before = Payroll::orderBy('id')->get()->toArray();
        $expected = Payroll::findOrFail(2)->toArray();
        Storage::fake('reports');
        Storage::fake('public');
        PDF::shouldReceive('loadView')->never();

        foreach ([$owner, $other] as $user) {
            $this->app['auth']->forgetGuards();
            $response = $this->get('/api/last-payroll', [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $user->createToken('fixture')->plainTextToken,
            ]);
            $response->assertOk()->assertHeader('Content-Type', 'application/json')->assertExactJson($expected);
            $this->assertSame(2, $response->json('id'));
            $this->assertSame($owner->id, $response->json('user_id'));
        }
        $this->assertSame($before, Payroll::orderBy('id')->get()->toArray());
        Storage::disk('reports')->assertDirectoryEmpty('');
        Storage::disk('public')->assertDirectoryEmpty('');
    }

    public function test_authenticated_empty_and_all_cancelled_keep_unmodified_controller_response(): void
    {
        $user = $this->user('Empty');
        $token = $user->createToken('fixture')->plainTextToken;
        foreach ([false, true] as $cancelled) {
            if ($cancelled) $this->payroll($user, 1, 'cancelled');
            // Characterize the unchanged controller using Laravel's own response conversion,
            // without guessing whether a null action return serializes as JSON null or empty.
            $result = app(PayrollController::class)->lastPayroll();
            $this->assertNull($result);
            $baseline = Router::toResponse(Request::create('/api/last-payroll'), $result);
            $this->app['auth']->forgetGuards();
            $response = $this->get('/api/last-payroll', [
                'Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token,
            ]);
            $response->assertStatus($baseline->getStatusCode());
            $this->assertSame($baseline->getContent(), $response->getContent());
            $this->assertSame($baseline->headers->get('Content-Type'), $response->headers->get('Content-Type'));
        }
    }

    public function test_registered_route_has_sanctum_and_explicit_json_error_name(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/api/last-payroll', 'GET'));
        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
        $this->assertSame('payrolls.last', $route->getName());
        $this->assertSame(PayrollController::class . '@lastPayroll', $route->getActionName());
    }
}
