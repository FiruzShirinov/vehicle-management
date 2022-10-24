<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResources([
    'users' => UserController::class,
    'vehicles' => VehicleController::class,
]);

Route::post('users/{user}/assign-vehicle/{vehicle}', [UserController::class, 'assignVehicle'])->name('users.assign_vehicle');
Route::post('users/{user}/unassign-vehicle/{vehicle}', [UserController::class, 'unassignVehicle'])->name('users.unassign_vehicle');

Route::post('vehicles/{vehicle}/assign-user/{user}', [VehicleController::class, 'assignUser'])->name('vehicles.assign_user');
Route::post('vehicles/{vehicle}/unassign-user/{user}', [VehicleController::class, 'unassignUser'])->name('vehicles.unassign_user');
