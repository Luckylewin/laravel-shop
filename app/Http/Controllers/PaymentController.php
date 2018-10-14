<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
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

        $this->afterPaid($order);

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

    // 微信支付
    public function payByWechat(Order $order, Request $request)
    {
        $this->authorize('own', $order);
        // 校验订单状态
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        try {
            // scan 方法为拉起微信扫码支付
            $wechatOrder = app('wechat_pay')->scan([
                'out_trade_no' => $order->no,  // 商户订单流水号，与支付宝 out_trade_no 一样
                'total_fee' => $order->total_amount * 100, // 与支付宝不同，微信支付的金额单位是分。
                'body'      => '支付 Laravel Shop 的订单：'.$order->no, // 订单描述
            ]);

            // 把要转换的字符串作为 QrCode 的构造函数参数
            $qrCode = new QrCode($wechatOrder->code_url);
        } catch (\Exception $e) {
            $qrCode = new QrCode('微信支付暂不可用,请使用其他支付方式');
        }

        // 将生成的二维码图片数据以字符串形式输出，并带上相应的响应类型
        return response($qrCode->writeString(), 200, ['Content-Type' => $qrCode->getContentType()]);
    }

    public function wechatNotify()
    {
        // 校验回调参数是否正确
        $data  = app('wechat_pay')->verify();
        // 找到对应的订单
        $order = Order::where('no', $data->out_trade_no)->first();
        // 订单不存在则告知微信支付
        if (!$order) {
            return 'fail';
        }
        // 订单已支付
        if ($order->paid_at) {
            // 告知微信支付此订单已处理
            return app('wechat_pay')->success();
        }

        // 将订单标记为已支付
        $order->update([
            'paid_at'        => Carbon::now(),
            'payment_method' => 'wechat',
            'payment_no'     => $data->transaction_id,
        ]);

        $this->afterPaid($order);

        return app('wechat_pay')->success();
    }

    /**
     * @param Order $order
     */
    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }

}