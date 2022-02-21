<?php


namespace App\Http\Controllers;


use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShippingAddressesController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $addresses = ShippingAddress::where('user_id', Auth::user()->id)->orderBy('is_primary', 'DESC')->get();

        return response()->json(['shipping_addresses' => $addresses], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "address" => "required",
            "mobile" => "required",
            "country" => "required",
            "city" => "required",
            "postal_code" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Required or incorrect fields', 'errors' => $validator->errors()], 500);
        }

        $user = Auth::user();

        $shippingAddress = new ShippingAddress();
        $shippingAddress->address = $request->input("address");
        $shippingAddress->country = $request->input("country");
        $shippingAddress->city = $request->input("city");
        $shippingAddress->postal_code = $request->input("postal_code");
        $shippingAddress->mobile = $request->input("mobile");
        $shippingAddress->user_id = $user->id;

        if ($request->has('is_primary')) {
            $this->revertShippingAddresses();

            $shippingAddress->is_primary = 1;
        } else {
            if (ShippingAddress::where('user_id', $user->id)->count() == 0) {
                $shippingAddress->is_primary = 1;
            } else {
                $shippingAddress->is_primary = 0;
            }
        }

        $shippingAddress->save();

        return response()->json(['success' => 1, 'message' => 'Address saved successfully', 'shipping_address' => $shippingAddress], 200);
    }

    public function show($id)
    {
        $address = ShippingAddress::where('id', $id)->where('user_id', Auth::user()->id)->first();

        return response()->json(['shipping_address' => $address]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "address" => "required",
            "mobile" => "required",
            "country" => "required",
            "city" => "required",
            "postal_code" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Required or incorrect fields', 'errors' => $validator->errors()], 500);
        }

        $user = Auth::user();

        $shippingAddress = ShippingAddress::where('id', $id)->where('user_id', Auth::user()->id)->first();

        if (!$shippingAddress) {
            return response()->json(['success' => 0, 'message' => 'address not found'], 404);
        }

        if ($request->has('is_primary')) {
            $this->revertShippingAddresses();
        }

        $shippingAddress->address = $request->input("address");
        $shippingAddress->country = $request->input("country");
        $shippingAddress->city = $request->input("city");
        $shippingAddress->postal_code = $request->input("postal_code");
        $shippingAddress->mobile = $request->input("mobile");
        $shippingAddress->user_id = $user->id;

        if ($request->has('is_primary')) {

            $shippingAddress->is_primary = 1;
        } else {
            $shippingAddress->is_primary = 0;
        }

        $shippingAddress->save();

        return response()->json(['success' => 1, 'message' => 'Address updated successfully', 'shipping_address' => $shippingAddress], 200);
    }

    public function destroy($id)
    {
        $shippingAddress = ShippingAddress::where('id', $id)->where('user_id', Auth::user()->id)->first();

        if (!$shippingAddress) {
            return response()->json(['success' => 0, 'message' => 'address not found'], 404);
        }

        $is_primary = $shippingAddress->is_primary;

        $shippingAddress->delete();

        // check if there are existing addresses then update first as primary
        if ($is_primary) {
            $shippingAddress = ShippingAddress::where('user_id', Auth::user()->id)->first();

            if ($shippingAddress) {
                $shippingAddress->is_primary = 1;
                $shippingAddress->save();
            }
        }

        return response()->json(['success' => 1, 'message' => 'Address deleted successfully'], 200);
    }

    private function revertShippingAddresses()
    {
        foreach (ShippingAddress::where('user_id', Auth::user()->id)->get() as $address) {
            $address->is_primary = 0;
            $address->save();
        }
    }
}
