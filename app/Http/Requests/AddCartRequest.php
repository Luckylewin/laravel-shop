<?php

namespace App\Http\Requests;

use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;

class AddCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sku_id' => [
                'required',
                 // 闭包校验规则允许我们直接通过匿名函数的方式来校验用户输入，比较适合在项目中只使用一次的情况
                 // 参数名、参数值和错误回调
                 function($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        $fail('该商品不存在');
                        return;
                    }
                     if (!$sku->product->on_sale) {
                         $fail('该商品未上架');
                         return;
                     }
                     if ($sku->stock === 0) {
                         $fail('该商品已售完');
                         return;
                     }
                     if ($this->input('amount') > 0 && $sku->stock < $this->input('amount')) {
                         $fail('该商品库存不足');
                         return;
                     }
                 }
            ]
        ];
    }

    public function attributes()
    {
        return [
            'amount' => '商品数量'
        ];
    }

    public function messages()
    {
        return [
            'sku_id.required' => '请选择商品'
        ];
    }
}
