<?php


namespace App\Http\Controllers;


use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\ProductGallery;
use App\Traits\Helpers;
use App\Traits\HomeApi;
use App\Traits\SearchApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    use Helpers, HomeApi, SearchApi;

    public function __construct()
    {
        $this->middleware('super_admin_check:store-update-destroy-destroyImage');
    }

    public function index(Request $request)
    {
        $query = Product::with('category', 'user', 'gallery', 'brand');

        $this->filterAndResponse($request, $query);

        $query->orderBy('id', 'DESC');

        $products = $query->paginate(15);

        return response()->json(['products' => $products], 200);
    }

    public function store(Request $request)
    {
        try {
            if (($errors = $this->doValidate($request)) && count($errors) > 0) {
                return response()->json(['success' => 0, 'message' => 'Please fix these errors', 'errors' => $errors], 500);
            }

            $product = new Product;
            foreach ($request->except('features', 'image') as $key => $value) {
                if ($key == 'discount_start_date' || $key == 'discount_end_date') {
                    $product->{$key} = !empty($value) && !is_null($value) ? date("Y-m-d", strtotime($value)) : null;
                } else if ($key == 'discount') {
                    $product->{$key} = $value ? intval($value) : 0;
                } else if ($key == 'brand_id') {
                    $product->{$key} = is_null($value) || empty($value) ? NULL : intval($value);
                } else {
                    $product->{$key} = $value;
                }
            }

            $product->created_by = auth()->user()->id;

            $product->save();

            // save features if any
            $this->insertFeatures($request, $product);

            // upload images
            $this->uploadImages($request, $product);

            return response()->json(['success' => 1, 'message' => 'Created successfully', 'product' => $product], 201);
        } catch (\Exception $e) {
            $product->delete();

            return response()->json(['success' => 0, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $product = Product::with('features', 'gallery', 'brand', 'category')->findOrFail($id);

        return response()->json(['product' => $product], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            if (($errors = $this->doValidate($request, $id)) && count($errors) > 0) {
                return response()->json(['success' => 0, 'message' => 'Please fix these errors', 'errors' => $errors], 500);
            }

            foreach ($request->except('features', 'image', '_method') as $key => $value) {
                if ($key == 'discount_start_date' || $key == 'discount_end_date') {
                    $product->{$key} = !empty($value) && !is_null($value) ? date("Y-m-d", strtotime($value)) : null;
                } else if ($key == 'discount') {
                    $product->{$key} = $value ? intval($value) : 0;
                } else if ($key == 'brand_id') {
                    $product->{$key} = is_null($value) || empty($value) ? NULL : intval($value);
                } else {
                    $product->{$key} = $value;
                }
            }

            $product->save();

            if ($product->features()->count() > 0) {
                $product->features()->delete();
            }

            $this->insertFeatures($request, $product);

            // upload images
            $this->uploadImages($request, $product);

            return response()->json(['success' => 1, 'message' => 'Updated successfully', 'product' => $product]);
        } catch (\Exception $e) {
            return response()->json(['success' => 0, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::with('gallery')->findOrFail($id);

            foreach ($product->gallery as $gallery) {
                if (!empty($gallery->image)) {
                    foreach ($gallery->image_url as $dir => $url) {
                        $this->deleteFile(base_path('public') . '/uploads/' . $gallery->product_id . '/' . $dir . '/' . $gallery->image);
                    }

                    $this->deleteFile(base_path('public') . '/uploads/' . $gallery->product_id . '/' . $gallery->image);
                }
            }

            $product->delete();

            return response()->json(['success' => 1, 'message' => 'Deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => 0, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyImage($id)
    {
        try {
            $gallery = ProductGallery::findOrFail($id);

            if (!empty($gallery->image)) {
                foreach ($gallery->image_url as $dir => $url) {
                    $this->deleteFile(base_path('public') . '/uploads/' . $gallery->product_id . '/' . $dir . '/' . $gallery->image);
                }

                $this->deleteFile(base_path('public') . '/uploads/' . $gallery->product_id . '/' . $gallery->image);
            }

            $gallery->delete();

            return response()->json(['success' => 1, 'message' => 'Deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => 0, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * filter and response
     *
     * @param $request
     * @param $query
     */
    private function filterAndResponse($request, $query)
    {
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        if ($request->has('title')) {
            $query->where('title', 'like', "%$request->title%");
        }

        if ($request->has('product_code')) {
            $query->where('product_code', $request->product_code);
        }

        if ($request->has('from_price')) {
            $query->where('price', '>=', $request->from_price);
        }

        if ($request->has('to_price')) {
            $query->where('price', '<=', $request->to_price);
        }

        if ($request->has('amount')) {
            $query->where('amount', $request->amount);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
    }

    protected function insertFeatures($request, $product)
    {
        if ($request->has('features')) {
            foreach ($request->input('features') as $id => $feature_value) {
                if (!empty($feature_value)) {
                    $productFeature = new ProductFeature();
                    $productFeature->field_id = $id;
                    $productFeature->field_value = $feature_value;

                    $product->features()->save($productFeature);
                }
            }
        }
    }


    /**
     * upload images
     *
     * @param $request
     * @param $product
     */
    protected function uploadImages($request, $product)
    {
        $this->createProductUploadDirs($product->id, $this->imagesSizes);

        $uploaded_files = $this->uploadFiles($request, 'image', base_path('public') . '/uploads/' . $product->id);

        foreach ($uploaded_files as $uploaded_file) {

            $productGallery = new ProductGallery();
            $productGallery->image = $uploaded_file;

            $product->gallery()->save($productGallery);

            // start resize images
            foreach ($this->imagesSizes as $dirName => $imagesSize) {
                $this->resizeImage(base_path('public') . '/uploads/' . $product->id . '/' . $uploaded_file, base_path('public') . '/uploads/' . $product->id . '/' . $dirName . '/' . $uploaded_file, $imagesSize['width'], $imagesSize['height']);
            }
        }
    }

    /**
     * validate
     *
     * @param $request
     * @param null $id
     * @throws \Exception
     */
    protected function doValidate($request, $id = null)
    {
        $payload = [
            'title' => 'required',
            'price' => 'required|numeric',
            'amount' => 'required|numeric',
            'discount' => 'min:0|max:100',
            'category_id' => 'required',
            'discount_start_date' => 'date',
            'discount_end_date' => 'date|after:discount_start_date'
        ];

        if (!$id) {
            $payload += [
                'image' => 'required',
                'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10000'
            ];
        }

        $validator = Validator::make($request->all(), $payload);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return [];
    }


    public function sliderProducts()
    {
        return $this->getSliderProducts();
    }

    public function latestProducts()
    {
        return $this->getLatestProducts();
    }

    public function featuredProducts()
    {
        return $this->getFeaturedProducts();
    }

    public function searchProducts(Request $request)
    {
        return $this->getProductsForSearch($request->toArray());
    }
}
