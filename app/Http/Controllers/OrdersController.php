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
    public function index(Request $request)
    {
        $orders = Order::query()
                ->with(['items.product', 'items.productSku'])
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Order $order, Request $request)
    {
        // load 方法 延迟预加载 在已经查询处理的模型上调用
        $this->authorize('own', $order);
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));
        $remark  = $request->input('remark');
        $items   = $request->input('items');

        return $orderService->store($user, $address, $remark, $items);
    }
}
