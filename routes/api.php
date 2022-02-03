<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\GalleryImageController;
use App\Http\Controllers\ContactUsContentController;
use App\Http\Controllers\ContactUsMessageController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DiscountCodeController;
use App\Http\Controllers\CommonQuestionController;
use App\Http\Controllers\OrderTimeLimitController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\NotificationFrameController;
use App\Http\Controllers\NotificationController;

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

Route::get('/contact_us_content', [ContactUsContentController::class, 'get']);

Route::post('/contact_us_message', [ContactUsMessageController::class, 'store']);

Route::group([
    'prefix' => '/gallery_image'
], function(){

    Route::get('/', [GalleryImageController::class, 'get_all']);
    Route::get('/{id}', [GalleryImageController::class, 'get_by_id']);

});

Route::group([
    'prefix' => '/article'
], function(){

    Route::get('/', [ArticleController::class, 'get']);
    Route::get('/{id}', [ArticleController::class, 'get_by_id']);

});

Route::group([
    'prefix' => '/branch'
], function(){

    Route::get('/', [BranchController::class, 'get']);
    Route::get('/{id}', [BranchController::class, 'get_by_id']);

});

Route::group([
    'prefix' => '/common_question'
], function (){

    Route::get('/', [CommonQuestionController::class, 'get']);
    Route::get('/{id}', [CommonQuestionController::class, 'get_by_id']);

});

Route::group([
    'prefix' => '/license'
], function (){

    Route::get('/', [LicenseController::class, 'get']);
    Route::get('/{id}', [LicenseController::class, 'get_by_id']);

});

Route::group([
    'middleware' => ['auth:sanctum']
], function(){

    Route::group([
        'middleware' => ['AllowedUserClass:App\Models\Admin']
    ], function(){

        Route::get('/admin/info', [AdminController::class, 'get_info']);

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

            });

        });

        Route::group([
            'middleware' => ['RequiredPrivilege:manage_guest_side']
        ], function(){

            Route::put('/about_us', [AboutUsController::class, 'update']);

            Route::put('/contact_us_content', [ContactUsContentController::class, 'update']);

            Route::group([
                'prefix' => 'gallery_image'
            ], function(){

                Route::post('/', [GalleryImageController::class, 'store']);
                Route::delete('/{id}', [GalleryImageController::class, 'delete']);
                Route::put('/{id}', [GalleryImageController::class, 'update']);

            });

            Route::group([
                'prefix' => '/contact_us_message'
            ], function (){

                Route::get('/', [ContactUsMessageController::class, 'get_all']);
                Route::delete('/{id}', [ContactUsMessageController::class, 'delete_by_id']);

            });

            Route::group([
                'prefix' => '/article'
            ], function(){

                Route::post('/', [ArticleController::class, 'store']);
                Route::delete('/{id}', [ArticleController::class, 'delete_by_id']);
                Route::put('/{id}', [ArticleController::class, 'update_by_id']);

            });

            Route::group([
                'prefix' => '/branch'
            ], function(){

                Route::post('/', [BranchController::class, 'store']);
                Route::delete('/{id}', [BranchController::class, 'delete_by_id']);
                Route::put('/{id}', [BranchController::class, 'update']);

            });

            Route::group([
                'prefix' => '/common_question',
            ], function (){

                Route::post('/', [CommonQuestionController::class, 'store']);
                Route::put('/{id}', [CommonQuestionController::class, 'edit_by_id']);
                Route::delete('/{id}', [CommonQuestionController::class, 'delete_by_id']);

            });

            Route::group([
                'prefix' => '/license'
            ], function (){

                Route::post('/', [LicenseController::class, 'store']);
                Route::put('/{id}', [LicenseController::class, 'update_by_id']);
                Route::delete('/{id}', [LicenseController::class, 'delete_by_id']);

            });

        });

        Route::group([
            'middleware' => ['RequiredPrivilege:send_notifications'],
            'prefix' => '/notification'
        ], function (){

            Route::group([
                'prefix' => '/frame'
            ], function (){

                Route::post('/', [NotificationFrameController::class, 'store']);
                Route::get('/', [NotificationFrameController::class, 'get']);
                Route::get('/{id}', [NotificationFrameController::class, 'get_by_id']);
                Route::put('/{id}', [NotificationFrameController::class, 'update_by_id']);
                Route::delete('/{id}', [NotificationFrameController::class, 'delete_by_id']);

            });

            Route::post('/', [NotificationController::class, 'send']);
            Route::get('/', [NotificationController::class, 'get']);
            Route::get('/{id}', [NotificationController::class, 'get_by_id']);
            Route::delete('/{id}', [NotificationController::class, 'delete_by_id']);

        });

        Route::group([
            'middleware' => ['RequiredPrivilege:manage_orders']
        ], function (){

            Route::group([
                'prefix' => '/order_time_limit'
            ], function (){

                Route::get('/', [OrderTimeLimitController::class, 'get']);
                Route::put('/', [OrderTimeLimitController::class, 'update']);

            });

        });

        Route::get('/user', [UserController::class, 'get'])
            ->middleware('RequiredPrivilege:manage_users|manage_discounts');

        Route::group([
            'middleware' => ['RequiredPrivilege:manage_users'],
            'prefix' => '/user'
        ], function(){

            Route::post('/', [UserController::class, 'add']);
            Route::put('/{id}', [UserController::class, 'update_by_id']);
            Route::put('/{id}/block', [UserController::class, 'block_user_by_id']);
            Route::put('/{id}/unblock', [UserController::class, 'unblock_user_by_id']);

        });

        Route::group([
            'middleware' => ['RequiredPrivilege:manage_products'],
            'prefix' => '/product'
        ], function(){

            Route::post('/', [ProductController::class, 'store']);
            Route::get('/', [ProductController::class, 'get']);
            Route::get('/{id}', [ProductController::class, 'get_by_id']);
            Route::delete('/{id}', [ProductController::class, 'delete_by_id']);
            Route::put('/{id}', [ProductController::class, 'edit_by_id']);

        });

        Route::group([
            'middleware' => ['RequiredPrivilege:manage_discounts'],
            'prefix' => '/discount'
        ], function(){

            Route::post('/', [DiscountCodeController::class, 'store']);
            Route::get('/', [DiscountCodeController::class, 'get']);
            Route::get('/{id}', [DiscountCodeController::class, 'get_by_id']);
            Route::delete('/{id}', [DiscountCodeController::class, 'delete_by_id']);
            Route::get('/{id}/users', [DiscountCodeController::class, 'get_users']);

        });

    });

});
