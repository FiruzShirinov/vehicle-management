<?php

namespace Tests\Feature\Http\Requests;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreUserRequestTest extends TestCase
{
    public function test_if_user_name_is_required()
    {
        $response = $this->postJson(route('users.store'), $data = [
            'email' => 'chuck.norris@punch.kick',
            'password' => 'password'
        ]);

        $this->assertDatabaseMissing(
            'users', [
                'email' => $data['email'],
                'password' => $data['password']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('name');

        $response->assertExactJson([
            'message' => "The name field is required.",
            'errors' => [
                'name' => [
                    'The name field is required.'
                ]
            ]
        ]);
    }

    public function test_if_user_name_is_string()
    {
        $response = $this->postJson(route('users.store'), $data = [
            'name'  => 1234,
            'email' => 'chuck.norris@punch.kick',
            'password' => 'password'
        ]);

        $this->assertDatabaseMissing(
            'users', [
                'email' => $data['email'],
                'password' => $data['password']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('name');

        $response->assertExactJson([
            'message' => "The name must be a string.",
            'errors' => [
                'name' => [
                    'The name must be a string.'
                ]
            ]
        ]);
    }

    public function test_if_user_name_min_is_3()
    {
        $response = $this->postJson(route('users.store'), $data = [
            'name'  => 'Yu',
            'email' => 'chuck.norris@punch.kick',
            'password' => 'password'
        ]);

        $this->assertDatabaseMissing(
            'users', [
                'email' => $data['email'],
                'password' => $data['password']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('name');

        $response->assertExactJson([
            'message' => "The name must be at least 3 characters.",
            'errors' => [
                'name' => [
                    'The name must be at least 3 characters.'
                ]
            ]
        ]);
    }

    public function test_if_user_email_is_required()
    {
        $response = $this->postJson(route('users.store'), $data = [
            'name'  => 'Chuck Norris',
            'password' => 'password'
        ]);

        $this->assertDatabaseMissing(
            'users', [
                'name' => $data['name'],
                'password' => $data['password']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('email');

        $response->assertExactJson([
            'message' => "The email field is required.",
            'errors' => [
                'email' => [
                    'The email field is required.'
                ]
            ]
        ]);
    }

    public function test_if_user_email_is_email()
    {
        $response = $this->postJson(route('users.store'), $data = [
            'name'  => 'Chuck Norris',
            'email' => 'chuck.norris',
            'password' => 'password'
        ]);

        $this->assertDatabaseMissing(
            'users', [
                'name' => $data['name'],
                'password' => $data['password']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('email');

        $response->assertExactJson([
            'message' => "The email must be a valid email address.",
            'errors' => [
                'email' => [
                    'The email must be a valid email address.'
                ]
            ]
        ]);
    }

    public function test_if_user_email_is_unique()
    {
        $this->seed(UserSeeder::class);

        $user = User::first();

        $response = $this->postJson(route('users.store'), $data = [
            'name'  => $user->name,
            'email' => $user->email,
            'password' => 'password'
        ]);

        $this->assertDatabaseMissing(
            'users', [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('email');

        $response->assertExactJson([
            'message' => "The email has already been taken.",
            'errors' => [
                'email' => [
                    'The email has already been taken.'
                ]
            ]
        ]);
    }

    public function test_if_user_password_is_required()
    {
        $response = $this->postJson(route('users.store'), $data = [
            'name'  => 'Chuck Norris',
            'email' => 'chuck.norris@punch.kick',
        ]);

        $this->assertDatabaseMissing(
            'users', [
                'name' => $data['name'],
                'email' => $data['email'],
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('password');

        $response->assertExactJson([
            'message' => "The password field is required.",
            'errors' => [
                'password' => [
                    'The password field is required.'
                ]
            ]
        ]);
    }

    public function test_if_user_password_is_string()
    {
        $response = $this->postJson(route('users.store'), $data = [
            'name'  => 'Chuck Norris',
            'email' => 'chuck.norris@punch.kick',
            'password' => 123456
        ]);

        $this->assertDatabaseMissing(
            'users', [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('password');

        $response->assertExactJson([
            'message' => "The password must be a string.",
            'errors' => [
                'password' => [
                    'The password must be a string.'
                ]
            ]
        ]);
    }

    public function test_if_user_password_min_is_6()
    {
        $response = $this->postJson(route('users.store'), $data = [
            'name'  => 'Chuck Norris',
            'email' => 'chuck.norris@punch.kick',
            'password' => 'passw'
        ]);

        $this->assertDatabaseMissing(
            'users', [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('password');

        $response->assertExactJson([
            'message' => "The password must be at least 6 characters.",
            'errors' => [
                'password' => [
                    'The password must be at least 6 characters.'
                ]
            ]
        ]);
    }
}
