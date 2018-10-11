<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 订单商品明细模型
 * Class OrderItem
 * @var $amount integer
 * @var $price float
 * @var $rating integer
 * @var $review integer
 * @var $review_at string
 * @package App\Models
 */
class OrderItem extends Model
{
    protected $fillable = [
        'amount',
        'price',
        'rating',
        'review',
        'reviewed_at'
    ];

    protected $dates = ['reviewed_at'];

    //代表这个模型没有 created_at 和 updated_at 两个时间戳字段。
    public $timestamps = false;

    // 关联产品
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 关联产品sku
    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
