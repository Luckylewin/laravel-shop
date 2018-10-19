<?php

namespace App\Services;

use App\Exceptions\CouponCodeUnavailableException;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;

class OrderService
{
    public function store(User $user, UserAddress $address, $remark, $items, CouponCode $couponCode = null)
    {
        if ($couponCode) {
            $couponCode->checkAvailable();
        }
        // 开启一个数据事务
        $order = \DB::transaction(function() use ($user, $address, $remark, $items, $couponCode) {

            // 更新此地址的最后使用时间
            $address->update(['last_used' => Carbon::now()]);
            // 创建一个订单
            $order = new Order([
                'address' => [
                    // 将地址信息放入订单中
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone
                ],
                'remark' => $remark,
                'total_amount' => 0
            ]);
            // 订单关联到当前用户
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;

            // 遍历用户提交的 SKU
            foreach ($items as $data) {
                /**@var $sku ProductSku */
                $sku = ProductSku::find($data['sku_id']);
                // 快速创建一个 OrderItem 并直接与当前订单关联

                /**@var $item OrderItem */
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price
                ]);

                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);

                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($couponCode) {
                    $couponCode->checkAvailable($totalAmount);
                    $totalAmount = $couponCode->getAdjustPrice($totalAmount);
                    $order->couponCode()->associate($couponCode);
                    // 增加优惠券的用量 需要判断其返回值
                    if ($couponCode->stockIncrement() <= 0) {
                        throw new CouponCodeUnavailableException('该优惠券已被兑完');
                    }
                }

                // 处理库存数量
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('商品库存不足');
                }
            }

            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        // 订单加入延时删除任务
        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}