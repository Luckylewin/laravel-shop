<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payByAliPay(Order $order, Request $request)
    {
        // 判断当前订单是否属于当前用户
        $this->authorize('own', $order);
        // 订单已支付或者已经关闭时
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // 调用支付宝的网页支付
        return app('alipay')->web([
            'out_trade_no' => $order->no, // 订单编号, 需要保证商户端不重复
            'total_amount' => $order->total_amount, // 订单金额
            'subject'      => '支付 Laravel Shop 的订单:' . $order->no // 订单标题
        ]);
    }

    public function alipayReturn()
    {
       try {
           app('alipay')->verify();
       } catch (\Exception $e) {
           return view('pages.error', ['msg' => '数据不正确']);
       }

       return view('pages.success', ['msg' => '付款成功']);
    }

    public function alipayNotify()
    {
        $data = app('alipay')->verify();

        /** @var $order Order **/

        $order = Order::where('no', $data->out_trade_no)->first();

        if ($order == false) {
            return 'fail';
        }

        if ($order->paid_at) {
            return app('alipay')->success();
        }

        $order->update([
            'paid_at' => Carbon::now(),
            'payment_method' => 'alipay',
            'payment_no' => $data->trade_no
        ]);

        return app('alipay')->success();
    }
}
