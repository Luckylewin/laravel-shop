<?php

use Illuminate\Database\Seeder;
use \App\Models\Order;
use \App\Models\ProductSku;
use \App\Models\OrderItem;
use \App\Models\Product;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 获取 Faker 实例
        $faker = app(Faker\Generator::class);
        // 创建 100 笔订单
        $orders = factory(Order::class, 100)->create();
        // 被购买的商品，用于后面更新商品销量和评分
        $products = collect([]);
        foreach ($orders as $order) {
            /**@var $order Order **/
            // 每笔随机 1- 3 个商品
            $items = factory(OrderItem::class, random_int(1, 3))->create([
                'order_id'    => $order->id,
                'rating'      => $order->reviewed ? random_int(1, 5) : null, // 随机评分 1 -5
                'review'      => $order->reviewed ? $faker->sentence : null,
                'reviewed_at' => $order->reviewed ? $faker->dateTimeBetween($order->paid_at) : null, // 评价时间晚于支付时间
            ]);

            // 计算总价
            $total = $items->sum(function (OrderItem $item) {
               return $item->price * $item->amount;
            });

            // 如果有优惠券，则计算优惠后的价格
            if ($order->couponCode) {
                $total = $order->couponCode->getAdjustPrice($total);
            }

            // 更新订单总价格
            $order->update([
                'total_amount' => $total
            ]);

            // 把这笔订单的商品放到 products 集合中
            $products->merge($items->pluck('prodcut'));
        }

        // 由于 seeder 执行不会触发事件 所以需要手动进行统计

        // 根据商品ID过滤掉重复的商品
        $products->unique('id')->each(function (Product $product) {
            // 查出该商品的销量，评分，评价数
            $result = OrderItem::query()
                        ->where('product_id', $product->id)
                        ->whereHas('order', function ($query) {
                            $query->whereNotNull('paid_at');
                        })
                        ->first([
                            DB::raw('count(*) as review_count'),
                            DB::raw('avg(rating) as rating'),
                            DB::raw('sum(amount) as sold_count')
                        ]);

            $product->update([
                'rating'       => $result->rating ? $result->rating : 5,
                'review_count' => $result->review_count,
                'sold_count'   => $result->sold_count
            ]);

        });

    }
}
