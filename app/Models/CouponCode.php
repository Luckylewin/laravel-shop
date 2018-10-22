<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailableException;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CouponCode extends Model
{
    // 优惠券类型
    const TYPE_FIXED   = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED   => '固定金额',
        self::TYPE_PERCENT => '比例'
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled'
    ];

    protected $casts = [
        'enabled' => 'boolean'
    ];

    protected $dates = [
        'not_before',
        'not_after'
    ];

    // 附属字段
    protected $appends = [
        'description'
    ];

    /**
     * 描述信息
     */
    public function getDescriptionAttribute()
    {
        $str = '';

        if ($this->min_amount > 0) {
            $str = '满'.$this->min_amount;
        }

        $value = str_replace('.00', '', $this->value);

        if ($this->type == self::TYPE_PERCENT) {
            $str .= '优惠'.$value.'%';
        } else {
            $str .= '减'.$value;
        }

        return $str;
    }

    /**
     * 生成优惠券
     * @param int $length
     * @return string
     */
    public static function findAvailableCode($length = 16)
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * 检查优惠券可用性
     * @param User $user
     * @param null $orderAmount
     * @throws CouponCodeUnavailableException
     */
    public function checkAvailable(User $user, $orderAmount = null)
    {
        $used = Order::query()
                    ->where('user_id', $user->id)
                    ->where('coupon_code_id', $this->id)
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->whereNull('paid_at')
                                ->where('closed', false);
                        })->orWhere(function($query) {
                            $query->whereNotNull('paid_at')
                                  ->where('refund_status', '!=', Order::REFUND_STATUS_SUCCESS);
                        });
                    })
                    ->exists();

        if ($used) {
            throw new CouponCodeUnavailableException('该优惠券已被使用');
        }

        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }

        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('该优惠券已被兑完');
        }

        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券现在还不能使用');
        }

        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠券最低金额');
        }
    }

    /**
     * 获取优惠后的价格
     * @param $orderAmount
     * @return mixed|string
     */
    public function getAdjustPrice($orderAmount)
    {
        if ($this->type == self::TYPE_FIXED) {
            return max(0.01, $orderAmount - $this->value);
        }

        return number_format($orderAmount * (100 - 100 * $this->value) / 100 , 2, '.','');
    }

    // 使用 + 1
    public function stockIncrement()
    {
        return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
    }

    // 使用 - 1
    public function stockDecrease()
    {
        return $this->decrement('used');
    }
}
