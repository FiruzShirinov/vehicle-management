<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use App\Http\Resources\UserResource;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'users' => UserResource::collection(User::all()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        return response()->json([
            "message" => "{$request->name} has been saved.",
            "user" => new UserResource(User::create($request->validated())),
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $userId
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return response()->json([
            'user' => new UserResource($user->load('vehicles')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        return response()->json([
            "message" => "{$request->name} has been updated.",
            "user" => new UserResource($user->load('vehicles')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            "message" => "{$user->name} has been deleted.",
        ], 200);
    }

    /**
     * Assign to the specified resource a vehicle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function assignVehicle(User $user, Vehicle $vehicle)
    {
        if ($user->hasAssignedVehicle() && $vehicle->isNot($user->assignedVehicle())) {
            return response()->json([
                "message" => "{$user->name} has already been assigned to drive {$user->assignedVehicle()->year} {$user->assignedVehicle()->make} {$user->assignedVehicle()->model}.",
            ], 422);
        }
        if ($user->hasAssignedVehicle() && $vehicle->is($user->assignedVehicle())) {
            return response()->json([
                "message" => "{$user->name} is already assigned to drive {$user->assignedVehicle()->year} {$user->assignedVehicle()->make} {$user->assignedVehicle()->model}.",
            ], 422);
        }
        $user->assignVehicle($vehicle);
        return response()->json([
            "message" => "{$user->name} has been assigned to drive {$user->assignedVehicle()->year} {$user->assignedVehicle()->make} {$user->assignedVehicle()->model}.",
            "user" => new UserResource($user->load('vehicles')),
        ], 200);
    }

    /**
     * Unassign to the specified resource a vehicle.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function unassignVehicle(User $user, Vehicle $vehicle)
    {
        if (!$user->hasAssignedVehicle()) {
            return response()->json([
                "message" => "{$user->name} does not have an assigned vehicle."
            ], 422);
        }

        if ($user->hasAssignedVehicle() && $vehicle->isNot($user->assignedVehicle())) {
            return response()->json([
                "message" => "The vehicle: {$vehicle->year} {$vehicle->make} {$vehicle->model} is not assigned to {$user->name}.",
            ], 422);
        }

        $user->unassignVehicle($vehicle);
        return response()->json([
            "message" => "The vehicle: {$vehicle->year} {$vehicle->make} {$vehicle->model} has been unassigned from {$user->name}.",
        ], 200);
    }
}
