<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;

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

        return redirect()->back();
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
}
