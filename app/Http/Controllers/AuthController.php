<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Models\School;

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
            'school_name' => 'required|string|between:2,100',
            'school_domain' => 'required|string|between:2,20|regex:/(^([a-z]+)(\d+)?$)/u|unique:schools',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Registration error', 422, $validator->errors());
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        $user_role = new UserRole();
        $user_role->user_id = $user->user_id;
        $user_role->role_type_id = 1;
        $user_role->save();

        $school = new School();
        $school->school_domain = $request->school_domain;
        $school->school_name = $request->school_name;
        $school->school_type_id = 1;
        $school->owner_id = $user->user_id;
        $school->email = $user->email;
        $school->phone = $user->phone;
        $school->save();

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
        $user = auth()->user();

        $roles = [];

        foreach (UserRole::where('user_id', $user->user_id)->get() as $key => $value) {
            array_push($roles, $value['role_type_id']);
        }

        return response()->json([
            'user' => $user,
            'roles' => $roles
        ], 200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->json('success', 'Logout successful', 200, 'Tokens revoked');
    }
}