<?php

use App\Http\Controllers\AlRashidController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/routes',[AlRashidController::class,'getRoutes']);
Route::post('/routes',[AlRashidController::class,'createRoutes']);
Route::put('/routes/{id}',[AlRashidController::class,'updateRoutes']);
Route::delete('/routes/{id}',[AlRashidController::class,'deleteRoutes']);

Route::get('/lines',[AlRashidController::class,'getLines']);
Route::post('/lines',[AlRashidController::class,'createLines']);
Route::put('/lines/{id}',[AlRashidController::class,'updateLines']);
Route::delete('/lines/{id}',[AlRashidController::class,'deleteLine']);

Route::get('/places',[AlRashidController::class,'getPlacesPoints']);
Route::post('/places',[AlRashidController::class,'createPlacesPoints']);
Route::put('/places/{id}',[AlRashidController::class,'updatePlacesPoints']);
Route::delete('/places/{id}',[AlRashidController::class,'deletePlacesPoints']);


Route::get('/nearestLine',[AlRashidController::class, 'generateExpectedRoute']);