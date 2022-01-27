<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWTAuth;

class RegisterController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->only('name', 'email', 'password', 'password_confirmation'), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Please fix these errors', 'errors' => $validator->errors()], 500);
        }

        try {

            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);

            $user->save();

            $token = auth()->tokenById($user->id);

            return response()->json([
                'success' => 1,
                'message' => 'User Registration Succesful!',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $user,
                'expires_in' => auth()->factory()->getTTL() * 60

            ]);
        } catch (\Exception $e) {
            //return error message
            return response()->json(['success' => 0, 'message' => 'User Registration Failed!'], 409);
        }
    }
}
