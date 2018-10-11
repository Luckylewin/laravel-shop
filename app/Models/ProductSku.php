<?php

namespace App\Models;

use App\Exceptions\InternalException;
use Illuminate\Database\Eloquent\Model;

/**
 * SKU 模型
 * Class ProductSku
 * @package App\Models
 */
class ProductSku extends Model
{
    protected $fillable = ['title', 'description', 'price', 'stock'];

    // 关联产品
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 减库存操作
    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('减库存不可小于0');
        }

        return $this->newQuery()->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }

    // 增库存操作
    public function increaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('减库存不可小于0');
        }

        $this->increment('stock', $amount);
    }
}
