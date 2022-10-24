<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\UserSeeder;
use Database\Seeders\VehicleSeeder;
use App\Http\Resources\VehicleResource;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicles_index_function_returns_all_vehicles()
    {
        $this->seed(VehicleSeeder::class);

        $response = $this->get(route('vehicles.index'));

        $response->assertStatus(200);

        $vehicles = VehicleResource::collection(Vehicle::all())->resolve();

        $response->assertExactJson([
            'vehicles' => $vehicles
        ]);
    }

    public function test_vehicles_store_function_creates_a_vehicle()
    {
        $response = $this->post(route('vehicles.store'), $data = [
            'make'  => 'Toyota',
            'model' => 'Prius',
            'year' => 2022
        ]);

        $response->assertSessionHasNoErrors([
            'make',
            'model',
            'year'
        ]);

        $this->assertEquals(1, Vehicle::count());

        $this->assertDatabaseHas(
            'vehicles',
            [
                'make' => $data['make'],
                'model' => $data['model'],
                'year' => $data['year']
            ]
        );

        $vehicle = Vehicle::latest()->first();

        $response->assertStatus(201);

        $response->assertExactJson([
            'message' => "{$vehicle->year} {$vehicle->make} {$vehicle->model} has been saved.",
            'vehicle' => [
                'id' => $vehicle->id,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'created_at' => ($vehicle->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => ($vehicle->updated_at)->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function test_users_show_function_returns_a_user()
    {
        $this->seed(VehicleSeeder::class);

        $vehicle = Vehicle::first();

        $response = $this->get(route('vehicles.show', $vehicle));

        $response->assertStatus(200);

        $response->assertExactJson([
            'vehicle' => [
                'id' => $vehicle->id,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'users' => $vehicle->users,
                'created_at' => ($vehicle->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => ($vehicle->updated_at)->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function test_vehicles_update_function_updates_a_vehicle()
    {
        $this->seed(VehicleSeeder::class);

        $firstVehicle = Vehicle::first();

        $response = $this->patch(route('vehicles.update', $firstVehicle), $data = [
            'make'  => 'Toyota',
            'model' => 'Prius',
            'year' => 2022
        ]);

        $response->assertSessionHasNoErrors([
            'make',
            'model',
            'year'
        ]);

        $this->assertDatabaseHas(
            'vehicles',
            [
                'make' => $data['make'],
                'model' => $data['model'],
                'year' => $data['year']
            ]
        );

        $vehicle = Vehicle::find($firstVehicle->id);

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => "{$vehicle->year} {$vehicle->make} {$vehicle->model} has been updated.",
            'vehicle' => [
                'id' => $vehicle->id,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'users' => $vehicle->users,
                'created_at' => ($vehicle->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => ($vehicle->updated_at)->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function test_vehicles_destroy_function_deletes_a_vehicle()
    {
        $this->seed(VehicleSeeder::class);

        $vehicle = Vehicle::first();

        $response = $this->delete(route('vehicles.destroy', $vehicle));

        $this->assertDatabaseMissing(
            'vehicles',
            [
                'id' => $vehicle->id,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'created_at' => $vehicle->created_at,
                'updated_at' => $vehicle->updated_at
            ]
        );

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => "{$vehicle->year} {$vehicle->make} {$vehicle->model} has been deleted."
        ]);
    }

    public function test_vehicles_assign_user_function_assigns_vehicle_to_user()
    {
        $this->seed([
            VehicleSeeder::class,
            UserSeeder::class
        ]);

        $vehicle = Vehicle::first();
        $user = User::first();

        $response = $this->post(route('vehicles.assign_user', [$vehicle, $user]));

        $this->assertDatabaseHas(
            'user_vehicle',
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => "{$vehicle->year} {$vehicle->make} {$vehicle->model} has been assigned to {$vehicle->assignedUser()->name}.",
            "vehicle" => [
                'id' => $vehicle->id,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'users' => [
                    [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => ($user->created_at)->format('Y-m-d H:i:s'),
                        'updated_at' => ($user->updated_at)->format('Y-m-d H:i:s'),
                    ]
                ],
                'created_at' => ($vehicle->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => ($vehicle->updated_at)->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function test_vehicles_assign_user_function_to_assign_already_assigned_vehicle()
    {
        $this->seed([
            VehicleSeeder::class,
            UserSeeder::class
        ]);

        $vehicle = Vehicle::first();
        $user = User::first();

        $vehicle->users()->detach();

        $response = $this->post(route('vehicles.assign_user', [$vehicle, $user]));

        $this->assertDatabaseHas(
            'user_vehicle',
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $differentUser = User::orderByDesc('id')->first();

        $response = $this->post(route('vehicles.assign_user', [$vehicle, $differentUser]));

        $response->assertStatus(422);

        $response->assertExactJson([
            'message' => "{$vehicle->year} {$vehicle->make} {$vehicle->model} has already been assigned to {$vehicle->assignedUser()->name}."
        ]);
    }

    public function test_vehicles_assign_user_function_to_assign_vehicle_to_the_same_assigned_user()
    {
        $this->seed([
            VehicleSeeder::class,
            UserSeeder::class
        ]);

        $vehicle = Vehicle::first();
        $user = User::first();

        $vehicle->users()->detach();

        $response = $this->post(route('vehicles.assign_user', [$vehicle, $user]));

        $this->assertDatabaseHas(
            'user_vehicle',
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response = $this->post(route('vehicles.assign_user', [$vehicle, $user]));

        $response->assertStatus(422);

        $response->assertExactJson([
            'message' => "{$vehicle->year} {$vehicle->make} {$vehicle->model} is already assigned to {$user->name}."
        ]);
    }

    public function test_vehicles_unassign_user_function_unassigns_vehicle_from_user()
    {
        $this->seed([
            VehicleSeeder::class,
            UserSeeder::class
        ]);

        $vehicle = Vehicle::first();
        $user = User::first();
        $vehicle->assignUser($user);

        $this->assertDatabaseHas(
            'user_vehicle',
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response = $this->post(route('vehicles.unassign_user', [$vehicle, $user]));

        $this->assertDatabaseMissing(
            'user_vehicle',
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => "The vehicle: {$vehicle->year} {$vehicle->make} {$vehicle->model} has been unassigned from {$user->name}.",
        ]);
    }

    public function test_vehicles_unassign_user_function_to_unassign_the_unassigned_vehicle_from_user()
    {
        $this->seed([
            VehicleSeeder::class,
            UserSeeder::class
        ]);

        $vehicle = Vehicle::first();
        $user = User::first();

        $response = $this->post(route('vehicles.unassign_user', [$vehicle, $user]));

        $response->assertStatus(422);

        $response->assertExactJson([
            'message' => "{$vehicle->year} {$vehicle->make} {$vehicle->model} does not have an assigned user."
        ]);
    }

    public function test_vehicles_unassign_user_function_to_unassign_vehicle_from_different_user()
    {
        $this->seed([
            VehicleSeeder::class,
            UserSeeder::class
        ]);

        $vehicle = Vehicle::first();
        $user = User::first();
        $vehicle->assignUser($user);

        $this->assertDatabaseHas(
            'user_vehicle',
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $differentUser = User::orderByDesc('id')->first();

        $response = $this->post(route('vehicles.unassign_user', [$vehicle, $differentUser]));

        $this->assertDatabaseHas(
            'user_vehicle',
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response->assertStatus(422);

        $response->assertExactJson([
            'message' => "The vehicle: {$vehicle->year} {$vehicle->make} {$vehicle->model} is not assigned to {$differentUser->name}.",
        ]);
    }
}
