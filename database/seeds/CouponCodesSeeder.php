<?php

use Illuminate\Database\Seeder;

// 优惠券填充器
class CouponCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\CouponCode::class)->create();
    }
}
