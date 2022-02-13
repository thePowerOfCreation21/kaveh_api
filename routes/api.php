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
use App\Http\Controllers\InformativeProductController;
use App\Http\Controllers\TestQueueController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

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

Route::post('/admin/login', [AdminController::class, 'login']);

Route::get('/about_us', [AboutUsController::class, 'get']);

Route::get('/contact_us_content', [ContactUsContentController::class, 'get']);

Route::post('/contact_us_message', [ContactUsMessageController::class, 'store']);

Route::group([
    'prefix' => '/user'
], function (){

    Route::post('/login', [UserController::class, 'login']);
    Route::post('/login/otp', [UserController::class, 'login_with_OTP']);
    Route::post('/forgot_password', [UserController::class, 'forgot_password']);

});

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
    'prefix' => '/informative_product'
], function (){

    Route::get('/', [InformativeProductController::class, 'get']);
    Route::get('/{id}', [InformativeProductController::class, 'get_by_id']);

});

Route::group([
    'middleware' => ['auth:sanctum']
], function(){

    Route::group([
        'middleware' => ['AllowedUserClass:App\Models\User']
    ], function(){

        Route::group([
            'middleware' => [
                'CheckIfUserIsBlocked',
                'CheckIfShouldChangePassword',
            ]
        ], function(){

            Route::group([
                'prefix' => '/user'
            ], function(){

                Route::get('/info', [UserController::class, 'get_user_from_request']);

                Route::group([
                    'prefix' => '/cart'
                ], function (){

                    Route::get('/', [CartController::class, 'get_cart_products']);
                    Route::delete('/', [CartController::class, 'empty_the_cart']);
                    Route::put('/product/{id}', [CartController::class, 'store_or_update_cart_product']);
                    Route::delete('/product/{id}', [CartController::class, 'delete_cart_product']);

                });

                Route::group([
                    'prefix' => '/order'
                ], function(){

                    Route::post('/', [OrderController::class, 'store']);

                });

                Route::get('/discount/check', [DiscountCodeController::class, 'check_if_user_can_use_the_discount']);

            });


        });

        Route::put('/user/password', [UserController::class, 'change_password'])
            ->middleware(['CheckIfUserIsBlocked']);

    });

    Route::group([
        'middleware' => ['AllowedUserClass:App\Models\Admin']
    ],function()
    {

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

            Route::post('/test/queue', [TestQueueController::class, 'set_value']);
            Route::get('/test/queue', [TestQueueController::class, 'get_value']);

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

            Route::group([
                'prefix' => '/informative_product'
            ], function(){

                Route::post('/', [InformativeProductController::class, 'store']);
                Route::put('/{id}', [InformativeProductController::class, 'update_by_id']);
                Route::delete('/{id}', [InformativeProductController::class, 'delete_by_id']);

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
            Route::get('/{id}/user', [NotificationController::class, 'get_users']);
            Route::get('/user/{id}', [UserController::class, 'get_notifications_by_id']);

        });

        Route::group([
            'middleware' => ['RequiredPrivilege:manage_orders']
        ], function (){

            Route::group([
                'prefix' => '/order_time_limit'
            ], function (){

                Route::get('/', [OrderTimeLimitController::class, 'get']);
                Route::get('/available_groups', [OrderTimeLimitController::class, 'get_available_groups']);
                Route::put('/', [OrderTimeLimitController::class, 'update']);

            });

            Route::group([
                'prefix' => '/order'
            ], function(){

                Route::get('/', [OrderController::class, 'get']);
                Route::get('/{id}', [OrderController::class, 'get_by_id']);


            });

        });

        Route::group([
            'middleware' => ['RequiredPrivilege:manage_users|manage_discounts|send_notifications'],
            'prefix' => '/user'
        ], function (){

            Route::get('/', [UserController::class, 'get']);
            Route::get('/{id}', [UserController::class, 'get_by_id']);

        });

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

    Route::get('/product', [ProductController::class, 'get']);

});
