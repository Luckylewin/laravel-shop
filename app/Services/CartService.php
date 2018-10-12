<?php
namespace App\Services;

use Auth;
use App\Models\CartItem;

class CartService
{
    /**
     * 获取购物车中的商品
     * @return \Illuminate\Database\Eloquent\Collection
     */
   public function get()
   {
   	return Auth::user()->cartItems()->with(['productSku.product'])->get();	
   }

    /**
     * 往购物车中增加商品
     * @param $skuId
     * @param $amount
     * @return CartItem
     */
   public function add($skuId, $amount)
   {
        $user = Auth::user();
        if ($item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            $item->update([
                'amount' => $item->amount + $amount
            ]);
        } else {
           // 不在则创建一个新的购物车记录
           $item = new CartItem(['amount' => $amount]);
           $item->user()->associate($user);
           $item->productSku()->associate($skuId);
           $item->save();
        }

        return $item;
   }

    /**
     * 从购物车中移除一个或多个商品
     * @param $skuIds integer|array
     */
   public function remove($skuIds)
   {
       if (is_array($skuIds) == false) {
          $skuIds = [$skuIds];
       }

       Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
   }

}
