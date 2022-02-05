<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Traits\SearchApi;
use Illuminate\Support\Facades\Validator;

class BrandsController extends Controller
{
    use SearchApi;

    public function __construct()
    {
        $this->middleware('super_admin_check:store-update-destroy');
    }

    public function index(Request $request)
    {
        $brands = $this->filterAndResponse($request);

        return response()->json(['brands' => $brands], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->only('title'), [
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Please fix these errors', 'errors' => $validator->errors()], 500);
        }

        $brand = Brand::create($request->all());

        return response()->json(['success' => 1, 'message' => 'Created successfully', 'brand' => $brand], 201);
    }

    public function show($id)
    {
        $brand = Brand::findOrFail($id);

        return response()->json(['brand' => $brand], 200);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validator = Validator::make($request->only('title'), [
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Please fix these errors', 'errors' => $validator->errors()], 500);
        }

        $brand->title = $request->input('title');

        $brand->save();

        return response()->json(['success' => 1, 'message' => 'Updated successfully', 'brand' => $brand], 200);
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        $brand->delete();

        return response()->json(['success' => 1, 'message' => 'Deleted successfully'], 200);
    }

    /**
     * @param Request $request
     */
    protected function filterAndResponse(Request $request)
    {
        $query = Brand::whereRaw("1=1");

        if ($request->has('all')) {
            return $query->get();
        }

        if ($request->id) {
            $query->where('id', $request->id);
        }

        if ($request->title) {
            $query->where('title', 'like', "%" . $request->title . "%");
        }

        $brands = $query->paginate(10);

        return $brands;
    }

    public function brandsByCategory(Request $request)
    {
        return $this->getBrandsByCategory($request->category_id);
    }
}
