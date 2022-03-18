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
    }

    public function store(Request $request)
    {
    }

    public function show($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function getLatestPendingOrders()
    {
    }
}
