<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    /**
     * Test the home page loads successfully.
     */
    public function test_home_page_returns_200(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test the home page contains expected content.
     */
    public function test_home_page_has_title(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Oweru Tech Solutions');
    }

    /**
     * Test the enquiry page loads successfully.
     */
    public function test_enquiry_page_returns_200(): void
    {
        $response = $this->get('/enquiry');
        $response->assertStatus(200);
    }

    /**
     * Test the scanner page loads successfully.
     */
    public function test_scanner_page_returns_200(): void
    {
        $response = $this->get('/website-check');
        $response->assertStatus(200);
    }

    /**
     * Test the scanner page contains the scanner form.
     */
    public function test_scanner_page_has_form(): void
    {
        $response = $this->get('/website-check');
        $response->assertStatus(200);
        $response->assertSee('scanner-form');
        $response->assertSee('Scan Website');
    }

    /**
     * Test the enquiry page has the enquiry form.
     */
    public function test_enquiry_page_has_form(): void
    {
        $response = $this->get('/enquiry');
        $response->assertStatus(200);
        $response->assertSee('enquiry');
    }
}
