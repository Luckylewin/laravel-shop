<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * 支付成功事件
 * 事件本身不需要逻辑，只需要包含相关的信息即可
 * 在此场景只需要一个订单对象即可
 * Class OrderPaid
 * @package App\Events
 */
class OrderPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Order $order
     */
    protected $order;

    /**
     * Create a new event instance.
     * OrderPaid constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }
}
