<?php

namespace App\Admin\Controllers;

use App\Exceptions\InternalException;
use App\Exceptions\InvalidRequestException;

use App\Http\Requests\Admin\HandleRefundRequest;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param Order $order
     * @param Content $content
     * @return Content
     */
    public function show(Order $order, Content $content)
    {
        return $content
            ->header('查看订单')
            ->body(view('admin.orders.show', ['order' => $order]));
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);

        $grid->model()->whereNotNull('paid_at');

        $grid->no('订单流水');
        $grid->column('user.name', '买家');
        $grid->total_amount('总金额');

        $grid->paid_at('支付时间');

        $grid->ship_status('物流')->display(function($value) {
            return Order::$shipStatusMap[$value];
        });

        $grid->refund_status('退款状态')->display(function($value) {
            return Order::$refundStatusMap[$value];
        });

        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 禁用删除和编辑按钮
            $actions->disableDelete();
            $actions->disableEdit();
        });

        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Order::findOrFail($id));

        $show->id('Id');
        $show->no('No');
        $show->user_id('User id');
        $show->address('Address');
        $show->total_amount('Total amount');
        $show->remark('Remark');
        $show->paid_at('Paid at');
        $show->payment_method('Payment method');
        $show->payment_no('Payment no');
        $show->refund_status('Refund status');
        $show->refund_no('Refund no');
        $show->closed('Closed');
        $show->reviewed('Reviewed');
        $show->ship_status('Ship status');
        $show->ship_data('Ship data');
        $show->extra('Extra');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    //  发货处理
    public function ship(Order $order, Request $request)
    {
        // 判断当前订单是否支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付');
        }

        // 判断订单发货状态
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已经发货');
        }

        $data = $this->validate($request, [
             // 快递公司
            'express_company' => ['required'],
             // 快递单号
            'express_no' => ['required'],
        ], [], [
            'express_company' => '物流公司',
            'express_no' => '物流单号'
        ]);

        // 订单状态改为已经发货 并存入物流信息
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            'ship_data' => $data
        ]);

        return redirect()->back();

    }

    // 审核退款申请
    public function handleRefund(Order $order, HandleRefundRequest $request)
    {
        // 判断订单状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_APPLIED) {
            throw new InvalidRequestException('订单状态不正确');
        }

        if ($request->input('agree')) {
            $this->_refundOrder($order);
        } else {
            $extra = $order->extra ?: [];
            $extra['refund_disagree_reason'] = $request->input('reason');

            // 订单状态改为未退款
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra'         => $extra
            ]);
        }

        return $order;
    }

    // 具体退款逻辑
    protected function _refundOrder(Order $order)
    {
        switch ($order->payment_method) {
            case 'wechat':
                // 生成退款订单号
                $refundNo = Order::findAvailableRefundNo();
                app('wechat_pay')->refund([
                    'out_trade_no' => $order->no, // 之前的订单流水号
                    'total_fee' => $order->total_amount * 100, //原订单金额，单位分
                    'refund_fee' => $order->total_amount * 100, // 要退款的订单金额，单位分
                    'out_refund_no' => $refundNo, // 退款订单号
                    // 微信支付的退款结果并不是实时返回的，而是通过退款回调来通知，因此这里需要配上退款回调接口地址
                    'notify_url' => route('payment.wechat.refund_notify') // 开发环境，配成 requestbin(http://requestbin.fullcontact.com/xxxxx) 地址
                ]);
                // 将订单状态改成退款中
                $order->update([
                    'refund_no' => $refundNo,
                    'refund_status' => Order::REFUND_STATUS_PROCESSING,
                ]);
                break;
            case 'alipay':
                $refundNo = Order::findAvailableRefundNo();
                $result = app('alipay')->refund([
                    'out_trade_no' => $order->no, // 订单流水号
                    'refund_amount' => $order->total_amount, // 退款金额 单位元
                    'out_request_no' => $refundNo
                ]);
                // 如果有 sub_code 字段说明退款失败
                if ($result->sub_code) {
                    $extra = $order->extra;
                    $extra['refund_failed_code'] = $result->sub_code;

                    // 订单标记为退款失败
                    $order->update([
                        'refund_no'     => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_FAILED,
                        'extra'         => $extra
                    ]);
                } else {
                    $order->update([
                        'refund_no'     => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_SUCCESS
                    ]);
                }

                break;
            default:
                // 原则上不可能出现，这个只是为了代码健壮性
                throw new InternalException('未知订单支付方式：'.$order->payment_method);
                break;
        }
    }
}
