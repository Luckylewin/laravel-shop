<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    //
    public function index(Request $request)
    {
        $queryBuilder = Product::query()->where('on_sale', true);

        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊搜索商品标题，商品详情，sku标题，sku描述
            $queryBuilder->where(function($query) use ($like) {
                 $query->where('title', 'like', $like)
                       ->orWhere('description', 'like', $like)
                       ->orWhereHas('skus', function ($query) use ($like) {
                           $query->where('title', 'like', $like)
                                 ->orWhere('description', 'like', $like);
                       });
            });
        }

        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    $queryBuilder->orderBy($m[1], $m[2]);
                }
            }
        }

        $products = $queryBuilder->paginate(16);

        return view('products.index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
                'order' => $order
            ]
        ]);
    }
}
