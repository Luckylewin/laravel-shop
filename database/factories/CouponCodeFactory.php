<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\CouponCode::class, function (Faker $faker) {
    // 首先随机的取得一个类型
    $type = $faker->randomElement(array_keys(\App\Models\CouponCode::$typeMap));
    // 根据取的类型生成对应折扣
    $value = $type === \App\Models\CouponCode::TYPE_FIXED ? random_int(1, 200) : random_int(5, 50);

    if ($type === \App\Models\CouponCode::TYPE_FIXED) {
        $minAmount = $value + 0.01;
    } else {
        if (random_int(0, 100) < 50) {
            $minAmount = 0;
        } else {
            $minAmount = random_int(1000, 1000);
        }
    }

    return [
        'name'       => join(' ', $faker->words), // 随机生成名称
        'code'       => App\Models\CouponCode::findAvailableCode(), // 调用优惠码生成方法
        'type'       => $type,
        'value'      => $value,
        'total'      => 1000,
        'used'       => 0,
        'min_amount' => $minAmount,
        'not_before' => null,
        'not_after'  => null,
        'enabled'    => true,
    ];
});
