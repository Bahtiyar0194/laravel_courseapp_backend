<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Models\School;
use App\Models\UserOperation;
use App\Models\Language;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100',
            'phone' => 'required|regex:/^((?!_).)*$/s',
            'first_registration' => 'required',
            'school_name' => 'nullable|required_if:first_registration,true|string|between:2,100',
            'school_domain' => 'nullable|required_if:first_registration,true|string|between:2,20|regex:/(([a-z]+)(\d+)?$)/u|unique:schools',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Registration error', 422, $validator->errors());
        }

        if($request->first_registration == 'true'){
            $school = new School();
            $school->school_domain = str_replace(' ', '', $request->school_domain);
            $school->school_name = $request->school_name;
            $school->school_type_id = 1;
            $school->save();
        }
        elseif($request->first_registration == 'false'){
            $origin = parse_url($request->header('Origin'));
            $host = $origin['host'];
            $parts = explode('.', $host);

            if(count($parts) >= 2){
                $subdomain = $parts[0];
                $school = School::where('school_domain', $subdomain)->first();

                if(!isset($school)){
                    return $this->json('error', 'Registration error', 422, ['registration_failed' => trans('auth.school_not_found')]);
                }

                $getSchoolUser = User::where('email', $request->email)
                ->where('school_id', $school->school_id)
                ->first();

                if(isset($getSchoolUser)){
                    return $this->json('error', 'Registration error', 422, ['email' => trans('auth.user_already_exists')]);
                }
            }
        }
        else{
            return $this->json('error', 'Registration error', 422, ['registration_failed' => 'First registration: true or false']);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->school_id = $school->school_id;
        $user->password = bcrypt($request->password);
        if($request->first_registration == 'true'){
            $user->current_role_id = 2;
        }
        elseif($request->first_registration == 'false'){
            $user->current_role_id = 4;
        }

        $user->save();

        if($request->first_registration == 'true'){

            $user_role = new UserRole();
            $user_role->user_id = $user->user_id;
            $user_role->role_type_id = 2;
            $user_role->save();

            $user_role = new UserRole();
            $user_role->user_id = $user->user_id;
            $user_role->role_type_id = 3;
            $user_role->save();

            $user_role = new UserRole();
            $user_role->user_id = $user->user_id;
            $user_role->role_type_id = 4;
            $user_role->save();

            $user_operation = new UserOperation();
            $user_operation->operator_id = $user->user_id;
            $user_operation->operation_type_id = 2;
            $user_operation->save();
        }
        elseif($request->first_registration == 'false'){
           $user_role = new UserRole();
           $user_role->user_id = $user->user_id;
           $user_role->role_type_id = 4;
           $user_role->save();
       }

       $user_operation = new UserOperation();
       $user_operation->operator_id = $user->user_id;
       $user_operation->operation_type_id = 1;
       $user_operation->save();

       return $this->json('success', 'Registration successful', 200, ['token' => $user->createToken('API Token')->plainTextToken]);
   }

   public function login(Request $request){
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
        'school_domain' => 'required'
    ]);

    if($validator->fails()) {
        return $this->json('error', 'Login error', 422, $validator->errors());
    }

    $getSchool = School::where('school_domain', $request->school_domain)->first();

    if(!isset($getSchool)){
        return $this->json('error', 'Login error', 401, ['auth_failed' => trans('auth.school_not_found')]);
    }

    $getSchoolUser = User::where('email', $request->email)
    ->where('school_id', $getSchool->school_id)
    ->first();

    if(!isset($getSchoolUser)){
        return $this->json('error', 'Login error', 401, ['email' => trans('auth.not_found')]);
    }

    $userdata = array(
        'school_id' => $getSchoolUser->school_id,
        'email' => $request->email,
        'password' => $request->password,
    );

    if (!Auth::attempt($userdata)) {
        return $this->json('error', 'Login error', 401, ['auth_failed' => trans('auth.failed')]);
    }

    if(auth()->user()->user_status_id == 2){
        return $this->json('error', 'Login error', 401, ['auth_failed' => trans('auth.banned')]);
    }

    return $this->json('success', 'Login successful', 200,  ['token' => auth()->user()->createToken('API Token')->plainTextToken]);
}

public function me(Request $request){
    $user = auth()->user();

    $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

    $roles = UserRole::leftJoin('types_of_user_roles', 'users_roles.role_type_id', '=', 'types_of_user_roles.role_type_id')
    ->leftJoin('types_of_user_roles_lang', 'types_of_user_roles.role_type_id', '=', 'types_of_user_roles_lang.role_type_id')
    ->where('users_roles.user_id', $user->user_id)
    ->where('types_of_user_roles_lang.lang_id', $language->lang_id)
    ->select(
        'users_roles.role_type_id',
        'types_of_user_roles.role_type_slug',
        'types_of_user_roles_lang.user_role_type_name'
    )
    ->get();

    $user->roles = $roles;

    return response()->json([
        'user' => $user
    ], 200);
}

public function change_mode(Request $request){
   $user = auth()->user();
   $role_found = false;

   $roles = UserRole::where('user_id', $user->user_id)
   ->select('role_type_id')->get();

   foreach ($roles as $key => $value) {
    if($value->role_type_id == $request->role_type_id){
        $role_found = true;
        break;
    }
}

if($role_found === true){
    $change_user = User::find($user->user_id);
    $change_user->current_role_id = $request->role_type_id;
    $change_user->save();

    return response()->json('User mode change successful', 200);
}
else{
   return response()->json('Access denied', 403);
}
}

public function logout(){
    auth()->user()->tokens()->delete();
    return $this->json('success', 'Logout successful', 200, 'Tokens revoked');
}
}