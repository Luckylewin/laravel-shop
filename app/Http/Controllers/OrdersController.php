<?php

namespace App\Http\Controllers;

use App\Events\OrderReviewed;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;

use App\Http\Requests\SendReviewRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    // 订单列表
    public function index(Request $request)
    {
        $orders = Order::query()
                ->with(['items.product', 'items.productSku'])
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }

    // 用户下单
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));
        $remark  = $request->input('remark');
        $items   = $request->input('items');

        return $orderService->store($user, $address, $remark, $items);
    }

    // 订单详情
    public function show(Order $order, Request $request)
    {
        // load 方法 延迟预加载 在已经查询处理的模型上调用
        $this->authorize('own', $order);
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    // 收货处理
    public function receive(Order $order, Request $request)
    {
        $this->authorize('own', $order);
        
        if ($order->ship_status != Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('商品尚未发货');
        }

        $order->update([
            'ship_status' => Order::SHIP_STATUS_RECEIVED
        ]);

        return $order;
    }

    // 评价展示页面
    public function review(Order $order)
    {
        // 校验权限
        $this->authorize('own', $order);
        // 判断是否已经支付
        if ($order->paid_at == false) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }

        // 使用load 方法来加载关联数据,避免 N + 1 性能问题
        return view('orders.review', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    // 保存评价
    public function sendReview(Order $order, SendReviewRequest $request)
    {
        // 校验权限
        $this->authorize('own', $order);
        // 判断是否已经支付
        if ($order->paid_at == false) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        // 判断是否已经评价
        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已评价，不可重复提交');
        }

        $reviews = $request->input('reviews');
        \DB::transaction(function () use ($reviews, $order) {
             // 遍历提交的数据
             foreach ($reviews as $review) {
                 $orderItem = $order->items()->find($review['id']);
                 $orderItem->update([
                     'rating'      => $review['rating'],
                     'review'      => $review['review'],
                     'reviewed_at' => Carbon::now()
                 ]);
             }
             // 订单标记为已经评价
             $order->update(['reviewed' => true]);

             // 触发事件
             event(new OrderReviewed($order));
        });

        return redirect()->back();
    }

}
