<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Language;
use App\Models\School;

use Mail;
use App\Mail\WelcomeMail;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Str;
use Validator;
use DB;

class UserController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_roles(Request $request){
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

        return response()->json($roles, 200);
    }

    public function get_users(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $per_page = $request->per_page ? $request->per_page : 10;

        $users = User::leftJoin('types_of_status','users.status_type_id','=','types_of_status.status_type_id')
        ->leftJoin('types_of_status_lang','types_of_status.status_type_id','=','types_of_status_lang.status_type_id')
        ->select(
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.phone',
            'users.created_at',
            'types_of_status_lang.status_type_name'
        )
        ->where('users.school_id', '=', auth()->user()->school_id)
        ->where('types_of_status_lang.lang_id', $language->lang_id)
        ->orderBy('users.created_at', 'desc');

        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $email = $request->email;
        $phone = $request->phone;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if (!empty($first_name)){
            $users->where('users.first_name','LIKE','%'.$first_name.'%');
        }

        if (!empty($last_name)){
            $users->where('users.last_name','LIKE','%'.$last_name.'%');
        }

        if (!empty($email)){
            $users->where('users.email','LIKE','%'.$email.'%');
        }

        if (!empty($phone)){
            $users->where('users.phone','LIKE','%'.$phone.'%');
        }

        if ($created_at_from && $created_at_to) {
            $users->whereBetween('users.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $users->where('users.created_at','>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $users->where('users.created_at','<=', $created_at_to.' 23:59:00');
        }

        return response()->json($users->paginate($per_page)->onEachSide(1), 200);
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

    public function invite_user(Request $request){
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

        $find_email = User::where('school_id', '=', auth()->user()->school_id)
        ->where('email', '=', $request->email)
        ->first();

        if(isset($find_email)){
            $email_error = ['email' => trans('auth.user_already_exists')];
            return response()->json($email_error, 422);
        }

        $email_hash = Str::random(32);

        $new_user = new User();
        $new_user->first_name = $request->first_name;
        $new_user->last_name = $request->last_name;
        $new_user->email = $request->email;
        $new_user->phone = $request->phone;
        $new_user->school_id = auth()->user()->school_id;
        $new_user->current_role_id = $request->roles[0];
        $new_user->status_type_id = 4;
        $new_user->email_hash = $email_hash;
        $new_user->save();

        foreach ($request->roles as $key => $value) {
            if($value != 1){
                $user_role = new UserRole();
                $user_role->user_id = $new_user->user_id;
                $user_role->role_type_id = $value;
                $user_role->save();
            }
        }

        $getSchool = School::find(auth()->user()->school_id);

        $mail_body = new \stdClass();
        $mail_body->subject = $getSchool->school_name;
        $mail_body->first_name = $request->first_name;
        $mail_body->activation_url = $request->header('Origin').'/activation/'.$email_hash;
        $mail_body->school_name = $getSchool->school_name;

        Mail::to($new_user->email)->send(new WelcomeMail($mail_body));
        return response()->json($new_user, 200);
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
