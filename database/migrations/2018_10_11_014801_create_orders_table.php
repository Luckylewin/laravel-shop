<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            // 订单流水
            $table->string('no')->unique();
            // 用户id
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // 收货地址
            $table->text('address');
            // 订单总金额
            $table->decimal('total_amount');
            // 订单备注
            $table->text('remark')->nullable();
            // 支付时间
            $table->dateTime('paid_at')->nullable();
            // 支付方式
            $table->string('payment_method')->nullable();
            // 支付平台订单号
            $table->string('payment_no')->nullable();
            // 退款状态
            $table->string('refund_status')->default(\App\Models\Order::REFUND_STATUS_PENDING);
            // 退款单号
            $table->string('refund_no')->nullable();
            // 订单是否已经关闭
            $table->boolean('closed')->default(false);
            // 订单是否已经评价
            $table->boolean('reviewed')->default(false);
            // 物流状态
            $table->string('ship_status')->default(\App\Models\Order::SHIP_STATUS_PENDING);
            // 物流数据
            $table->text('ship_data')->nullable();
            // 其他额外的数据
            $table->text('extra')->nullable();

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
        Schema::dropIfExists('orders');
    }
}
