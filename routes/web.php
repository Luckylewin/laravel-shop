<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/products')->name('root');


Auth::routes();

Route::group(['middleware' => 'auth'], function() {
    // 邮件验证提醒
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
    // 邮件验证链接
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');
    // 手动发送邮件验证
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');

    // 收货地址
    Route::group(['middleware' => 'email_verified'], function() {
        // 收货地址列表
        Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
        // 新增收货地址
        Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
        // 编辑收货地址
        Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
        // 更新收货地址
        Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
        // 保存收货地址信息
        Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
        // 删除收货地址信息
        Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');

        // 收藏商品
        Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
        // 取消收藏
        Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
        // 收藏列表
        Route::get('products/favorites', 'ProductsController@favorites')->name('products.favorites');

        // 购物车列表
        Route::get('cart', 'CartController@index')->name('cart.index');
        // 放入购物车
        Route::post('cart', 'CartController@add')->name('cart.add');
        // 从购物车中移除
        Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');
        // 下单操作
        Route::post('orders', 'OrdersController@store')->name('orders.store');
        // 用户订单中心
        Route::get('orders', 'OrdersController@index')->name('orders.index');
        // 查看订单
        Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
    });

});

// 商品列表
Route::get('products', 'ProductsController@index')->name('products.index');

// 商品详情
Route::get('products/{product}', 'ProductsController@show')->name('products.show');

