<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ProductSku;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * 关闭订单延时任务(QUEUE_DRIVER=redis)
 * Class CloseOrder
 * @package App\Jobs
 */
class CloseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Order
     */
    protected $order;

    /**
     * CloseOrder constructor.
     * @param Order $order
     * @param $delay
     */
    public function __construct(Order $order, $delay)
    {
        $this->order = $order;
        $this->delay($delay);
    }


    public function handle()
    {
        if ($this->order->paid_at) {
            return;
        }

        // 通过事务执行sql
        \DB::transaction(function() {
            // 将订单的 closed 字段标记为 true, 即关闭订单
            $this->order->update(['closed' => true]);
            // 循环遍历订单中的sku 把订单中的数量放回库存
            foreach ($this->order->items as $item) {
                $item->productSku->increaseStock($item->amount);
            }
        });
    }
}
