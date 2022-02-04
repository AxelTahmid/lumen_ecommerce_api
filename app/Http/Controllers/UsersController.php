<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin_check:store-update-destroy');
    }

    public function index(Request $request)
    {
        $users = $this->filterAndResponse($request);

        return response()->json(['users' => $users], 200);
    }

    public function store(Request $request)
    {
        $validator = $this->getValidator($request);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Please fix these errors', 'errors' => $validator->errors()], 500);
        }

        $bundle = $request->except('password_confirmation');

        $bundle['password'] = app('hash')->make($request->input('password'));

        $user = User::create($bundle);

        return response()->json(['success' => 1, 'message' => 'Created successfully', 'user' => $user], 201);
    }

    public function show($id)
    {
        return response()->json(['user' => User::findOrFail($id)], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = $this->getValidator($request, $id);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Please fix these errors', 'errors' => $validator->errors()], 500);
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->is_super_admin = $request->input('is_super_admin');

        if ($request->input('password') != "") {
            $user->password = app('hash')->make($request->input('password'));
        }

        $user->save();

        return response()->json(['success' => 1, 'message' => 'Updated successfully', 'user' => $user], 200);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return response()->json(['success' => 1, 'message' => 'Deleted successfully'], 200);
    }

    /**
     * @param Request $request
     */
    protected function filterAndResponse(Request $request)
    {
        $query = User::with("products", "orders")->orderBy('id', 'DESC');

        if ($request->has('all')) {
            return $query->get();
        }

        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        if ($request->has('name')) {
            $query->where('name', 'like', "%" . $request->name . "%");
        }

        if ($request->has('email')) {
            $query->where('email', 'like', "%" . $request->email . "%");
        }

        $users = $query->paginate(10);

        return $users;
    }

    private function getValidator($request, $id = null)
    {
        $validate_rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|' . ($id == null ? 'unique:users,email' : 'unique:users,email,' . $id),
        ];

        if ($id == null || $request->input('password') != "") {
            $validate_rules += ['password' => 'required|min:4|confirmed'];
        }

        $validator = Validator::make($request->all(), $validate_rules);

        return $validator;
    }
}
