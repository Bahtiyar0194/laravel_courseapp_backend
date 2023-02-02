<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request) {
        app()->setLocale($request->header('Accept-Language'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|numeric|unique:users',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Registration error', 422, $validator->errors());
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return $this->json('success', 'Login successful', 200, ['token' => $user->createToken('API Token')->plainTextToken]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if($validator->fails()) {
            return $this->json('error', 'Login error', 422, $validator->errors());
        }

        if(!Auth::attempt($request->all())){
            return $this->json('error', 'Login error', 401, ['auth_failed' => trans('auth.failed')]);
        }

        if(auth()->user()->ban_status_id == 2){
            return $this->json('error', 'Login error', 401, ['auth_failed' => trans('auth.banned')]);
        }

        return $this->json('success', 'Login successful', 200,  ['token' => auth()->user()->createToken('API Token')->plainTextToken]);
    }

    public function me(Request $request){
        return auth()->user();
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->json('success', 'Logout successful', 200, 'Tokens revoked');
    }
}