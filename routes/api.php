<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

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

Route::group([
    'prefix' => 'admin'
], function(){
    Route::post('login', [AdminController::class, 'login']);

    Route::group([
        'middleware' => ['auth:sanctum', 'AllowedUserClass:App\Models\Admin', 'NeededPrivilege:manage_admins']
    ], function(){
        Route::post('register', [AdminController::class, 'register']);
        Route::get('/', [AdminController::class, 'get_all']);
    });
});
