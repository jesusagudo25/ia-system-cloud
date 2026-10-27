<?php

namespace Tests\Feature;

use App\Http\Controllers\InvoiceController;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

// No RefreshDatabase or application migrations: only synthetic tables on SQLite
// :memory:. The renderer is stubbed; this tests real route/auth/binding, not Dompdf.
class InvoiceDownloadAuthenticationTest extends TestCase
{
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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        $renderer = Mockery::mock(InvoiceController::class)->makePartial();
        $renderer->shouldReceive('pdf')->andReturnUsing(function (Invoice $invoice) {
            return response('%PDF-1.4 synthetic fixture', 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice_' . $invoice->id . '.pdf"',
                'Cache-Control' => 'private, no-store',
            ]);
        });
        $this->app->instance(InvoiceController::class, $renderer);
    }

    private function user(string $name): User
    {
        return User::create([
            'full_name' => $name,
            'email' => strtolower($name) . '@example.invalid',
            'password' => bcrypt('synthetic-fixture-password'),
        ]);
    }

    public function test_anonymous_and_invalid_tokens_receive_json_401_even_accepting_pdf(): void
    {
        $invoice = Invoice::create(['user_id' => $this->user('AnonymousFixture')->id]);
        // Existing and missing IDs must both be rejected before model binding.
        foreach ([null, 'invalid-synthetic-token'] as $token) {
            $headers = ['Accept' => 'application/pdf'];
            if ($token) $headers['Authorization'] = 'Bearer ' . $token;
            foreach ([$invoice->id, 999] as $invoiceId) {
                $response = $this->get('/api/invoices/' . $invoiceId . '/download', $headers);
                $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
                $this->assertStringNotContainsString('%PDF-', $response->getContent());
            }
        }
    }

    public function test_revoked_token_is_rejected(): void
    {
        $user = $this->user('Revoked');
        $token = $user->createToken('fixture')->plainTextToken;
        $user->tokens()->delete();
        $this->get('/api/invoices/999/download', [
            'Accept' => 'text/html', 'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_owner_and_another_authenticated_user_can_download(): void
    {
        $owner = $this->user('Owner');
        $other = $this->user('Other');
        $invoice = Invoice::create(['user_id' => $owner->id]);
        foreach ([$owner, $other] as $user) {
            // Clear the guard's cached user between independent HTTP requests.
            $this->app['auth']->forgetGuards();
            $response = $this->get('/api/invoices/' . $invoice->id . '/download', [
                'Accept' => 'application/pdf',
                'Authorization' => 'Bearer ' . $user->createToken('fixture')->plainTextToken,
            ]);
            $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
            $this->assertStringContainsString('inline;', $response->headers->get('Content-Disposition'));
        }
    }

    public function test_authenticated_missing_invoice_is_404(): void
    {
        $token = $this->user('Missing')->createToken('fixture')->plainTextToken;
        $this->get('/api/invoices/999/download', [
            'Accept' => 'application/pdf', 'Authorization' => 'Bearer ' . $token,
        ])->assertNotFound();
    }
}
