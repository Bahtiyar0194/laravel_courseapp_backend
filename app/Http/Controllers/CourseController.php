<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\UserOperation;
use App\Models\UserCourse;
use App\Models\User;
use App\Models\School;
use App\Models\UserRole;
use App\Models\Language;
use App\Models\CourseLevelType;
use App\Models\CourseMentor;
use App\Models\CourseInvite;
use App\Models\CourseRequest;
use App\Models\CourseSkill;
use App\Models\CourseSuitable;
use App\Models\CourseRequirement;
use App\Models\CourseReview;
use App\Models\UploadConfiguration;
use Iman\Streamer\VideoStreamer;

use Str;
use Mail;
use App\Mail\CourseInvitationMail;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use File;
use App\Http\Controllers\Controller;
use Validator;
use DB;

class CourseController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_course_attributes(Request $request){
        $categories = CourseCategory::leftJoin('course_categories_lang', 'course_categories.course_category_id', '=', 'course_categories_lang.course_category_id')
        ->leftJoin('languages', 'course_categories_lang.lang_id', '=', 'languages.lang_id')
        ->select(
         'course_categories.course_category_id',
         'course_categories_lang.course_category_name')
        ->where('languages.lang_tag', $request->header('Accept-Language'))
        ->orderBy('course_categories.course_category_id')
        ->get();

        $languages = Language::leftJoin('languages_lang', 'languages_lang.lang_id', '=', 'languages.lang_id')
        ->select(
            'languages.lang_id',
            'languages_lang.lang_name'
        )
        ->where('languages_lang.lang_tag', $request->header('Accept-Language'))
        ->get();

        $levels = CourseLevelType::leftJoin('types_of_course_level_lang', 'types_of_course_level.level_type_id', '=', 'types_of_course_level_lang.level_type_id')
        ->leftJoin('languages', 'types_of_course_level_lang.lang_id', '=', 'languages.lang_id')
        ->select(
            'types_of_course_level.level_type_id',
            'types_of_course_level_lang.level_type_name'
        )
        ->where('languages.lang_tag', $request->header('Accept-Language'))
        ->orderBy('types_of_course_level.level_type_id')
        ->get();

        $authors = DB::table('users')
        ->leftJoin('users_roles', 'users.user_id', '=', 'users_roles.user_id')
        ->where('users.school_id', '=', auth()->user()->school_id)
        ->select(
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'users.avatar',
            'users_roles.role_type_id'
        )
        ->groupBy('users_roles.user_id')
        ->havingRaw('users_roles.role_type_id in (2,3)')
        ->get();

        $attributes = new \stdClass();

        $attributes->course_categories = $categories;
        $attributes->course_languages = $languages;
        $attributes->course_levels = $levels;
        $attributes->course_authors = $authors;

        return response()->json($attributes, 200);
    }

    public function get_courses(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $my_courses = Course::leftJoin('course_categories','courses.course_category_id','=','course_categories.course_category_id')
        ->leftJoin('course_categories_lang','course_categories.course_category_id','=','course_categories_lang.course_category_id')
        ->leftJoin('languages','courses.course_lang_id','=','languages.lang_id')
        ->leftJoin('languages_lang','languages.lang_id','=','languages_lang.lang_id')
        ->select(
            'courses.course_id',
            'courses.course_name',
            'courses.course_description',
            'courses.course_poster_file',
            'courses.course_cost',
            'courses.created_at',
            'course_categories_lang.course_category_name',
            'languages_lang.lang_name'
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('courses.show_status_id', 1)
        ->where('languages_lang.lang_tag', $language->lang_tag)
        ->where('course_categories_lang.lang_id', $language->lang_id)
        ->get();

        return response()->json($my_courses, 200);
    }

    public function my_courses(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $my_courses = Course::leftJoin('users_courses','courses.course_id','=','users_courses.course_id')
        ->leftJoin('course_categories', 'courses.course_category_id', '=', 'course_categories.course_category_id')
        ->leftJoin('course_categories_lang', 'course_categories.course_category_id', '=', 'course_categories_lang.course_category_id')
        ->leftJoin('languages', 'courses.course_lang_id', '=', 'languages.lang_id')
        ->leftJoin('languages_lang', 'languages.lang_id', '=', 'languages_lang.lang_id')
        ->select(
            'courses.course_id',
            'courses.course_name',
            'courses.course_description',
            'courses.course_poster_file',
            'courses.course_cost',
            'courses.created_at',
            'course_categories_lang.course_category_name',
            'languages_lang.lang_name'
        )
        ->where('users_courses.recipient_id', auth()->user()->user_id)
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('courses.show_status_id', 1)
        ->where('languages_lang.lang_tag', $language->lang_tag)
        ->where('course_categories_lang.lang_id', $language->lang_id)
        ->get();

        return response()->json($my_courses, 200);
    }

    public function course(Request $request){

        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $course = Course::leftJoin('course_categories','courses.course_category_id','=','course_categories.course_category_id')
        ->leftJoin('course_categories_lang','course_categories.course_category_id','=','course_categories_lang.course_category_id')
        ->leftJoin('types_of_course_level', 'courses.level_type_id', '=', 'types_of_course_level.level_type_id')
        ->leftJoin('types_of_course_level_lang', 'types_of_course_level.level_type_id', '=', 'types_of_course_level_lang.level_type_id')
        ->leftJoin('languages','courses.course_lang_id','=','languages.lang_id')
        ->leftJoin('languages_lang','languages.lang_id','=','languages_lang.lang_id')
        ->leftJoin('users', 'courses.author_id', '=', 'users.user_id')
        ->select(
            'courses.course_id',
            'courses.course_name',
            'courses.course_description',
            'courses.course_content',
            'courses.course_poster_file',
            'courses.course_trailer_file',
            'courses.course_cost',
            'courses.course_category_id',
            'courses.course_lang_id',
            'courses.level_type_id',
            'courses.author_id',
            'courses.created_at',
            'course_categories_lang.course_category_name',
            'languages_lang.lang_name',
            'types_of_course_level.level_type_id',
            'types_of_course_level_lang.level_type_name',
            'users.last_name',
            'users.first_name',
            'users.avatar'
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('courses.course_id', $request->course_id)
        ->where('languages_lang.lang_tag', $language->lang_tag)
        ->where('course_categories_lang.lang_id', $language->lang_id)
        ->where('types_of_course_level_lang.lang_id', $language->lang_id)
        ->first();

        if(isset($course)){
            $subscribed = UserCourse::where('recipient_id', '=', auth()->user()->user_id)
            ->where('course_id', '=', $request->course_id)
            ->first();

            if(isset($subscribed)){
                $course->subscribed = true;
            }
            else{
                $course->subscribed = false;
            }

            $subscribers = UserCourse::leftJoin('users as operator','users_courses.operator_id','=','operator.user_id')
            ->leftJoin('users as recipient','users_courses.recipient_id','=','recipient.user_id')
            ->leftJoin('types_of_course_subscribes','users_courses.subscribe_type_id','=','types_of_course_subscribes.subscribe_type_id')
            ->leftJoin('types_of_course_subscribes_lang','types_of_course_subscribes.subscribe_type_id','=','types_of_course_subscribes_lang.subscribe_type_id')
            ->select(
                'operator.first_name as operator_first_name',
                'operator.last_name as operator_last_name',
                'recipient.first_name as recipient_first_name',
                'recipient.last_name as recipient_last_name',
                'users_courses.id',
                'users_courses.cost',
                'users_courses.created_at',
                'types_of_course_subscribes_lang.subscribe_type_name'
            )
            ->where('users_courses.course_id', '=', $request->course_id)
            ->where('types_of_course_subscribes_lang.lang_id', $language->lang_id)
            ->get();

            $mentors = DB::table('users')
            ->leftJoin('courses_mentors', 'users.user_id', '=', 'courses_mentors.mentor_id')
            ->where('courses_mentors.course_id', '=', $course->course_id)
            ->select(
                'users.user_id',
                'users.first_name',
                'users.last_name',
                'users.avatar'
            )
            ->groupBy('courses_mentors.mentor_id')
            ->get();

            $mentors_id = [];

            foreach ($mentors as $key => $value) {
                array_push($mentors_id, $value->user_id);
            }

            $subscribe_types = DB::table('types_of_course_subscribes')
            ->leftJoin('types_of_course_subscribes_lang', 'types_of_course_subscribes.subscribe_type_id', '=', 'types_of_course_subscribes_lang.subscribe_type_id')
            ->leftJoin('languages', 'types_of_course_subscribes_lang.lang_id', '=', 'languages.lang_id')
            ->select(
             'types_of_course_subscribes.subscribe_type_id',
             'types_of_course_subscribes_lang.subscribe_type_name')
            ->where('languages.lang_tag', $request->header('Accept-Language'))
            ->orderBy('types_of_course_subscribes.subscribe_type_id')
            ->get();

            $in_the_request = CourseRequest::where('course_id', '=', $request->course_id)
            ->where('status_type_id', '=', 12)
            ->count();

            $course->in_the_request = $in_the_request;

            $skills = CourseSkill::where('course_id', '=', $course->course_id)
            ->get();

            $suitables = CourseSuitable::where('course_id', '=', $course->course_id)
            ->get();

            $requirements = CourseRequirement::where('course_id', '=', $course->course_id)
            ->get();

            $reviews = CourseReview::leftJoin('users','course_reviews.user_id','=','users.user_id')
            ->where('course_reviews.course_id', '=', $course->course_id)
            ->select(
                'course_reviews.id',
                'users.user_id',
                'users.last_name',
                'users.first_name',
                'users.avatar',
                'course_reviews.rating',
                'course_reviews.review',
                'course_reviews.created_at',
            )
            ->get();

            $my_review = false;

            foreach ($reviews as $key => $review) {
                if($review->user_id == auth()->user()->user_id){
                    $my_review = true;
                    break;
                }
            }

            $course->subscribe_types = $subscribe_types;
            $course->subscribers = $subscribers;
            $course->mentors = $mentors;
            $course->mentors_id = $mentors_id;
            $course->skills = $skills;
            $course->suitables = $suitables;
            $course->requirements = $requirements;
            $course->reviews = $reviews;
            $course->reviewers_count = count($reviews);
            $course->my_review = $my_review;

            $rating = 0;

            if(count($reviews) > 0){
             $rating = $reviews->sum('rating') / count($reviews);
         }

         $course->rating = $rating;

         return response()->json($course, 200);
     }
     else{
        return response()->json('Course not found', 404);
    }
}

public function create(Request $request){

    $poster_max_file_size = UploadConfiguration::where('file_type_id', '=', 3)
    ->first()->max_file_size_mb;

    $trailer_max_file_size = UploadConfiguration::where('file_type_id', '=', 1)
    ->first()->max_file_size_mb;

    $school_id = auth()->user()->school_id;

    $validator = Validator::make($request->all(), [
        'course_name' => 'required|string|between:3, 300',
        'course_description' => 'required|string|max:1000',
        'course_content' => 'required|string|max:10000',
        'course_category_id' => 'required|numeric',
        'course_lang_id' => 'required|numeric',
        'level_type_id' => 'required|numeric',
        'author_id' => 'required|numeric',
        'course_mentors_count' => 'required|numeric|min:1',
        'course_poster_file' => 'required|file|mimes:jpg,png,jpeg,gif,svg|max_mb:'.$poster_max_file_size,
        'course_trailer_file' => 'nullable|file|mimes:mp4,ogx,oga,ogv,ogg,webm|max_mb:'.$trailer_max_file_size,
        'course_free' => 'required',
        'course_cost' => 'nullable|required_if:course_free,false|numeric|min:1',
    ]);

    if($validator->fails()){
        return $this->json('error', 'Course create error', 422, $validator->errors());
    }

    if($request->course_free == 'false'){
        $course_cost = $request->course_cost;
    }
    else{
        $course_cost = 0;
    }

    if(isset($request->course_poster_file)){
        $poster_file = $request->file('course_poster_file');
        $poster_file_name = $poster_file->hashName();
    }

    if(isset($request->course_trailer_file)){
        $trailer_file = $request->file('course_trailer_file');
        $trailer_file_name = $trailer_file->hashName();
    }
    else{
        $trailer_file_name = null;
    }

    $new_course = new Course();
    $new_course->course_name = $request->course_name;
    $new_course->course_description = $request->course_description;
    $new_course->course_content = $request->course_content;
    $new_course->course_poster_file = $poster_file_name;
    $new_course->course_trailer_file = $trailer_file_name;
    $new_course->course_category_id = $request->course_category_id;
    $new_course->school_id = $school_id;
    $new_course->author_id = $request->author_id;
    $new_course->course_lang_id = $request->course_lang_id;
    $new_course->level_type_id = $request->level_type_id;
    $new_course->course_cost = $course_cost;
    $new_course->save();


    if(isset($request->course_poster_file)){
        $poster_file->storeAs('schools/'.$school_id.'/course_posters/'.$new_course->course_id.'/', $poster_file_name);
    }

    if(isset($request->course_trailer_file)){
        $trailer_file->storeAs('schools/'.$school_id.'/course_trailers/'.$new_course->course_id.'/', $trailer_file_name);
    }

    $course_mentors = json_decode($request->course_mentors);

    if(count($course_mentors) > 0){
        foreach ($course_mentors as $key => $mentor) {
            $new_mentor = new CourseMentor();
            $new_mentor->course_id = $new_course->course_id;
            $new_mentor->mentor_id = $mentor;
            $new_mentor->save();

            $new_user_course = new UserCourse();
            $new_user_course->operator_id = auth()->user()->user_id;
            $new_user_course->recipient_id = $mentor;
            $new_user_course->mentor_id = $course_mentors[0];
            $new_user_course->course_id = $new_course->course_id;
            $new_user_course->cost = 0;
            $new_user_course->subscribe_type_id = 4;
            $new_user_course->save();
        }
    }

    $course_skills = json_decode($request->course_skills);

    if(count($course_skills) > 0){
        foreach ($course_skills as $key => $skill) {
            $new_skill = new CourseSkill();
            $new_skill->course_id = $new_course->course_id;
            $new_skill->item_value = $skill->item_value;
            $new_skill->save();
        }
    }

    $course_suitables = json_decode($request->course_suitables);

    if(count($course_suitables) > 0){
        foreach ($course_suitables as $key => $suitable) {
            $new_suitable = new CourseSuitable();
            $new_suitable->course_id = $new_course->course_id;
            $new_suitable->item_value = $suitable->item_value;
            $new_suitable->save();
        }
    }

    $course_requirements = json_decode($request->course_requirements);

    if(count($course_requirements) > 0){
        foreach ($course_requirements as $key => $requirement) {
            $new_requirement = new CourseRequirement();
            $new_requirement->course_id = $new_course->course_id;
            $new_requirement->item_value = $requirement->item_value;
            $new_requirement->save();
        }
    }

    $user_operation = new UserOperation();
    $user_operation->operator_id = auth()->user()->user_id;
    $user_operation->operation_type_id = 3;
    $user_operation->save();

    if($request->author_id != auth()->user()->user_id){
        $new_user_course = new UserCourse();
        $new_user_course->operator_id = auth()->user()->user_id;
        $new_user_course->recipient_id = $request->author_id;
        $new_user_course->course_id = $new_course->course_id;
        $new_user_course->cost = 0;
        $new_user_course->subscribe_type_id = 2;
        $new_user_course->save();
    }

    return $this->json('success', 'Course create successful', 200, $new_course);
}

public function update(Request $request){

    $poster_max_file_size = UploadConfiguration::where('file_type_id', '=', 3)
    ->first()->max_file_size_mb;

    $trailer_max_file_size = UploadConfiguration::where('file_type_id', '=', 1)
    ->first()->max_file_size_mb;

    $school_id = auth()->user()->school_id;

    $validator = Validator::make($request->all(), [
        'course_name' => 'required|string|between:3, 300',
        'course_description' => 'required|string|max:1000',
        'course_content' => 'required|string|max:10000',
        'course_category_id' => 'required|numeric',
        'course_lang_id' => 'required|numeric',
        'level_type_id' => 'required|numeric',
        'author_id' => 'required|numeric',
        'course_mentors_count' => 'required|numeric|min:1',
        'old_course_poster' => 'required',
        'new_course_poster_file' => 'nullable|required_if:old_course_poster,false|file|mimes:jpg,png,jpeg,gif,svg|max_mb:'.$poster_max_file_size,
        'old_course_trailer' => 'required',
        'new_course_trailer_file' => 'nullable|file|mimes:mp4,ogx,oga,ogv,ogg,webm|max_mb:'.$trailer_max_file_size,
        'course_free' => 'required',
        'course_cost' => 'nullable|required_if:course_free,false|numeric|min:1'
    ]);

    if($validator->fails()){
        return $this->json('error', 'Course create error', 422, $validator->errors());
    }

    if($request->course_free == 'false'){
        $course_cost = $request->course_cost;
    }
    else{
        $course_cost = 0;
    }

    $edit_course = Course::where('course_id', '=', $request->course_id)
    ->where('school_id', '=', $school_id)
    ->first();

    if(!isset($edit_course)){
        return response()->json('Course not found', 404);
    }

    if(isset($request->new_course_poster_file)){
        $poster_file = $request->file('new_course_poster_file');
        $poster_file_name = $poster_file->hashName();

        if(isset($edit_course->course_poster_file)){
            $path = storage_path('/app/schools/'.$school_id.'/course_posters/'.$edit_course->course_id.'/'.$edit_course->course_poster_file);
            File::delete($path);
        }
    }
    else{
        $poster_file_name = $edit_course->course_poster_file;
    }

    if(isset($request->new_course_trailer_file)){
        $trailer_file = $request->file('new_course_trailer_file');
        $trailer_file_name = $trailer_file->hashName();

        if(isset($edit_course->course_trailer_file)){
            $path = storage_path('/app/schools/'.$school_id.'/course_trailers/'.$edit_course->course_id.'/'.$edit_course->course_trailer_file);
            File::delete($path);
        }
    }
    else{
        if($request->old_course_trailer == 'false'){
            $path = storage_path('/app/schools/'.$school_id.'/course_trailers/'.$edit_course->course_id.'/'.$edit_course->course_trailer_file);
            File::delete($path);
            
            $trailer_file_name = null;
        }
        else{
            $trailer_file_name = $edit_course->course_trailer_file;
        }
    }

    $edit_course->course_name = $request->course_name;
    $edit_course->course_description = $request->course_description;
    $edit_course->course_content = $request->course_content;
    $edit_course->course_poster_file = $poster_file_name;
    $edit_course->course_trailer_file = $trailer_file_name;
    $edit_course->course_category_id = $request->course_category_id;
    $edit_course->school_id = $school_id;
    $edit_course->author_id = $request->author_id;
    $edit_course->course_lang_id = $request->course_lang_id;
    $edit_course->level_type_id = $request->level_type_id;
    $edit_course->course_cost = $course_cost;
    $edit_course->save();

    if(isset($request->new_course_poster_file)){
        $poster_file->storeAs('schools/'.$school_id.'/course_posters/'.$edit_course->course_id.'/', $poster_file_name);
    }

    if(isset($request->new_course_trailer_file)){
        $trailer_file->storeAs('schools/'.$school_id.'/course_trailers/'.$edit_course->course_id.'/', $trailer_file_name);
    }

    $course_mentors = json_decode($request->course_mentors);

    CourseMentor::where('course_id', '=', $edit_course->course_id)
    ->delete();

    UserCourse::where('course_id', '=', $edit_course->course_id)
    ->where('subscribe_type_id', '=', 4)
    ->delete();

    if(count($course_mentors) > 0){
        foreach ($course_mentors as $key => $mentor) {
            $new_mentor = new CourseMentor();
            $new_mentor->course_id = $edit_course->course_id;
            $new_mentor->mentor_id = $mentor;
            $new_mentor->save();

            $new_user_course = new UserCourse();
            $new_user_course->operator_id = auth()->user()->user_id;
            $new_user_course->recipient_id = $mentor;
            $new_user_course->mentor_id = $course_mentors[0];
            $new_user_course->course_id = $edit_course->course_id;
            $new_user_course->cost = 0;
            $new_user_course->subscribe_type_id = 4;
            $new_user_course->save();
        }
    }

    $course_skills = json_decode($request->course_skills);

    CourseSkill::where('course_id', '=', $edit_course->course_id)
    ->delete();

    if(count($course_skills) > 0){
        foreach ($course_skills as $key => $skill) {
            $new_skill = new CourseSkill();
            $new_skill->course_id = $edit_course->course_id;
            $new_skill->item_value = $skill->item_value;
            $new_skill->save();
        }
    }

    $course_suitables = json_decode($request->course_suitables);

    CourseSuitable::where('course_id', '=', $edit_course->course_id)
    ->delete();

    if(count($course_suitables) > 0){
        foreach ($course_suitables as $key => $suitable) {
            $new_suitable = new CourseSuitable();
            $new_suitable->course_id = $edit_course->course_id;
            $new_suitable->item_value = $suitable->item_value;
            $new_suitable->save();
        }
    }

    $course_requirements = json_decode($request->course_requirements);

    CourseRequirement::where('course_id', '=', $edit_course->course_id)
    ->delete();

    if(count($course_requirements) > 0){
        foreach ($course_requirements as $key => $requirement) {
            $new_requirement = new CourseRequirement();
            $new_requirement->course_id = $edit_course->course_id;
            $new_requirement->item_value = $requirement->item_value;
            $new_requirement->save();
        }
    }

    $user_operation = new UserOperation();
    $user_operation->operator_id = auth()->user()->user_id;
    $user_operation->operation_type_id = 16;
    $user_operation->save();

    return $this->json('success', 'Course update successful', 200, $edit_course);
}

public function free_subscribe(Request $request){
    $find_course = Course::where('course_id', '=', $request->course_id)
    ->where('school_id', '=', auth()->user()->school_id)
    ->first();

    if(isset($find_course)){
        if($find_course->course_cost == 0){
            $new_user_course = new UserCourse();
            $new_user_course->operator_id = auth()->user()->user_id;
            $new_user_course->recipient_id = auth()->user()->user_id;
            $new_user_course->course_id = $request->course_id;
            $new_user_course->cost = $find_course->course_cost;
            $new_user_course->subscribe_type_id = 1;
            $new_user_course->save();

            return response()->json('Subscribe success', 200);
        }
        else{
            return response()->json('Access denied', 403);
        }
    }
    else{
        return response()->json('Course not found', 404);
    }
}

public function get_subscribers(Request $request){
    $per_page = $request->per_page ? $request->per_page : 10;

    $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

    $find_course = Course::where('course_id', '=', $request->course_id)
    ->where('school_id', '=', auth()->user()->school_id)
    ->first();

    if(isset($find_course)){
        $subscribers = UserCourse::leftJoin('users as operator','users_courses.operator_id','=','operator.user_id')
        ->leftJoin('users as recipient','users_courses.recipient_id','=','recipient.user_id')
        ->leftJoin('users as mentor','users_courses.mentor_id','=','mentor.user_id')
        ->leftJoin('types_of_course_subscribes','users_courses.subscribe_type_id','=','types_of_course_subscribes.subscribe_type_id')
        ->leftJoin('types_of_course_subscribes_lang','types_of_course_subscribes.subscribe_type_id','=','types_of_course_subscribes_lang.subscribe_type_id')
        ->select(
            'operator.first_name as operator_first_name',
            'operator.last_name as operator_last_name',
            'recipient.first_name as recipient_first_name',
            'recipient.last_name as recipient_last_name',
            'mentor.first_name as mentor_first_name',
            'mentor.last_name as mentor_last_name',
            'recipient.user_id',
            'users_courses.id',
            'users_courses.cost',
            'users_courses.created_at',
            'recipient.avatar',
            'recipient.email',
            'types_of_course_subscribes_lang.subscribe_type_name'
        )
        ->where('users_courses.course_id', '=', $request->course_id)
        ->where('types_of_course_subscribes_lang.lang_id', $language->lang_id)
        ->orderBy('users_courses.created_at', 'desc');

        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $subscribe_type_id = $request->subscribe_type_id;
        $email = $request->email;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if(!empty($first_name)){
            $subscribers->where('recipient.first_name','LIKE','%'.$first_name.'%');
        }

        if(!empty($last_name)){
            $subscribers->where('recipient.last_name','LIKE','%'.$last_name.'%');
        }

        if(!empty($subscribe_type_id)){
            $subscribers->where('users_courses.subscribe_type_id','=', $subscribe_type_id);
        }

        if(!empty($email)){
            $subscribers->where('recipient.email','LIKE','%'.$email.'%');
        }

        if($created_at_from && $created_at_to) {
            $subscribers->whereBetween('users_courses.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $subscribers->where('users_courses.created_at','>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $subscribers->where('users_courses.created_at','<=', $created_at_to.' 23:59:00');
        }

        return response()->json($subscribers->paginate($per_page)->onEachSide(1), 200);
    }
    else{
        return response()->json('Course not found', 404);
    }
}

public function get_invites(Request $request){
    $per_page = $request->per_page ? $request->per_page : 10;

    $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

    $find_course = Course::where('course_id', '=', $request->course_id)
    ->where('school_id', '=', auth()->user()->school_id)
    ->first();

    if(isset($find_course)){
        $invites = CourseInvite::leftJoin('users as operator','courses_invites.operator_id','=','operator.user_id')
        ->leftJoin('users as mentor','courses_invites.mentor_id','=','mentor.user_id')
        ->leftJoin('types_of_status','courses_invites.status_type_id','=','types_of_status.status_type_id')
        ->leftJoin('types_of_status_lang','types_of_status.status_type_id','=','types_of_status_lang.status_type_id')
        ->select(
            'operator.first_name as operator_first_name',
            'operator.last_name as operator_last_name',
            'mentor.first_name as mentor_first_name',
            'mentor.last_name as mentor_last_name',
            'courses_invites.id as invite_id',
            'courses_invites.course_cost',
            'courses_invites.created_at',
            'courses_invites.subscriber_email',
            'types_of_status_lang.status_type_name'
        )
        ->where('courses_invites.course_id', '=', $request->course_id)
        ->where('types_of_status_lang.lang_id', $language->lang_id)
        ->orderBy('courses_invites.created_at', 'desc');

        $email = $request->email;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if(!empty($email)){
            $invites->where('courses_invites.subscriber_email','LIKE','%'.$email.'%');
        }

        if($created_at_from && $created_at_to) {
            $invites->whereBetween('courses_invites.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $invites->where('courses_invites.created_at','>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $invites->where('courses_invites.created_at','<=', $created_at_to.' 23:59:00');
        }

        return response()->json($invites->paginate($per_page)->onEachSide(1), 200);
    }
    else{
        return response()->json('Course not found', 404);
    }
}

public function invite_subscriber(Request $request){
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email|max:200',
        'mentor_id' => 'required|numeric',
        'course_free' => 'required',
        'course_cost' => 'nullable|required_if:course_free,false|numeric|min:1'
    ]);

    if($validator->fails()){
        return $this->json('error', 'Invite error', 422, $validator->errors());
    }

    $find_user_course = UserCourse::leftJoin('users as recipient','users_courses.recipient_id','=','recipient.user_id')
    ->where('course_id', '=', $request->course_id)
    ->where('recipient.email', '=', $request->email)
    ->first();

    if(isset($find_user_course)){
        return $this->json('error', 'Login error', 422, ['email' => trans('auth.already_been_invited')]);
    }

    $search_email = User::where('email', '=', $request->email)
    ->where('school_id', '=', auth()->user()->school_id)
    ->first();

    if($request->course_free === false){
        $course_cost = $request->course_cost;
    }
    else{
        $course_cost = 0;
    }

    if(isset($search_email)){
        $new_user_course = new UserCourse();
        $new_user_course->operator_id = auth()->user()->user_id;
        $new_user_course->recipient_id = $search_email->user_id;
        $new_user_course->mentor_id = $request->mentor_id;
        $new_user_course->course_id = $request->course_id;
        $new_user_course->cost = $course_cost;
        $new_user_course->subscribe_type_id = 6;
        $new_user_course->save();
    }
    else{
        $search_invite_email = CourseInvite::where('subscriber_email', '=', $request->email)
        ->where('course_id', '=', $request->course_id)
        ->first();

        if(isset($search_invite_email)){
            return $this->json('error', 'Login error', 422, ['email' => trans('auth.already_been_invited')]);
        }
        else{
            $hash = Str::random(16);

            $school = School::find(auth()->user()->school_id);
            $course = Course::find($request->course_id);

            $new_course_invite = new CourseInvite();
            $new_course_invite->subscriber_email = $request->email;
            $new_course_invite->url_hash = $hash;
            $new_course_invite->course_id = $request->course_id;
            $new_course_invite->operator_id = auth()->user()->user_id;
            $new_course_invite->mentor_id = $request->mentor_id;
            $new_course_invite->course_cost = $course_cost;
            $new_course_invite->save();

            $mail_body = new \stdClass();
            $mail_body->subject = $school->school_name;
            $mail_body->course_name = $course->course_name;
            $mail_body->invitation_url = $request->header('Origin').'/invitation/'.$hash;

            Mail::to($request->email)->send(new CourseInvitationMail($mail_body));
        }
    }

    return response()->json('Subscribe success', 200);
}

public function get_invitation(Request $request){
    $invitation = CourseInvite::leftJoin('courses','courses.course_id','=','courses_invites.course_id')
    ->select(
        'courses_invites.subscriber_email',
        'courses_invites.course_id',
        'courses.course_name'
    )
    ->where('courses_invites.url_hash', '=', $request->hash)
    ->where('courses_invites.status_type_id', '=', 4)
    ->first();

    if(isset($invitation)){
        return response()->json($invitation, 200);
    }
    else{
        return response()->json('Invitation not found', 404);
    }
}

public function get_requests(Request $request){
    $per_page = $request->per_page ? $request->per_page : 10;

    $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

    $find_course = Course::where('course_id', '=', $request->course_id)
    ->where('school_id', '=', auth()->user()->school_id)
    ->first();

    if(isset($find_course)){
        $requests = CourseRequest::leftJoin('users as initiator','courses_requests.initiator_id','=','initiator.user_id')
        ->leftJoin('types_of_status','courses_requests.status_type_id','=','types_of_status.status_type_id')
        ->leftJoin('types_of_status_lang','types_of_status.status_type_id','=','types_of_status_lang.status_type_id')
        ->select(
            'initiator.avatar',
            'initiator.first_name as initiator_first_name',
            'initiator.last_name as initiator_last_name',
            'initiator.email as initiator_email',
            'initiator.phone as initiator_phone',
            'courses_requests.id as request_id',
            'courses_requests.created_at',
            'courses_requests.status_type_id',
            'types_of_status_lang.status_type_name'
        )
        ->where('courses_requests.course_id', '=', $request->course_id)
        ->where('types_of_status_lang.lang_id', $language->lang_id)
        ->orderBy('courses_requests.created_at', 'desc');

        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $email = $request->email;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if(!empty($first_name)){
            $requests->where('initiator.first_name','LIKE','%'.$first_name.'%');
        }

        if(!empty($last_name)){
            $requests->where('initiator.last_name','LIKE','%'.$last_name.'%');
        }

        if(!empty($email)){
            $requests->where('initiator.email','LIKE','%'.$email.'%');
        }

        if($created_at_from && $created_at_to) {
            $requests->whereBetween('courses_requests.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $requests->where('courses_requests.created_at','>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $requests->where('courses_requests.created_at','<=', $created_at_to.' 23:59:00');
        }

        return response()->json($requests->paginate($per_page)->onEachSide(1), 200);
    }
    else{
        return response()->json('Course not found', 404);
    }
}

public function accept_request(Request $request){
    $validator = Validator::make($request->all(), [
        'mentor_id' => 'required|numeric'
    ]);

    if($validator->fails()){
        return $this->json('error', 'Invite error', 422, $validator->errors());
    }

    $find_request = CourseRequest::leftJoin('courses','courses_requests.course_id','=','courses.course_id')
    ->where('courses_requests.id', '=', $request->request_id)
    ->where('courses_requests.status_type_id', '=', 12)
    ->where('courses.school_id', '=', auth()->user()->school_id)
    ->first();

    if(isset($find_request)){
        $save_request = CourseRequest::find($find_request->id);
        $save_request->status_type_id = 13;
        $save_request->save();

        $find_user_course = UserCourse::where('course_id', '=', $find_request->course_id)
        ->where('recipient_id', '=', $find_request->initiator_id)
        ->first();

        if(!isset($find_user_course)){
            $new_user_course = new UserCourse();
            $new_user_course->operator_id = auth()->user()->user_id;
            $new_user_course->recipient_id = $find_request->initiator_id;
            $new_user_course->mentor_id = $request->mentor_id;
            $new_user_course->course_id = $find_request->course_id;
            $new_user_course->cost = 0;
            $new_user_course->subscribe_type_id = 5;
            $new_user_course->save();
        }

        return response()->json('Accept request success', 200);
    }
    else{
        return response()->json('Request not found', 404);
    }
}

public function create_review(Request $request){

    $find_user_course = UserCourse::where('course_id', '=', $request->course_id)
    ->where('recipient_id', '=', auth()->user()->user_id)
    ->first();

    if(isset($find_user_course)){
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|between:1, 5',
            'review' => 'nullable|string|min:10|max:1000'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Course create error', 422, $validator->errors());
        }

        $new_review = new CourseReview();
        $new_review->course_id = $request->course_id;
        $new_review->user_id = auth()->user()->user_id;
        $new_review->rating = $request->rating;
        $new_review->review = $request->review;
        $new_review->save();

        return $this->json('success', 'Review create successful', 200, $new_review);
    }
    else{
        return response()->json('User course not found', 404);
    }
}

public function poster($file_name){
    $course = Course::where('course_poster_file', '=', $file_name)->first();

    if(isset($course)){
        $path = storage_path('/app/schools/'.$course->school_id.'/course_posters/'.$course->course_id.'/'.$file_name);

        if (!File::exists($path)) {
            return response()->json('Poster not found', 404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }
    else{
        return response()->json('Poster not found', 404);
    }
}

public function trailer($file_name){
    $course = Course::where('course_trailer_file', '=', $file_name)->first();

    if(isset($course)){
        $path = storage_path('/app/schools/'.$course->school_id.'/course_trailers/'.$course->course_id.'/'.$file_name);

        if (!File::exists($path)) {
            return response()->json('Trailer not found', 404);
        }

        $response = VideoStreamer::streamFile($path);
        return $response;
    }
    else{
        return response()->json('Trailer not found', 404);
    }
}
}