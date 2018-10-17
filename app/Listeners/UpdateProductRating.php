<?php

namespace App\Listeners;

use DB;
use App\Events\OrderReviewed;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateProductRating implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderReviewed  $event
     * @return void
     */
    public function handle(OrderReviewed $event)
    {
        // 通过 with 方法解决N+1查询性能问题
        $items = $event->getOrder()->items()->with('product')->get();


        foreach ($items as $item) {
          $result = OrderItem::query()
                          ->where('product_id', $item->product_id)
                          ->WhereHas('order', function ($query) {
                              $query->whereNotNull('paid_at');
                          })
                          ->first([
                              DB::raw('count(*) as review_count'),
                              DB::raw('avg(rating) as rating')
                          ]);

          // 更新产品的评价分数 以及 评价总数
           $item->product->update([
               'review_count' => $result->review_count,
               'rating'       => $result->rating
           ]);
        }
    }
}
