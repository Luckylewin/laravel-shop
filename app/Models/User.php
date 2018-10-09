<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'email_verified'
    ];

    // 类型转换
    protected $casts = [
        'email_verified' => 'boolean'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // 一个用户有多个地址 一对多关联
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    // 一个用户收藏了多个产品 多对多关联
    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'user_favorite_products')
                    ->withTimestamps() // 代表中间表带有时间戳字段
                    ->orderBy('user_favorite_products.created_at', 'desc'); // 代表默认的排序方式是根据中间表的创建时间倒叙排序
    }
}
