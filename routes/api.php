<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\TestQueueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GalleryImageController;
use App\Http\Controllers\DiscountCodeController;
use App\Http\Controllers\CommonQuestionController;
use App\Http\Controllers\OrderTimeLimitController;
use App\Http\Controllers\ContactUsContentController;
use App\Http\Controllers\ContactUsMessageController;
use App\Http\Controllers\NotificationFrameController;
use App\Http\Controllers\InformativeProductController;
use App\Http\Controllers\InformativeProductCategoryController;

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

Route::get('/rules', [RulesController::class, 'get']);

Route::get('/privacy', [PrivacyController::class, 'get']);

Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/login/otp', [UserController::class, 'login_with_OTP']);
Route::post('/user/forgot_password', [UserController::class, 'forgot_password']);

Route::get('/gallery_image', [GalleryImageController::class, 'get_all']);
Route::get('/gallery_image/{id}', [GalleryImageController::class, 'get_by_id']);

Route::get('/article', [ArticleController::class, 'get']);
Route::get('/article/{id}', [ArticleController::class, 'get_by_id']);

Route::get('/branch', [BranchController::class, 'get']);
Route::get('/branch/{id}', [BranchController::class, 'get_by_id']);

Route::get('/common_question', [CommonQuestionController::class, 'get']);
Route::get('/common_question/{id}', [CommonQuestionController::class, 'get_by_id']);

Route::get('/license', [LicenseController::class, 'get']);
Route::get('/license/{id}', [LicenseController::class, 'get_by_id']);

Route::get('/informative_product', [InformativeProductController::class, 'get']);
Route::get('/informative_product/{id}', [InformativeProductController::class, 'get_by_id']);

Route::get('/informative_product_category', [InformativeProductCategoryController::class, 'get']);
Route::get('/informative_product_category/{id}', [InformativeProductCategoryController::class, 'get_by_id']);

Route::group([
    'middleware' => 'auth:sanctum'
], function(){
    Route::get('/product', [ProductController::class, 'get']);
    Route::get('/product/{id}', [ProductController::class, 'get_by_id']);

    Route::group([
        'middleware' => 'AllowedUserClass:App\Models\User'
    ], function(){
        Route::put('/user/password', [UserController::class, 'change_password'])
            ->middleware(['CheckIfUserIsBlocked']);

        Route::group([
            'middleware' => [
                'CheckIfUserIsBlocked',
                'CheckIfShouldChangePassword',
            ]
        ], function(){
            Route::get('/user/info', [UserController::class, 'get_user_from_request']);
            Route::put('/user/info', [UserController::class, 'update_by_request']);

            Route::put('/user', [UserController::class, 'update_by_request']);

            Route::get('/user/cart', [CartController::class, 'get_cart_products']);
            Route::delete('/user/cart', [CartController::class, 'empty_the_cart']);
            Route::put('/user/cart/product/{id}', [CartController::class, 'store_or_update_cart_product']);
            Route::delete('/user/cart/product/{id}', [CartController::class, 'delete_cart_product']);

            Route::post('/user/order', [OrderController::class, 'store']);
            Route::get('/user/order', [OrderController::class, 'get_user_orders']);
            Route::get('/user/order/product_stats', [OrderController::class, 'get_user_product_stats']);

            Route::get('/user/notification', [NotificationController::class, 'get_user_notifications']);
            Route::post('/user/notification/{id}/seen', [NotificationController::class, 'seen_by_user']);
            Route::post('/user/notification/seen', [NotificationController::class, 'seen_user_notifications']);

            Route::get('/user/discount/check', [DiscountCodeController::class, 'check_if_user_can_use_the_discount']);
        });
    });

    Route::group([
        'middleware' => 'AllowedUserClass:App\Models\Admin'
    ], function(){
        Route::get('/admin/info', [AdminController::class, 'get_info']);

        Route::get('/order/product_stats', [OrderController::class, 'get_product_stats']);

        Route::get('/stats', [StatsController::class, 'get'])->middleware('RequiredPrivilege:get_stats');

        Route::group([
            'middleware' => 'RequiredPrivilege:get_users|manage_users|send_notifications|manage_discounts'
        ], function(){
            Route::get('/user', [UserController::class, 'get']);
            Route::get('/user/{id}', [UserController::class, 'get_by_id']);
        });

        Route::group([
            'middleware' => 'RequiredPrivilege:manage_users'
        ], function(){
            Route::post('/user', [UserController::class, 'add']);
            Route::put('/user/{id}', [UserController::class, 'update_by_id']);
            Route::put('/user/{id}/block', [UserController::class, 'block_user_by_id']);
            Route::put('/user/{id}/unblock', [UserController::class, 'unblock_user_by_id']);
        });

        Route::group([
            'middleware' => 'ShouldBePrimary'
        ], function(){
            Route::post('/admin/register', [AdminController::class, 'register']);
            Route::get('/admin', [AdminController::class, 'get_all']);
            Route::get('/admin/{id}', [AdminController::class, 'get_by_id']);
            Route::delete('/admin/{id}', [AdminController::class, 'delete']);
            Route::put('/admin/{id}', [AdminController::class, 'update']);

            Route::post('/test/queue', [TestQueueController::class, 'set_value']);
            Route::get('/test/queue', [TestQueueController::class, 'get_value']);
        });

        Route::group([
            'middleware' => 'RequiredPrivilege:manage_products'
        ], function(){
            Route::post('/product', [ProductController::class, 'store']);
            Route::delete('/product/{id}', [ProductController::class, 'delete_by_id']);
            Route::put('/product/{id}', [ProductController::class, 'edit_by_id']);
        });

        Route::group([
            'middleware' => 'RequiredPrivilege:manage_discounts'
        ], function(){
            Route::post('/discount', [DiscountCodeController::class, 'store']);
            Route::get('/discount', [DiscountCodeController::class, 'get']);
            Route::get('/discount/{id}', [DiscountCodeController::class, 'get_by_id']);
            Route::delete('/discount/{id}', [DiscountCodeController::class, 'delete_by_id']);
            Route::get('/discount/{id}/users', [DiscountCodeController::class, 'get_users']);
        });

        Route::group([
            'middleware' => 'RequiredPrivilege:send_notifications'
        ], function(){
            Route::post('/notification/frame', [NotificationFrameController::class, 'store']);
            Route::get('/notification/frame', [NotificationFrameController::class, 'get']);
            Route::get('/notification/frame/{id}', [NotificationFrameController::class, 'get_by_id']);
            Route::put('/notification/frame/{id}', [NotificationFrameController::class, 'update_by_id']);
            Route::delete('/notification/frame/{id}', [NotificationFrameController::class, 'delete_by_id']);

            Route::post('/notification', [NotificationController::class, 'send']);
            Route::get('/notification', [NotificationController::class, 'get']);
            Route::get('/notification/{id}', [NotificationController::class, 'get_by_id']);
            Route::delete('/notification/{id}', [NotificationController::class, 'delete_by_id']);
            Route::get('/notification/{id}/user', [NotificationController::class, 'get_users']);
            Route::get('/notification/user/{id}', [UserController::class, 'get_notifications_by_id']);
        });

        Route::group([
            'middleware' => 'RequiredPrivilege:get_todays_orders'
        ], function(){
            Route::get('/order/todays_orders', [OrderController::class, 'get_todays_orders']);
            Route::get('/order/todays_orders/{id}', [OrderController::class, 'get_todays_order_by_id']);
        });

        Route::get('/order/{id}', [OrderController::class, 'get_by_id'])->middleware('RequiredPrivilege:get_orders|get_todays_orders');

        Route::group([
            'middleware' => 'RequiredPrivilege:get_orders'
        ], function(){
            Route::get('/order', [OrderController::class, 'get']);
        });

        Route::group([
            'middleware' => 'RequiredPrivilege:manage_order_time_limit'
        ], function(){
            Route::get('/order_time_limit', [OrderTimeLimitController::class, 'get']);
            Route::get('/order_time_limit/available_groups', [OrderTimeLimitController::class, 'get_available_groups']);
            Route::put('/order_time_limit', [OrderTimeLimitController::class, 'update']);
        });

        Route::group([
            'middleware' => 'RequiredPrivilege:manage_settings'
        ], function(){
            Route::put('/about_us', [AboutUsController::class, 'update']);

            Route::put('/rules', [RulesController::class, 'update']);

            Route::put('/privacy', [PrivacyController::class, 'update']);

            Route::put('/contact_us_content', [ContactUsContentController::class, 'update']);

            Route::get('/contact_us_message', [ContactUsMessageController::class, 'get_all']);
            Route::delete('/contact_us_message/{id}', [ContactUsMessageController::class, 'delete_by_id']);

            Route::post('/gallery_image', [GalleryImageController::class, 'store']);
            Route::delete('/gallery_image/{id}', [GalleryImageController::class, 'delete']);
            Route::put('/gallery_image/{id}', [GalleryImageController::class, 'update']);

            Route::post('/branch', [BranchController::class, 'store']);
            Route::delete('/branch/{id}', [BranchController::class, 'delete_by_id']);
            Route::put('/branch/{id}', [BranchController::class, 'update']);

            Route::post('/common_question', [CommonQuestionController::class, 'store']);
            Route::put('/common_question/{id}', [CommonQuestionController::class, 'edit_by_id']);
            Route::delete('/common_question/{id}', [CommonQuestionController::class, 'delete_by_id']);

            Route::post('/article', [ArticleController::class, 'store']);
            Route::delete('/article/{id}', [ArticleController::class, 'delete_by_id']);
            Route::put('/article/{id}', [ArticleController::class, 'update_by_id']);

            Route::post('/informative_product', [InformativeProductController::class, 'store']);
            Route::put('/informative_product/{id}', [InformativeProductController::class, 'update_by_id']);
            Route::delete('/informative_product/{id}', [InformativeProductController::class, 'delete_by_id']);

            Route::post('/informative_product_category', [InformativeProductCategoryController::class, 'store']);
            Route::put('/informative_product_category/{id}', [InformativeProductCategoryController::class, 'update_by_id']);
            Route::delete('/informative_product_category/{id}', [InformativeProductCategoryController::class, 'delete_by_id']);

            Route::post('/license', [LicenseController::class, 'store']);
            Route::put('/license/{id}', [LicenseController::class, 'update_by_id']);
            Route::delete('/license/{id}', [LicenseController::class, 'delete_by_id']);
        });
    });
});
