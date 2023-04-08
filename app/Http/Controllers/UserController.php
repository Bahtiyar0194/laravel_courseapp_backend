<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Validator;
use DB;

class UserController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_users(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $users = User::leftJoin('user_status','users.user_status_id','=','user_status.user_status_id')
        ->leftJoin('user_status_lang','user_status.user_status_id','=','user_status_lang.user_status_id')
        ->select(
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.phone',
            'users.created_at',
            'user_status_lang.user_status_name'
        )
        ->where('users.school_id', '=', auth()->user()->school_id)
        ->where('user_status_lang.lang_id', $language->lang_id)
        ->paginate(1);

        return response()->json($users, 200);
    }

    public function get_user(Request $request){
        $user = User::where('school_id', '=', auth()->user()->school_id)
        ->where('user_id', '=', $request->user_id)
        ->first();

        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $roles = DB::table('types_of_user_roles')
        ->leftJoin('types_of_user_roles_lang','types_of_user_roles.role_type_id','=','types_of_user_roles_lang.role_type_id')
        ->where('types_of_user_roles_lang.lang_id', $language->lang_id)
        ->where('types_of_user_roles.role_type_id', '!=', 1)
        ->select(
            'types_of_user_roles.role_type_id',
            'types_of_user_roles_lang.user_role_type_name'
        )
        ->get();

        foreach ($roles as $key => $role) {
            $find_user_role = UserRole::where('role_type_id', '=', $role->role_type_id)
            ->where('user_id', '=', $user->user_id)
            ->first();

            if(isset($find_user_role)){
                $roles[$key]->selected = true;
            }
            else{
                $roles[$key]->selected = false;
            }
        }

        $user->roles = $roles;

        return response()->json($user, 200);
    }

    public function update_user(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100',
            'phone' => 'required|regex:/^((?!_).)*$/s',
            'roles_count' => 'required|numeric|min:1',
            'roles' => 'required|array'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('school_id', '=', auth()->user()->school_id)
        ->where('user_id', '=', $request->user_id)
        ->first();

        if(isset($user)){
            if($user->email != $request->email){
                $find_email = User::where('school_id', '=', auth()->user()->school_id)
                ->where('email', '=', $request->email)
                ->first();

                if(isset($find_email)){
                    $email_error = ['email' => trans('auth.user_already_exists')];
                    return response()->json($email_error, 422);
                }
            }
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->current_role_id = $request->roles[0];
        $user->save();

        UserRole::where('user_id', $user->user_id)
        ->delete();

        foreach ($request->roles as $key => $value) {
            if($value != 1){
                $user_role = new UserRole();
                $user_role->user_id = $user->user_id;
                $user_role->role_type_id = $value;
                $user_role->save();
            }
        }

        return response()->json($user, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
