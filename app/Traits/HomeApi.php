<?php


namespace App\Traits;


use App\Models\Category;
use App\Models\Product;

trait HomeApi
{

    /**
     * getCategoryMenuTree
     */
    public function getCategoryMenuTree()
    {
        Category::getCategoryMenuTree(null, $output);

        return response()->json($output);
    }

    /**
     * get slider products
     */
    public function getSliderProducts()
    {
        $allProducts = [];

        // get top 5 products who have active discount

        $products = Product::with('gallery')->select('id', 'title', 'description', 'price', 'discount', 'discount_start_date', 'discount_end_date')
            ->where('amount', '>', 0)
            ->whereNotNull('discount')
            ->where('discount', '>', 0)
            ->has('gallery')
            ->where(function ($query) {
                $query->discountWithStartAndEndDates()
                    ->orWhere->discountWithStartDate()
                    ->orWhere->discountWithEndDate();
            })
            ->orderBy('id', 'DESC')->limit(5)->get();

        foreach ($products as $product) {
            $allProducts[] = $product;
        }

        // if retrieved products < 5 then try to retrieve some other products and append them to the products array

        if (count($allProducts) < 5) {

            $otherProducts = Product::with('gallery')
                ->select('id', 'title', 'description', 'price', 'discount', 'discount_start_date', 'discount_end_date')
                ->where('amount', '>', 0)
                ->has('gallery')
                ->whereNotIn('id', array_column($allProducts, 'id'))
                ->orderBy('id', 'DESC')
                ->limit(5)->get();

            foreach ($otherProducts as $product) {
                if (count($allProducts) == 5) {
                    break;
                }

                $allProducts[] = $product;
            }
        }

        return response()->json(['products' => $allProducts], 200);
    }


    /**
     * get latest products
     */
    public function getLatestProducts()
    {
        $categories = Category::all();

        $products = [];

        foreach ($categories as $category) {

            if (count($products) == 8) {
                break;
            }

            $items = Product::with('gallery')
                ->select('id', 'title', 'description', 'price', 'discount', 'discount_start_date', 'discount_end_date')
                ->where('amount', '>', 0)
                ->where('category_id', $category->id)
                ->has('gallery')
                ->limit(1)->orderBy('id', 'DESC')
                ->get();

            if ($items && $items->count() == 1) {
                $products[] = $items[0];
            }
        }

        return response()->json(['products' => $products]);
    }


    /**
     * getFeaturedCategories
     *
     */
    public function getFeaturedCategories()
    {
        $categories = Category::with(['products' => function ($query) {
            $query->with('gallery')
                ->where('amount', '>', 0)
                ->has('gallery')
                ->orderBy('id', 'DESC');
        }])->where('featured', 1)
            ->has('products')
            ->limit(9)
            ->orderBy('id', 'ASC')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    /**
     * getFeaturedProducts
     */
    public function getFeaturedProducts()
    {
        $products = Product::with('gallery')
            ->select('id', 'title', 'description', 'price', 'discount', 'discount_start_date', 'discount_end_date')
            ->where('amount', '>', 0)
            ->where('featured', 1)
            ->has('gallery')
            ->orderBy('id', 'DESC')
            ->limit(12)->get();

        return response()->json(['products' => $products]);
    }
}
