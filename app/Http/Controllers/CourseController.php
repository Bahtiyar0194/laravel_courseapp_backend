<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\UserOperation;
use App\Models\UserCourse;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Language;
use App\Models\CourseLevelType;
use App\Models\CourseSkill;
use App\Models\UploadConfiguration;

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
            'courses.created_at',
            'course_categories_lang.course_category_name',
            'languages_lang.lang_name',
            'types_of_course_level.level_type_id',
            'types_of_course_level_lang.level_type_name',
            'users.last_name',
            'users.first_name'
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

            $skills = CourseSkill::where('course_id', '=', $course->course_id)
            ->get();

            $course->subscribers = $subscribers;
            $course->skills = $skills;

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
            'course_category_id' => 'required',
            'course_lang_id' => 'required',
            'level_type_id' => 'required|numeric',
            'author_id' => 'required|numeric',
            'course_poster_file' => 'nullable|file|mimes:jpg,png,jpeg,gif,svg|max_mb:'.$poster_max_file_size,
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
            $file = $request->file('course_poster_file');
            $poster_file_name = $file->hashName();
            $file->storeAs('/course_posters/', $poster_file_name);
        }
        else{
            $poster_file_name = 'default.svg';
        }

        if(isset($request->course_trailer_file)){
            $file = $request->file('course_trailer_file');
            $trailer_file_name = $file->hashName();
            $file->storeAs('/course_trailers/', $trailer_file_name);
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

        $course_skills = json_decode($request->course_skills);

        if(count($course_skills) > 0){
            foreach ($course_skills as $key => $skill) {
                $new_skill = new CourseSkill();
                $new_skill->course_id = $new_course->course_id;
                $new_skill->skill_name = $skill->item_value;
                $new_skill->save();
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

        $new_user_course = new UserCourse();
        $new_user_course->operator_id = auth()->user()->user_id;
        $new_user_course->recipient_id = auth()->user()->user_id;
        $new_user_course->course_id = $new_course->course_id;
        $new_user_course->cost = 0;
        $new_user_course->subscribe_type_id = 4;
        $new_user_course->save();

        return $this->json('success', 'Course create successful', 200, $new_course);
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


    public function poster($file_name){
        $path = storage_path('/app/course_posters/'.$file_name);

        if (!File::exists($path)) {
            return response()->json('Poster not found', 404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }
}