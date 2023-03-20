<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Validator;

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
    public function get_users(){
        $users = User::where('school_id', '=', auth()->user()->school_id)
        ->get();

        return response()->json($users, 200);
    }

    public function get_user(Request $request){
        $user = User::where('school_id', '=', auth()->user()->school_id)
        ->where('user_id', '=', $request->user_id)
        ->first();

        return response()->json($user, 200);
    }

    public function update_user(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100',
            'phone' => 'required|regex:/^((?!_).)*$/s'
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
        $user->save();

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
