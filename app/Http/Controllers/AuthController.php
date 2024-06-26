<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Models\School;
use App\Models\UserOperation;
use App\Models\Language;
use App\Models\PasswordRecovery;
use App\Models\UploadConfiguration;
use App\Models\CourseInvite;
use App\Models\UserCourse;

use Mail;
use Storage;
use App\Mail\PasswordRecoveryMail;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Response;
use File;
use Hash;
use Image;

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
            'school_domain' => 'nullable|required_if:first_registration,true|string|between:2,20|regex:/^[a-z]+$/u|unique:schools',
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
            $school->subscription_expiration_at = date('Y-m-d H:i:s', strtotime('+14 days'));
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
        $user->status_type_id = 1;

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

 public function accept_invitation(Request $request){
    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|between:2,100',
        'last_name' => 'required|string|between:2,100',
        'email' => 'required|string|email|max:100',
        'phone' => 'required|regex:/^((?!_).)*$/s',
        'password' => 'required|string|min:6'
    ]);

    if($validator->fails()){
        return $this->json('error', 'Accept invitation error', 422, $validator->errors());
    }

    $invitation = CourseInvite::leftJoin('courses','courses.course_id','=','courses_invites.course_id')
    ->select(
        'courses_invites.id as invite_id',
        'courses_invites.operator_id',
        'courses_invites.mentor_id',
        'courses_invites.course_cost',
        'courses_invites.subscriber_email',
        'courses_invites.course_id',
        'courses.course_name',
        'courses.school_id'
    )
    ->where('courses_invites.url_hash', '=', $request->hash)
    ->where('courses_invites.status_type_id', '=', 4)
    ->first();

    if(isset($invitation)){
        $getSchoolUser = User::where('email', $request->email)
        ->where('school_id', $invitation->school_id)
        ->first();

        if(isset($getSchoolUser)){
            return $this->json('error', 'Accept invitation error', 422, ['email' => trans('auth.user_already_exists')]);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->school_id = $invitation->school_id;
        $user->password = bcrypt($request->password);
        $user->current_role_id = 4;
        $user->save();

        $user_role = new UserRole();
        $user_role->user_id = $user->user_id;
        $user_role->role_type_id = 4;
        $user_role->save();

        $user_operation = new UserOperation();
        $user_operation->operator_id = $user->user_id;
        $user_operation->operation_type_id = 1;
        $user_operation->save();

        $save_invitation = CourseInvite::find($invitation->invite_id);
        $save_invitation->status_type_id = 11;
        $save_invitation->subscriber_email = $request->email;
        $save_invitation->save();

        $new_user_course = new UserCourse();
        $new_user_course->operator_id = $invitation->operator_id;
        $new_user_course->recipient_id = $user->user_id;
        $new_user_course->mentor_id = $invitation->mentor_id;
        $new_user_course->course_id = $invitation->course_id;
        $new_user_course->cost = $invitation->course_cost;
        $new_user_course->subscribe_type_id = 6;
        $new_user_course->save();

        return $this->json('success', 'Accept invitation successful', 200, ['token' => $user->createToken('API Token')->plainTextToken]);
    }
    else{
        return response()->json('Invitation not found', 404);
    }
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
        return $this->json('error', 'Login error', 422, ['school_domain' => trans('auth.school_not_found')]);
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

public function get_activation_user(Request $request){

    $getActivationUser = User::where('email_hash', $request->hash)
    ->where('status_type_id', 4)
    ->first();

    if(!isset($getActivationUser)){
        return response()->json(['message' => 'Activation user not found'], 404);
    }

    return response()->json($getActivationUser, 200);
}

public function activate_user(Request $request){
    $validator = Validator::make($request->all(), [
        'password' => 'min:6',
        'password_confirmation' => 'min:6|required_with:password|same:password'
    ]);

    if($validator->fails()) {
        return $this->json('error', 'Activation error', 422, $validator->errors());
    }

    $getActivationUser = User::where('email_hash', $request->hash)
    ->where('status_type_id', 4)
    ->first();

    if(!isset($getActivationUser)){
        return response()->json(['message' => 'Activation user not found'], 404);
    }

    $getActivationUser->status_type_id = 1;
    $getActivationUser->password = bcrypt($request->password);
    $getActivationUser->save();

    return $this->json('success', 'Activation successful', 200, ['token' => $getActivationUser->createToken('API Token')->plainTextToken]);
}

public function forgot_password(Request $request){
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'school_domain' => 'required'
    ]);

    if($validator->fails()) {
        return $this->json('error', 'Send verification code error', 422, $validator->errors());
    }

    $getSchool = School::where('school_domain', $request->school_domain)->first();

    if(!isset($getSchool)){
        return $this->json('error', 'Send verification code error', 401, ['school_domain' => trans('auth.school_not_found')]);
    }

    $getSchoolUser = User::where('email', $request->email)
    ->where('school_id', $getSchool->school_id)
    ->first();

    if(!isset($getSchoolUser)){
        return $this->json('error', 'Send verification code error', 401, ['email' => trans('auth.not_found')]);
    }

    if($getSchoolUser->user_status_id == 2){
        return $this->json('error', 'Login error', 401, ['auth_failed' => trans('auth.banned')]);
    }

    $getSchoolUser->status_type_id = 5;
    $getSchoolUser->save();

    $verification_code = rand(10000000, 99999999);

    $new_password_recovery = new PasswordRecovery();
    $new_password_recovery->user_id = $getSchoolUser->user_id;
    $new_password_recovery->verification_code = $verification_code;
    $new_password_recovery->save();

    $mail_body = new \stdClass();
    $mail_body->subject = $getSchool->school_name;
    $mail_body->first_name = $getSchoolUser->first_name;
    $mail_body->verification_url = $request->header('Origin').'/password/recovery?code='.$verification_code;
    $mail_body->verification_code = $verification_code;
    $mail_body->school_name = $getSchool->school_name;

    Mail::to($getSchoolUser->email)->send(new PasswordRecoveryMail($mail_body));

    return response()->json('Verification code successfully sent', 200);
}

public function password_recovery(Request $request){

    $validator = Validator::make($request->all(), [
        'school_domain' => 'required',
        'recovery_code' => 'required|size:8',
        'password' => 'required|min:6',
        'password_confirmation' => 'required|same:password|min:6'
    ]);

    if($validator->fails()) {
        return $this->json('error', 'Send verification code error', 422, $validator->errors());
    }

    $getSchool = School::where('school_domain', $request->school_domain)->first();

    if(!isset($getSchool)){
        return $this->json('error', 'Recovery password error', 422, ['school_domain' => trans('auth.school_not_found')]);
    }

    $get_password_recovery = PasswordRecovery::leftJoin('users', 'password_recovery.user_id', '=', 'users.user_id')
    ->leftJoin('schools', 'users.school_id', '=', 'schools.school_id')
    ->where('password_recovery.verification_code', '=', $request->recovery_code)
    ->where('schools.school_id', '=', $getSchool->school_id)
    ->first();

    if(!isset($get_password_recovery)){
        return $this->json('error', 'Recovery password error', 422, ['recovery_code' => trans('auth.wrong_recovery_code')]);
    }
    else{
        if($get_password_recovery->verification_status_id == 2){
            return $this->json('error', 'Recovery password error', 422, ['recovery_code' => trans('auth.password_was_previously_restored')]);
        }
    }

    $get_password_recovery->verification_status_id = 2;
    $get_password_recovery->save();

    $getUser = User::find($get_password_recovery->user_id);
    $getUser->status_type_id = 1;
    $getUser->password = bcrypt($request->password);
    $getUser->save();

    return $this->json('success', 'Password recovery successful', 200, ['token' => $getUser->createToken('API Token')->plainTextToken]);
}

public function me(Request $request){
    $user = auth()->user();

    $change_user_activity = User::find($user->user_id);
    $change_user_activity->last_activity = date('Y-m-d H:i:s');
    $change_user_activity->ip_address = $request->ip();
    $change_user_activity->save();

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

    foreach ($roles as $key => $role) {
        if($role->role_type_id == $user->current_role_id){
            $user->current_role_name = $role->user_role_type_name;
            break;
        }
    }

    $user->roles = $roles;

    return response()->json([
        'user' => $user
    ], 200);
}

public function update(Request $request){
    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|between:2,100',
        'last_name' => 'required|string|between:2,100',
        'email' => 'required|string|email|max:100',
        'phone' => 'required|regex:/^((?!_).)*$/s',
        'about_me' => 'nullable|string|min:10|max:2000'
    ]);

    if($validator->fails()){
        return response()->json($validator->errors(), 422);
    }

    $user = auth()->user();

    if($user->email != $request->email){
        $find_email = User::where('school_id', '=', auth()->user()->school_id)
        ->where('email', '=', $request->email)
        ->first();

        if(isset($find_email)){
            $email_error = ['email' => trans('auth.user_already_exists')];
            return response()->json($email_error, 422);
        }
    }
    
    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->about_me = $request->about_me;
    $user->save();

    return response()->json([
        'user' => $user
    ], 200);
}

public function get_avatar(Request $request){
    $user = User::where('avatar', '=', $request->avatar_file)
    ->first();

    $path = storage_path('/app/schools/'.$user->school_id.'/avatars/'.$user->user_id.'/'.$user->avatar);

    if (!File::exists($path)) {
        return response()->json('Image not found', 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
}

public function upload_avatar(Request $request){
    $file_type_id = 3;
    $max_image_file_size = UploadConfiguration::where('file_type_id', '=', $file_type_id)
    ->first()->max_file_size_mb;

    $validator = Validator::make($request->all(), [
        'image_file' => 'required|file|mimes:jpg,jpeg,png,gif,svg,webp|max_mb:'.$max_image_file_size
    ]);

    if($validator->fails()){
        return response()->json($validator->errors(), 422);
    }

    if(isset($request->user_id)){
        $user = User::where('user_id', '=', $request->user_id)
        ->where('school_id', '=', auth()->user()->school_id)
        ->first();
    }
    else{
        $user = auth()->user();
    }

    if(isset($user->avatar)){
        $path = storage_path('/app/schools/'.$user->school_id.'/avatars/'.$user->user_id.'/'.$user->avatar);
        File::delete($path);
    }

    $file = $request->file('image_file');
    $file_target = $file->hashName();

    $resized_image = Image::make($file)->resize(300, null, function ($constraint) {
        $constraint->aspectRatio();
    })->stream('png', 20);

    Storage::disk('local')->put('/schools/'.$user->school_id.'/avatars/'.$user->user_id.'/'.$file_target, $resized_image);

    $user->avatar = $file_target;
    $user->save();

    return response()->json([
        'message' => 'Upload avatar successful'
    ], 200);
}

public function delete_avatar(Request $request){
    if(isset($request->user_id)){
        $user = User::where('user_id', '=', $request->user_id)
        ->where('school_id', '=', auth()->user()->school_id)
        ->first();
    }
    else{
        $user = auth()->user();
    }

    if(isset($user->avatar)){
        $path = storage_path('/app/schools/'.$user->school_id.'/avatars/'.$user->user_id.'/'.$user->avatar);
        File::delete($path);
    }
    
    $user->avatar = null;
    $user->save();

    return response()->json([
        'message' => 'Delete avatar successful'
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

public function change_password(Request $request){
    $validator = Validator::make($request->all(), [
        'current_password' => 'required',
        'password' => 'required|min:6',
        'password_confirmation' => 'required|same:password|min:6'
    ]);

    if($validator->fails()) {
        return $this->json('error', 'Change password error', 422, $validator->errors());
    }

    $user = auth()->user();

    if(!(Hash::check($request->current_password, $user->password))){
        return $this->json('error', 'Change password error', 422, [
            'current_password' => 'Неправильный текущий пароль'
        ]);
    }

    $user->password = bcrypt($request->password);
    $user->save();

    return $this->json('success', 'Change password successful', 200, ['token' => $user->createToken('API Token')->plainTextToken]);
}

public function logout(){
    auth()->user()->tokens()->delete();
    return $this->json('success', 'Logout successful', 200, 'Tokens revoked');
}
}