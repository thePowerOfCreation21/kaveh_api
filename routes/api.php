<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AboutUsController;

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

Route::post('admin/login', [AdminController::class, 'login']);

Route::get('/about_us', [AboutUsController::class, 'get']);

Route::group([
    'middleware' => ['auth:sanctum']
], function(){

    Route::group([
        'middleware' => ['AllowedUserClass:App\Models\Admin']
    ], function(){

        Route::group([
            'middleware' => ['ShouldBePrimary']
        ], function(){

            Route::group([
                'prefix' => 'admin'
            ], function(){
                Route::post('register', [AdminController::class, 'register']);
                Route::get('/', [AdminController::class, 'get_all']);
                Route::get('/{id}', [AdminController::class, 'get_by_id']);
                Route::delete('/{id}', [AdminController::class, 'delete']);
                Route::put('/{id}', [AdminController::class, 'update']);
                Route::post('/{id}/privileges', [AdminController::class, 'add_privileges']);
                Route::delete('/{id}/privileges', [AdminController::class, 'delete_privileges']);
            });

        });

        Route::group([
            'middleware' => ['RequiredPrivilege:manage_guest_side']
        ], function(){

            Route::group([
                'prefix' => 'about_us'
            ], function(){

                Route::put('/', [AboutUsController::class, 'update']);

            });

        });

    });

});
