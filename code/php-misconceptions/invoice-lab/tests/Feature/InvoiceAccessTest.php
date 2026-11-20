<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceAccessTest extends TestCase
{
    use RefreshDatabase;

    private const CUSTOMER_NAME = 'Design & "Review" <Partners>';

    private User $owner;

    private string $invoiceUrl;

    private string $customerNameMarker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $invoiceId = DB::table('invoices')->insertGetId([
            'user_id' => $this->owner->id,
            'customer_name' => self::CUSTOMER_NAME,
        ]);
        $this->invoiceUrl = '/invoice-lab/invoices/'.$invoiceId;
        $this->customerNameMarker = explode(' ', self::CUSTOMER_NAME, 2)[0];
    }

    public function test_owner_receives_escaped_html_that_must_not_be_stored(): void
    {
        $response = $this->actingAs($this->owner)->get($this->invoiceUrl);

        $response->assertOk()
            ->assertSee('Design &amp; &quot;Review&quot; &lt;Partners&gt;', false)
            ->assertDontSee(self::CUSTOMER_NAME, false);
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
    }

    public function test_other_customer_gets_the_same_error_as_for_a_missing_invoice(): void
    {
        config(['app.debug' => false]);
        $otherCustomer = User::factory()->create();
        $this->actingAs($otherCustomer);

        $missing = $this->getJson('/invoice-lab/invoices/999999999')->assertNotFound();
        $denied = $this->getJson($this->invoiceUrl)->assertNotFound();

        $denied->assertExactJson($missing->json());
        $denied->assertDontSee($this->customerNameMarker, false);
        $this->assertTrue($denied->headers->hasCacheControlDirective('no-store'));
    }

    public function test_owner_gets_not_found_for_a_malformed_id(): void
    {
        $this->actingAs($this->owner)->get('/invoice-lab/invoices/abc')
            ->assertNotFound()
            ->assertDontSee($this->customerNameMarker, false);
    }

    public function test_guest_browser_is_redirected_to_sign_in(): void
    {
        $this->get($this->invoiceUrl)
            ->assertStatus(302)
            ->assertRedirectToRoute('login')
            ->assertDontSee($this->customerNameMarker, false);
    }

    public function test_guest_asking_for_json_is_unauthenticated(): void
    {
        $this->getJson($this->invoiceUrl)
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.'])
            ->assertDontSee($this->customerNameMarker, false);
    }
}
