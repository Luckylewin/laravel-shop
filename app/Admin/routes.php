<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('users', 'UsersController@index');
    $router->get('products', 'ProductsController@index');
    $router->get('products/create', 'ProductsController@create');
    $router->get('products/{id}/edit', 'ProductsController@edit');
    $router->put('products/{id}', 'ProductsController@update');
    $router->post('products', 'ProductsController@store');

    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');
    // 订单详情
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');
    // 订单发货处理
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');
    // 审核订单退款处理
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund');

    // 优惠券首页
    $router->get('coupon_codes', 'CouponCodesController@index');
    // 新增优惠券
    $router->get('coupon_codes/create', 'CouponCodesController@create');
    // 编辑优惠券
    $router->get('coupon_codes/{id}/edit', 'CouponCodesController@edit');
    // 更新优惠券
    $router->put('coupon_codes/{id}', 'CouponCodesController@update');
    // 新增优惠券
    $router->post('coupon_codes', 'CouponCodesController@store');
    // 删除优惠券
    $router->delete('coupon_codes/{id}', 'CouponCodesController@destroy');
});
