<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_redirects_to_login()
    {
        $response = $this->get('/');

        // Esperamos una redirección (302) a la página de login
        $response->assertStatus(302)
                ->assertRedirect('/login');
    }

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
