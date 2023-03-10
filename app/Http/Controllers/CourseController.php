<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\UserOperation;
use App\Models\Language;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use File;
use App\Http\Controllers\Controller;
use Validator;

class CourseController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function my_courses(Request $request){
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

    public function course(Request $request){

        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $course = Course::leftJoin('course_categories','courses.course_category_id','=','course_categories.course_category_id')
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
        ->where('courses.course_id', $request['course_id'])
        ->where('languages_lang.lang_tag', $language->lang_tag)
        ->where('course_categories_lang.lang_id', $language->lang_id)
        ->first();

        if(isset($course)){
            return response()->json($course, 200);
        }
        else{
            return response()->json('Not found', 404);
        }
    }

    public function create(Request $request){
        $max_file_size = 1;

        $validator = Validator::make($request->all(), [
            'course_name' => 'required|string|between:3, 300',
            'course_description' => 'required|string|max:1000',
            'course_category_id' => 'required',
            'course_language_id' => 'required',
            'course_poster' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max_mb:'.$max_file_size,
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

        if(isset($request->course_poster)){
            $file = $request->file('course_poster');
            $file_name = $file->hashName();
            $file->storeAs('/images/', $file_name);
        }
        else{
            $file_name = 'default.svg';
        }
        
        $new_course = new Course();
        $new_course->course_name = $request->course_name;
        $new_course->course_description = $request->course_description;
        $new_course->course_poster_file = $file_name;
        $new_course->course_category_id = $request->course_category_id;
        $new_course->course_lang_id = $request->course_language_id;
        $new_course->school_id = auth()->user()->school_id;
        $new_course->course_cost = $course_cost;
        $new_course->save();

        $user_operation = new UserOperation();
        $user_operation->operator_id = auth()->user()->user_id;
        $user_operation->operation_type_id = 3;
        $user_operation->save();

        return $this->json('success', 'Course create successful', 200, $new_course);
    }


    public function poster($file_name){
        $path = storage_path('/app/images/'.$file_name);

        if (!File::exists($path)) {
            return response()->json('Not found', 404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }
}