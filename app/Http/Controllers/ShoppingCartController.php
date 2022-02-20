<?php


namespace App\Http\Controllers;


use App\Models\Product;
use App\Models\ShoppingCart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShoppingCartController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $user = Auth::user();

        $cart = ShoppingCart::with('product')->where('user_id', $user->id)->get();

        return response()->json(['cart' => $cart], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->only('product_id', 'amount'), [
            'product_id' => "required",
            'amount' => "required|numeric|min:1"
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Required or incorrect fields', 'errors' => $validator->errors()], 500);
        }

        $product = Product::find($request->input('product_id'));

        if (!$product) {
            return response()->json(['success' => 0, 'message' => 'Product not found'], 404);
        }

        if ($product->amount == 0) {
            return response()->json(['success' => 0, 'message' => 'Product has no more available items'], 500);
        }

        if ($product->amount < $request->input('amount')) {
            return response()->json(['success' => 0, 'message' => 'There are only ' . $product->amount . ' available items of this product'], 500);
        }

        $user = Auth::user();

        $cartItem = new ShoppingCart();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $request->input('product_id');
        $cartItem->amount = $request->input('amount');
        $cartItem->save();

        // update product amount
        $product->decrement('amount', $request->input('amount'));

        $cartItem = ShoppingCart::with('product')->where('user_id', $user->id)->where('product_id', $request->input('product_id'))->first();

        return response()->json(['success' => 1, 'message' => 'Item added successfully to the cart', 'item' => $cartItem], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->only('product_id', 'amount'), [
            'product_id' => "required",
            'amount' => "required|numeric|min:1"
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Required or incorrect fields', 'errors' => $validator->errors()], 500);
        }

        $product = Product::find($request->input('product_id'));

        if (!$product) {
            return response()->json(['success' => 0, 'message' => 'Product not found'], 404);
        }

        $user = Auth::user();

        $cartItem = ShoppingCart::where('user_id', $user->id)->where('product_id', $request->input('product_id'))->first();

        $oldAmount = $cartItem->amount;

        if ($product->amount + $oldAmount < $request->input('amount')) {
            return response()->json(['success' => 0, 'message' => 'There are only ' . (($product->amount + $oldAmount) - $oldAmount) . ' available items of this product'], 500);
        }

        $cartItem->amount = $request->input('amount');
        $cartItem->save();

        // update product amount
        $product->amount = (($product->amount + $oldAmount) - $request->input('amount'));
        $product->save();

        $cartItem = ShoppingCart::with('product')->where('user_id', $user->id)->where('product_id', $request->input('product_id'))->first();

        return response()->json(['success' => 1, 'message' => 'Cart item updated successfully', 'item' => $cartItem], 200);
    }

    public function show($id)
    {
        $cartItem = ShoppingCart::with('product')->find($id);

        if (!$cartItem) {
            return response()->json(['success' => 0, 'message' => 'Cart item not found'], 404);
        }

        $user = Auth::user();

        if ($user->id != $cartItem->user_id) {
            return response()->json(['success' => 0, 'message' => 'This cart item does not belong to you!'], 500);
        }

        return response()->json(['item' => $cartItem], 200);
    }

    public function destroy($id)
    {
        $cartItem = ShoppingCart::with('product')->find($id);

        if (!$cartItem) {
            return response()->json(['success' => 0, 'message' => 'Cart item not found'], 404);
        }

        $user = Auth::user();

        if ($user->id != $cartItem->user_id) {
            return response()->json(['success' => 0, 'message' => 'This cart item does not belong to you!'], 500);
        }

        $cartItemAmount = $cartItem->amount;

        $cartItem->delete();

        // reset the product amount
        $product = Product::find($cartItem->product_id);

        $product->increment('amount', $cartItemAmount);

        return response()->json(['success' => 1, 'message' => 'Item removed successfully from cart'], 200);
    }

    public function clearAll()
    {
        $user = Auth::user();

        $cart = ShoppingCart::where('user_id', $user->id)->get();

        if (!$cart) {
            return response()->json(['success' => 0, 'message' => 'Your cart has no items to remove'], 500);
        }

        foreach ($cart as $item) {
            $cartItemAmount = $item->amount;

            $product = Product::find($item->product_id);

            if ($product) {
                $product->increment('amount', $cartItemAmount);
            }

            $item->delete();
        }

        return response()->json(['success' => 1, 'message' => 'Cart cleared successfully'], 200);
    }
}
