<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PrivateReportStorage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PDF;
use Tests\TestCase;

// Real routes, Sanctum, report generation queries and private storage service.
// SQLite :memory: and fake disks only. Only Dompdf rendering is replaced.
class ReportDownloadAuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'cache.default' => 'array', 'session.driver' => 'array', 'reports.retention_hours' => 24,
        ]);
        DB::purge('sqlite');
        Storage::fake('reports');
        Storage::fake('public');
        Log::spy(); // The existing annual report logs SQL; synthetic tests need no log writes.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
        foreach (['interpreters', 'coordinators'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('full_name');
                $table->integer('status')->default(1);
                $table->integer('lenguage_id')->nullable();
                foreach (['ssn', 'address', 'city', 'state', 'zip_code'] as $column) {
                    $table->string($column)->nullable();
                }
            });
        }
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
        });
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('interpreter_id');
            $table->integer('coordinator_id')->nullable();
            $table->integer('payroll_id');
        });
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_id');
            $table->decimal('total_interpreter')->default(0);
            $table->decimal('total_coordinator')->default(0);
        });
        foreach (range(1, 4) as $id) {
            DB::table('reports')->insert(['id' => $id, 'title' => 'Synthetic report ' . $id]);
        }
        foreach (['Alpha', 'Beta'] as $name) {
            DB::table('interpreters')->insert([
                'full_name' => 'Synthetic ' . $name, 'lenguage_id' => 1, 'status' => 1,
            ]);
        }
        PDF::shouldReceive('loadView')->andReturnUsing(function ($view, $data) {
            $pdf = Mockery::mock();
            $pdf->shouldReceive('setPaper')->andReturnSelf();
            $pdf->shouldReceive('output')->andReturn('%PDF-1.4 synthetic ' . $view . ' ' . json_encode($data));
            return $pdf;
        });
    }

    private function user(string $name): User
    {
        return User::create([
            'full_name' => $name, 'email' => $name . '@example.invalid',
            'password' => bcrypt('synthetic-fixture-password'),
        ]);
    }

    private function headers(User $user): array
    {
        $this->app['auth']->forgetGuards();
        return ['Accept' => 'application/pdf', 'Authorization' => 'Bearer ' . $user->createToken('fixture')->plainTextToken];
    }

    private function generate(User $user, int $type = 1, string $name = 'Alpha'): array
    {
        return $this->postJson('/api/reports/generate', [
            'report_id' => $type, 'user_id' => $user->id,
            'filters' => [
                'start_date' => '2026-01-01', 'end_date' => '2026-02-28',
                'type_of_person' => 'Interpreter', 'name_of_interpreter' => $name, 'language_id' => 1,
            ],
        ], $this->headers($user))->assertOk()->assertJson(['status' => 'success'])
            ->assertJsonStructure(['generation_id', 'file_name', 'file_path', 'expires_at'])->json();
    }

    public function test_anonymous_invalid_and_revoked_tokens_receive_json_401_not_pdf(): void
    {
        $user = $this->user('Revoked');
        $revoked = $user->createToken('fixture')->plainTextToken;
        $user->tokens()->delete();
        $file = app(PrivateReportStorage::class)->store('%PDF-1.4 synthetic');
        foreach ([null, 'invalid-synthetic-token', $revoked] as $token) {
            foreach (['application/pdf', 'text/html'] as $accept) {
                $this->app['auth']->forgetGuards();
                $headers = ['Accept' => $accept];
                if ($token) $headers['Authorization'] = 'Bearer ' . $token;
                $response = $this->get($file['file_path'], $headers);
                $response->assertStatus(401)->assertHeader('Content-Type', 'application/json')
                    ->assertJson(['message' => 'Unauthenticated.']);
                $this->assertStringNotContainsString('%PDF-', $response->getContent());
            }
        }
        $this->app['auth']->forgetGuards();
        $this->postJson('/api/reports/generate', [])->assertUnauthorized();
    }

    public function test_all_four_report_types_allow_another_user_and_repeated_downloads(): void
    {
        $owner = $this->user('Owner');
        $other = $this->user('Other');
        foreach (range(1, 4) as $type) {
            $file = $this->generate($owner, $type);
            $this->assertMatchesRegularExpression('/\A' . PrivateReportStorage::ID_PATTERN . '\z/', $file['generation_id']);
            foreach ([$owner, $other, $other] as $reader) {
                $response = $this->get($file['file_path'], $this->headers($reader));
                $response->assertOk()->assertHeader('Content-Type', 'application/pdf')
                    ->assertHeader('X-Content-Type-Options', 'nosniff');
                $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
                $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
                $this->assertStringStartsWith('%PDF-', $response->getContent());
                $this->assertStringContainsString('Synthetic Alpha', $response->getContent());
            }
            Storage::disk('reports')->assertExists($file['file_name']);
            Storage::disk('public')->assertDirectoryEmpty('');
            $this->assertFileDoesNotExist(storage_path('app/public/reports/' . $file['file_name']));
            $this->get('/storage/reports/' . $file['file_name'])->assertNotFound();
        }
    }

    public function test_same_dates_different_filters_and_duplicate_generation_never_overwrite(): void
    {
        $owner = $this->user('Owner');
        $files = [$this->generate($owner, 1, 'Alpha'), $this->generate($owner, 1, 'Beta'), $this->generate($owner, 1, 'Alpha')];
        $this->assertCount(3, array_unique(array_column($files, 'generation_id')));
        $disk = Storage::disk('reports');
        $alpha = $disk->get($files[0]['file_name']);
        $beta = $disk->get($files[1]['file_name']);
        $this->assertStringContainsString('Synthetic Alpha', $alpha);
        $this->assertStringNotContainsString('Synthetic Beta', $alpha);
        $this->assertStringContainsString('Synthetic Beta', $beta);
        $this->assertNotSame($alpha, $beta);
        $this->assertSame($alpha, $disk->get($files[2]['file_name']));
        $this->assertCount(3, $disk->files());
    }

    public function test_missing_malformed_encoded_and_non_pdf_identifiers_are_safe(): void
    {
        $user = $this->user('Invalid');
        $id = '00000000-0000-4000-8000-000000000000';
        foreach ([$id, 'arbitrary.pdf', '..', '%2e%2e', '%252e%252e', 'C:%5csecret.pdf', $id . '.pdf', $id . '.txt'] as $value) {
            $response = $this->get('/api/reports/' . $value . '/download', $this->headers($user));
            $response->assertNotFound();
            $this->assertStringNotContainsString('%PDF-', $response->getContent());
            $this->assertStringNotContainsString(storage_path(), $response->getContent());
        }
        $store = app(PrivateReportStorage::class);
        foreach (['../secret.pdf', '/absolute.pdf', '%2e%2e%2fsecret.pdf', '..\\secret.pdf'] as $value) {
            $this->assertNull($store->read($value));
        }
    }

    public function test_retention_boundary_cleanup_and_historical_isolation(): void
    {
        $this->travelTo(now()->startOfSecond());
        $store = app(PrivateReportStorage::class);
        $file = $store->store('%PDF-1.4 expiring synthetic');
        $disk = Storage::disk('reports');
        $disk->put('unmanaged.pdf', 'sentinel');
        $disk->put('nested/00000000-0000-4000-8000-000000000000.pdf', 'sentinel');
        Storage::disk('public')->put('reports/historical.pdf', 'historical sentinel');
        $orphan = '00000000-0000-4000-8000-000000000001.tmp';
        $disk->put($orphan, 'partial synthetic');
        touch($disk->path($orphan), now()->getTimestamp());
        $this->travel(24 * 3600 - 1)->seconds();
        $this->assertSame('%PDF-1.4 expiring synthetic', $store->read($file['generation_id']));
        $this->assertSame(0, $store->prune());
        $this->travel(1)->seconds();
        $this->assertNull($store->read($file['generation_id']));
        $response = $this->get($file['file_path'], $this->headers($this->user('Expired')));
        $response->assertNotFound()->assertHeader('Content-Type', 'application/json');
        $this->artisan('reports:prune')->assertSuccessful();
        $disk->assertMissing([$file['file_name'], $orphan]);
        $disk->assertExists(['unmanaged.pdf', 'nested/00000000-0000-4000-8000-000000000000.pdf']);
        Storage::disk('public')->assertExists('reports/historical.pdf');
        $this->assertSame(0, $store->prune());
        $this->travelBack();
    }

    public function test_corrupt_pdf_and_storage_failures_return_controlled_errors(): void
    {
        $file = app(PrivateReportStorage::class)->store('%PDF-1.4 synthetic');
        Storage::disk('reports')->put($file['file_name'], '{"success":false}');
        $this->get($file['file_path'], $this->headers($this->user('Corrupt')))
            ->assertNotFound()->assertHeader('Content-Type', 'application/json');
        $store = Mockery::mock(PrivateReportStorage::class);
        $store->shouldReceive('read')->andThrow(new \RuntimeException('private-path-secret'));
        $store->shouldReceive('store')->andThrow(new \RuntimeException('private-path-secret'));
        $this->app->instance(PrivateReportStorage::class, $store);
        $response = $this->get($file['file_path'], $this->headers($this->user('Unavailable')));
        $response->assertStatus(503)->assertHeader('Content-Type', 'application/json');
        $this->assertStringNotContainsString('private-path-secret', $response->getContent());
        $user = $this->user('WriteFailure');
        $response = $this->postJson('/api/reports/generate', [
            'report_id' => 1, 'user_id' => $user->id,
            'filters' => ['start_date' => '2026-01-01', 'end_date' => '2026-02-28', 'type_of_person' => 'Interpreter'],
        ], $this->headers($user));
        $response->assertStatus(500)->assertJson(['status' => 'error']);
        $this->assertStringNotContainsString('private-path-secret', $response->getContent());
    }

    public function test_retention_is_configurable_and_invalid_values_fail_to_safe_default(): void
    {
        $store = app(PrivateReportStorage::class);
        config(['reports.retention_hours' => 2]);
        $this->assertSame(7200, $store->retentionSeconds());
        foreach ([0, -1, 'invalid', null] as $value) {
            config(['reports.retention_hours' => $value]);
            $this->assertSame(86400, $store->retentionSeconds());
        }
    }

    public function test_file_symlinks_cannot_escape_the_private_directory(): void
    {
        $id = '00000000-0000-4000-8000-000000000000';
        Storage::disk('public')->put('reports/synthetic.pdf', '%PDF-1.4 outside sentinel');
        $target = Storage::disk('public')->path('reports/synthetic.pdf');
        $link = Storage::disk('reports')->path($id . '.pdf');
        if (!@symlink($target, $link)) {
            $this->markTestSkipped('Creating symlinks is unavailable on this test platform.');
        }
        try {
            $this->assertNull(app(PrivateReportStorage::class)->read($id));
            $this->assertSame('%PDF-1.4 outside sentinel', file_get_contents($target));
        } finally {
            unlink($link); // Synthetic link only; never the target.
        }
    }
}
