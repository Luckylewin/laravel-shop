<?php

namespace App\Http\Controllers;

use App\Exceptions\CouponCodeUnavailableException;
use App\Models\CouponCode;


class CouponCodesController extends Controller
{

    public function show($code)
    {
        // 判断优惠券 是否存在
        if (!$record = CouponCode::query()->where('code', $code)->first()) {
           throw new CouponCodeUnavailableException('优惠券不存在');
        }

        $record->checkAvailable();

        return $record;
    }
}
