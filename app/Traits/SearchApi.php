<?php


namespace App\Traits;


use App\Models\Product;
use Illuminate\Support\Facades\DB;

trait SearchApi
{

    /**
     * getProductsForSearch
     *
     * Retrieves products for the search page like shop and category
     * accepts $params array that contain multiple fields to search with
     * like category_id, brand_id, etc
     *
     * @param array $params
     */
    public function getProductsForSearch($params = array())
    {
        extract($params);

        $query = Product::with('gallery', 'category', 'brand')
            ->select('id', 'title', 'description', 'price', 'discount', 'discount_start_date', 'discount_end_date', 'category_id', 'brand_id', 'featured')
            ->where('amount', '>', 0)
            ->has('gallery');

        if (isset($category_id) && !empty($category_id)) {
            $query->where('category_id', $category_id);
        }

        if (isset($brand_id) && !empty($brand_id)) {
            $query->where('brand_id', $brand_id);
        }

        if (isset($from_price) && !empty($from_price) && is_numeric($from_price)) {
            $query->where('price', '>=', floatval($from_price));
        }

        if (isset($to_price) && !empty($to_price) && is_numeric($to_price)) {
            $query->where('price', '<=', floatval($to_price));
        }

        if (isset($keyword) && !empty($keyword)) {
            $query->where('title', 'like', "%$keyword%");
        }

        if (isset($except)) {
            $query->where('id', '!=', $except);
        }

        $products = $query->orderBy('id', 'DESC')
            ->paginate(12);

        return response()->json(['products' => $products]);
    }


    /**
     * getBrandsByCategory
     *
     * get brands and their product counts by category
     *
     * @param $categoryId
     */
    public function getBrandsByCategory($categoryId)
    {
        $brands = DB::table('products AS p')
            ->join('brands', 'brands.id', '=', 'p.brand_id')
            ->where('amount', '>', 0)
            ->where('category_id', $categoryId)
            ->whereRaw('exists (select * from `product_gallery` where p.id = product_gallery.product_id)')
            ->select(DB::raw('DISTINCT brand_id, brands.title, (select count(*) from products where products.brand_id = p.brand_id and products.category_id = p.category_id) as count_products'))
            ->get();


        return response()->json(['brands' => $brands]);
    }
}
