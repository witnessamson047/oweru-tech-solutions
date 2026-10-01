<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccessRequestTest extends TestCase
{
    public function test_register_page_explains_admin_review_without_creating_an_account(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Request an account');
        $response->assertSee('An administrator will review your request');
    }

    public function test_registration_request_is_sent_to_admin_review_not_granted_automatically(): void
    {
        Mail::fake();

        $response = $this->post(route('register.store'), [
            'name' => 'New Staff Member',
            'email' => 'new-staff@example.test',
            'phone' => '',
            'reason' => 'I need access to manage website scan enquiries for the team.',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('enquiries', [
            'email' => 'new-staff@example.test',
            'source' => 'contact',
            'business_name' => 'Admin access request',
        ]);
        $this->assertDatabaseMissing('users', ['email' => 'new-staff@example.test']);
    }
}