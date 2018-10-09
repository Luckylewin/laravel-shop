<?php

use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //创建30个产品
        $products = factory(\App\Models\Product::class, 30)->create();
        foreach ($products as $product) {
            // 创建 3 个 SKU, 并且每个SKU的 product_id 字段都设置为当前循环的商品id
            $skus = factory(\App\Models\ProductSku::class, 3)->create(['product_id' => $product->id]);

            $product->update(['price' => $skus->min('price')]);
        }
    }
}
