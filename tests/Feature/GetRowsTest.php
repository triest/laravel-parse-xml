<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetRowsTest extends TestCase
{
    protected function basicAuthenticate($email, $password): self
    {
        return $this->withHeaders([
            'Authorization' => 'Basic '. base64_encode("{$email}:{$password}")
        ]);
    }

    /**
     * A basic feature test example.
     */
    public function test_get_rows_not_auth(): void
    {
        $response = $this->get('/api/data');

        $response->assertStatus(401);
    }

    public function test_get_rows_auth(): void
    {
        $response = $this->basicAuthenticate('test@example.com','password')->get('/api/data');

        $response->assertStatus(200);
    }
}
