<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_codes', function (Blueprint $table) {
            $table->increments('id');
            // 优惠券标题
            $table->string('name');
            // 优惠码
            $table->string('code')->unique();
            // 优惠券类型 支持固定金额和百分比折扣
            $table->string('type');
            // 折扣值
            $table->decimal('value');
            // 全站可兑换的数量
            $table->unsignedInteger('total');
            // 当前已经兑换的数量
            $table->unsignedInteger('used')->default(0);
            // 使用该优惠券最低的订单金额
            $table->decimal('min_amount', 10, 2);
            // 在此时间之前不可用
            $table->datetime('not_before')->nullable();
            // 在此时间之后不可用
            $table->datetime('not_after')->nullable();
            // 优惠券是否生效
            $table->boolean('enabled');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_codes');
    }
}
