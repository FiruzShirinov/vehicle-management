<?php

namespace Tests\Feature\Exceptions;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HandlerTest extends TestCase
{
    public function test_return_not_found_on_paths_that_do_not_exist()
    {
        $response = $this->getJson('/');

        $response->assertStatus(404);
    }
}
