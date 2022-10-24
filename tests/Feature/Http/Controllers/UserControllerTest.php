<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\UserSeeder;
use Database\Seeders\VehicleSeeder;
use App\Http\Resources\UserResource;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_function_returns_all_users()
    {
        $this->seed(UserSeeder::class);

        $response = $this->get(route('users.index'));

        $response->assertStatus(200);

        $users = UserResource::collection(User::all())->resolve();

        $response->assertExactJson([
            'users' => $users
        ]);
    }

    public function test_users_store_function_creates_a_user()
    {
        $response = $this->post(route('users.store'), $data = [
            'name'  => 'Chuck Norris',
            'email' => 'chuck.norris@punch.kick',
            'password' => 'password'
        ]);

        $response->assertSessionHasNoErrors([
            'name',
            'email',
            'password'
        ]);

        $this->assertEquals(1, User::count());

        $this->assertDatabaseHas(
            'users', [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password']
            ]
        );

        $user = User::latest()->first();

        $response->assertStatus(201);

        $response->assertExactJson([
            'message' => "{$user->name} has been saved.",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => ($user->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => ($user->updated_at)->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function test_users_show_function_returns_a_user()
    {
        $this->seed(UserSeeder::class);

        $user = User::first();

        $response = $this->get(route('users.show', $user));

        $response->assertStatus(200);

        $response->assertExactJson([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'vehicles' => $user->vehicles,
                'created_at' => ($user->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => ($user->updated_at)->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function test_users_update_function_updates_a_user()
    {
        $this->seed(UserSeeder::class);

        $firstUser = User::first();

        $response = $this->patch(route('users.update', $firstUser), $data = [
            'name'  => 'Chuck Norris',
            'email' => 'chuck.norris@punch.kick',
            'password' => 'password'
        ]);

        $response->assertSessionHasNoErrors([
            'name',
            'email',
            'password'
        ]);

        $this->assertDatabaseHas(
            'users', [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password']
            ]
        );

        $user = User::find($firstUser->id);

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => "{$user->name} has been updated.",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'vehicles' => $user->vehicles,
                'created_at' => ($user->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => ($user->updated_at)->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function test_users_destroy_function_deletes_a_user()
    {
        $this->seed(UserSeeder::class);

        $user = User::first();

        $response = $this->delete(route('users.destroy', $user));

        $this->assertDatabaseMissing(
            'users', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        );

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => "{$user->name} has been deleted."
        ]);
    }

    public function test_users_assign_vehicle_function_assigns_user_to_vehicle()
    {
        $this->seed([
            UserSeeder::class,
            VehicleSeeder::class
        ]);

        $user = User::first();
        $vehicle = Vehicle::first();

        $response = $this->post(route('users.assign_vehicle', [$user, $vehicle]));

        $this->assertDatabaseHas(
            'user_vehicle', [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => "{$user->name} has been assigned to drive {$user->assignedVehicle()->year} {$user->assignedVehicle()->make} {$user->assignedVehicle()->model}.",
            "user" => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'vehicles' => [[
                        'id' => $vehicle->id,
                        'make' => $vehicle->make,
                        'model' => $vehicle->model,
                        'year' => $vehicle->year,
                        'created_at' => ($vehicle->created_at)->format('Y-m-d H:i:s'),
                        'updated_at' => ($vehicle->updated_at)->format('Y-m-d H:i:s'),
                    ]
                ],
                'created_at' => ($user->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => ($user->updated_at)->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function test_users_assign_vehicle_function_to_assign_already_assigned_user()
    {
        $this->seed([
            UserSeeder::class,
            VehicleSeeder::class
        ]);

        $user = User::first();
        $vehicle = Vehicle::first();

        $response = $this->post(route('users.assign_vehicle', [$user, $vehicle]));

        $this->assertDatabaseHas(
            'user_vehicle', [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $differentVehicle = Vehicle::orderByDesc('id')->first();

        $response = $this->post(route('users.assign_vehicle', [$user, $differentVehicle]));

        $response->assertStatus(422);

        $response->assertExactJson([
            'message' => "{$user->name} has already been assigned to drive {$user->assignedVehicle()->year} {$user->assignedVehicle()->make} {$user->assignedVehicle()->model}.",
        ]);
    }

    public function test_users_assign_vehicle_function_to_assign_user_the_same_assigned_vehicle()
    {
        $this->seed([
            UserSeeder::class,
            VehicleSeeder::class
        ]);

        $user = User::first();
        $vehicle = Vehicle::first();

        $response = $this->post(route('users.assign_vehicle', [$user, $vehicle]));

        $this->assertDatabaseHas(
            'user_vehicle', [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response = $this->post(route('users.assign_vehicle', [$user, $vehicle]));

        $response->assertStatus(422);

        $response->assertExactJson([
            'message' => "{$user->name} is already assigned to drive {$user->assignedVehicle()->year} {$user->assignedVehicle()->make} {$user->assignedVehicle()->model}.",
        ]);
    }

    public function test_users_unassign_vehicle_function_unassigns_user_from_vehicle()
    {
        $this->seed([
            UserSeeder::class,
            VehicleSeeder::class
        ]);

        $user = User::first();
        $vehicle = Vehicle::first();
        $user->assignVehicle($vehicle);

        $this->assertDatabaseHas(
            'user_vehicle', [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response = $this->post(route('users.unassign_vehicle', [$user, $vehicle]));

        $this->assertDatabaseMissing(
            'user_vehicle', [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => "The vehicle: {$vehicle->year} {$vehicle->make} {$vehicle->model} has been unassigned from {$user->name}.",
        ]);
    }

    public function test_users_unassign_vehicle_function_to_unassign_the_unassigned_user_from_vehicle()
    {
        $this->seed([
            UserSeeder::class,
            VehicleSeeder::class
        ]);

        $user = User::first();
        $vehicle = Vehicle::first();

        $response = $this->post(route('users.unassign_vehicle', [$user, $vehicle]));

        $response->assertStatus(422);

        $response->assertExactJson([
            'message' => "{$user->name} does not have an assigned vehicle."
        ]);
    }

    public function test_users_unassign_vehicle_function_to_unassign_user_from_different_vehicle()
    {
        $this->seed([
            UserSeeder::class,
            VehicleSeeder::class
        ]);

        $user = User::first();
        $vehicle = Vehicle::first();
        $user->assignVehicle($vehicle);

        $this->assertDatabaseHas(
            'user_vehicle',
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $differentVehicle = Vehicle::orderByDesc('id')->first();

        $response = $this->post(route('users.unassign_vehicle', [$user, $differentVehicle]));

        $this->assertDatabaseHas(
            'user_vehicle',
            [
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id
            ]
        );

        $response->assertStatus(422);

        $response->assertExactJson([
            'message' => "The vehicle: {$differentVehicle->year} {$differentVehicle->make} {$differentVehicle->model} is not assigned to {$user->name}.",
        ]);
    }
}
