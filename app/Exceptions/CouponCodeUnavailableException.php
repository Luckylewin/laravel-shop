<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

/**
 * 用户触发的业务异常(不记录到日志中 需要在Handler 中注册dontReport)
 * Class CouponCodeUnavailableException
 * @package App\Exceptions
 */
class CouponCodeUnavailableException extends Exception
{
    /**
     * CouponCodeUnavailableException constructor.
     * @param $message
     * @param int $code
     */
    public function __construct($message, int $code = 403)
    {
        parent::__construct($message, $code);
    }

    // 当这个异常被触发时 调用 render 方法输出给用户
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['msg' => $this->message], $this->code);
        }

        return redirect()->back()->withErrors(['coupon_code' => $this->message]);
    }
}
