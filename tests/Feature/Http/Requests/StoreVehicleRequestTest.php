<?php

namespace Tests\Feature\Http\Requests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreVehicleRequestTest extends TestCase
{
    public function test_if_vehicle_make_is_required()
    {
        $response = $this->postJson(route('vehicles.store'), $data = [
            'model' => 'Prius',
            'year' => 2022
        ]);

        $this->assertDatabaseMissing(
            'vehicles', [
                'model' => $data['model'],
                'year' => $data['year']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('make');

        $response->assertExactJson([
            'message' => 'The make field is required.',
            'errors' => [
                'make' => [
                    'The make field is required.'
                ]
            ]
        ]);
    }

    public function test_if_vehicle_make_is_string()
    {
        $response = $this->postJson(route('vehicles.store'), $data = [
            'make'  => 1234,
            'model' => 'Prius',
            'year' => 2022
        ]);

        $this->assertDatabaseMissing(
            'vehicles', [
                'make' => $data['make'],
                'model' => $data['model'],
                'year' => $data['year']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('make');

        $response->assertExactJson([
            'message' => 'The make must be a string.',
            'errors' => [
                'make' => [
                    'The make must be a string.'
                ]
            ]
        ]);
    }

    public function test_if_vehicle_model_is_required()
    {
        $response = $this->postJson(route('vehicles.store'), $data = [
            'make' => 'Toyota',
            'year' => 2022
        ]);

        $this->assertDatabaseMissing(
            'vehicles', [
                'make' => $data['make'],
                'year' => $data['year']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('model');

        $response->assertExactJson([
            'message' => 'The model field is required.',
            'errors' => [
                'model' => [
                    'The model field is required.'
                ]
            ]
        ]);
    }

    public function test_if_vehicle_model_is_string()
    {
        $response = $this->postJson(route('vehicles.store'), $data = [
            'make'  => 'Toyota',
            'model' => 1234,
            'year' => 2022
        ]);

        $this->assertDatabaseMissing(
            'vehicles', [
                'make' => $data['make'],
                'model' => $data['model'],
                'year' => $data['year']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('model');

        $response->assertExactJson([
            'message' => 'The model must be a string.',
            'errors' => [
                'model' => [
                    'The model must be a string.'
                ]
            ]
        ]);
    }

    public function test_if_vehicle_year_is_required()
    {
        $response = $this->postJson(route('vehicles.store'), $data = [
            'make' => 'Toyota',
            'model' => 'Prius'
        ]);

        $this->assertDatabaseMissing(
            'vehicles', [
                'make' => $data['make'],
                'model' => $data['model']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('year');

        $response->assertExactJson([
            'message' => 'The year field is required.',
            'errors' => [
                'year' => [
                    'The year field is required.'
                ]
            ]
        ]);
    }

    public function test_if_vehicle_year_is_not_greater_current_year()
    {
        $response = $this->postJson(route('vehicles.store'), $data = [
            'make'  => 'Toyota',
            'model' => 'Prius',
            'year' => '2023'
        ]);

        $this->assertDatabaseMissing(
            'vehicles', [
                'make' => $data['make'],
                'model' => $data['model'],
                'year' => $data['year']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('year');

        $response->assertExactJson([
            'message' => 'The year must not be greater than '. now()->year .'.',
            'errors' => [
                'year' => [
                    'The year must not be greater than '. now()->year .'.',
                ]
            ]
        ]);
    }

    public function test_if_vehicle_year_is_at_least_1885()
    {
        $response = $this->postJson(route('vehicles.store'), $data = [
            'make'  => 'Toyota',
            'model' => 'Prius',
            'year' => '1800'
        ]);

        $this->assertDatabaseMissing(
            'vehicles', [
                'make' => $data['make'],
                'model' => $data['model'],
                'year' => $data['year']
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('year');

        $response->assertExactJson([
            'message' => 'The year must be at least 1885.',
            'errors' => [
                'year' => [
                    'The year must be at least 1885.',
                ]
            ]
        ]);
    }
}
