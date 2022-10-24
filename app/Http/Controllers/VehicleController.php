<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use App\Http\Resources\VehicleResource;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'vehicles' => VehicleResource::collection(Vehicle::all()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreVehicleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVehicleRequest $request)
    {
        return response()->json([
            "message" => "{$request->year} {$request->make} {$request->model} has been saved.",
            "vehicle" => new VehicleResource(Vehicle::create($request->validated()))
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle)
    {
        return response()->json([
            'vehicle' => new VehicleResource($vehicle->load('users'))
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateVehicleRequest  $request
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $vehicle->update($request->validated());
        return response()->json([
            "message" => "{$request->year} {$request->make} {$request->model} has been updated.",
            "vehicle" => new VehicleResource($vehicle->load('users'))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return response()->json([
            "message" => "{$vehicle->year} {$vehicle->make} {$vehicle->model} has been deleted."
        ], 200);
    }

    /**
     * Assign to the specified resource a user.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function assignUser(Vehicle $vehicle, User $user)
    {
        if ($vehicle->isAssigned() && $user->isNot($vehicle->assignedUser())) {
            return response()->json([
                "message" => "{$vehicle->year} {$vehicle->make} {$vehicle->model} has already been assigned to {$vehicle->assignedUser()->name}."
            ], 422);
        }
        if ($vehicle->isAssigned() && $user->is($vehicle->assignedUser())) {
            return response()->json([
                "message" => "{$vehicle->year} {$vehicle->make} {$vehicle->model} is already assigned to {$user->name}."
            ], 422);
        }
        $vehicle->assignUser($user);
        return response()->json([
            "message" => "{$vehicle->year} {$vehicle->make} {$vehicle->model} has been assigned to {$vehicle->assignedUser()->name}.",
            "vehicle" => new VehicleResource($vehicle->load('users')),
        ], 200);
    }

    /**
     * Unassign to the specified resource a user.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function unassignUser(Vehicle $vehicle, User $user)
    {
        if (!$vehicle->isAssigned()) {
            return response()->json([
                "message" => "{$vehicle->year} {$vehicle->make} {$vehicle->model} does not have an assigned user."
            ], 422);
        }

        if ($vehicle->isAssigned() && $user->isNot($vehicle->assignedUser())) {
            return response()->json([
                "message" => "The vehicle: {$vehicle->year} {$vehicle->make} {$vehicle->model} is not assigned to {$user->name}."
            ], 422);
        }

        $vehicle->unassignUser($user);
        return response()->json([
            "message" => "The vehicle: {$vehicle->year} {$vehicle->make} {$vehicle->model} has been unassigned from {$user->name}."
        ], 200);
    }

}
