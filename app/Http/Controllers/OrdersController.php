<?php

namespace App\Http\Controllers;


use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{
    use Helpers;

    public function __construct()
    {
        $this->middleware('super_admin_check:show-update-getLatestPendingOrders');
    }

    public function index(Request $request)
    {
        $orders = $this->retrieveOrders($request);

        return response()->json(["orders" => $orders]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $cart = ShoppingCart::where("user_id", $user->id)->get();

        if (!$cart) {
            return response()->json(["success" => 0, "message" => "You must have items in the cart in order to checkout!"], 500);
        }

        $validator = Validator::make($request->all(), [
            "payment_method" => "required|exists:payment_methods,slug",
            "shipping_address_id" => "required|exists:shipping_addresses,id"
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Required fields', 'errors' => $validator->errors()], 500);
        }

        $paymentMethod = PaymentMethod::where('slug', $request->input("payment_method"))->first();

        $order = new Order();
        $order->user_id = $user->id;

        if ($request->input("payment_method") !== 'paypal') {
            $order->status = "pending";
        } else {
            $order->status = $request->input("status");
        }
        $order->status_message = $request->input("status_message");
        $order->payment_method_id = $paymentMethod->id;
        $order->shipping_address_id = $request->input("shipping_address_id");
        $order->total_price = $this->getOrderTotal($cart);

        if ($request->has("paypal_order_id")) {
            $order->paypal_order_identifier = $request->input("paypal_order_id");
        }

        if ($request->has("paypal_email")) {
            $order->paypal_email = $request->input("paypal_email");
        }

        if ($request->has("paypal_given_name")) {
            $order->paypal_given_name = $request->input("paypal_given_name");
        }

        if ($request->has("paypal_payer_id")) {
            $order->paypal_payer_id = $request->input("paypal_payer_id");
        }

        $order->save();

        // saving order details
        foreach ($cart as $item) {
            $orderDetails = new OrderDetail();
            $orderDetails->order_id = $order->id;
            $orderDetails->product_id = $item->product_id;
            $orderDetails->price = $item->product->price_after_discount_numeric;
            $orderDetails->amount = $item->amount;
            $orderDetails->save();
        }

        // clear shopping cart
        foreach ($cart as $item) {
            $item->delete();
        }

        // if order cancelled restore the order products into the product inventory
        if ($order->status == "cancelled") {
            $this->restoreProducts($order);
        }

        $order = Order::with("user")->find($order->id);

        return response()->json(['success' => 1, "message" => "Order saved successfully!", "order" => $order]);
    }

    public function show($id)
    {
        $order = Order::with("orderDetails", "paymentMethod", "shippingAddress", "user")->find($id);

        if (!$order) {
            return response()->json(['success' => 0, 'message' => 'Not found'], 404);
        }

        return response()->json(['orderDetails' => $order], 200);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        $order->status = $request->input("status");

        $order->save();

        return response()->json(['success' => 1, "message" => "Order updated!", "order" => $order]);
    }

    public function getLatestPendingOrders()
    {
        $topOrders = Order::with("user")->where("status", "pending")->orderBy("created_at", "DESC")->limit(4)->get();

        $countAllPending = Order::with("user")->where("status", "pending")->count();

        return response()->json(["topOrders" => $topOrders, "countAllPending" => $countAllPending]);
    }

    private function getOrderTotal($cart)
    {
        $total = 0;

        foreach ($cart as $item) {
            $total += $item->total_price_numeric;
        }

        return $total;
    }

    private function restoreProducts($order)
    {
        $orderDetails = OrderDetail::where('order_id', $order->id)->get();

        if (!$orderDetails) {
            return;
        }

        foreach ($orderDetails as $item) {
            $amount = $item->amount;

            $product = Product::find($item->product_id);

            if ($product) {
                $product->increment('amount', $amount);
            }
        }
    }


    protected function retrieveOrders($request)
    {
        if ($this->superAdminCheck()) {
            $data = Order::with("orderDetails", "paymentMethod", "shippingAddress", "user");
        } else {
            $data = Order::with("orderDetails", "paymentMethod", "shippingAddress", "user")->where("user_id", Auth::user()->id);
        }

        if ($request->has("orderId")) {
            $data->where("id", $request->input("orderId"));
        }

        if ($request->has("userId")) {
            $data->where("user_id", $request->input("userId"));
        }

        if ($request->has("status")) {
            $data->where("status", $request->input("status"));
        }

        $orders = $data->orderBy("id", "DESC");

        if ($this->superAdminCheck()) {
            return $orders->paginate(20);
        } else {
            return $orders->get();
        }
    }
}
