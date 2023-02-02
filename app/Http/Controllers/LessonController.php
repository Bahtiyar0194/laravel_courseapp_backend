<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $validator = Validator::make($request->all(), [
            'lesson_name' => 'required|string|between:3, 300',
            'lesson_description' => 'required|string|max:1000',
            'course_id' => 'required',
            // 'course_category_id' => 'required',
            // 'course_language_id' => 'required',
            // 'course_poster' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:1024'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Lesson create error', 422, $validator->errors());
        }

        if($request->course_free == 'false'){
            $cost_validator = Validator::make($request->all(), [
                'course_cost' => 'required|numeric|min:1'
            ]);

            if($cost_validator->fails()){
                return $this->json('error', 'Course create error', 422, $cost_validator->errors());
            }

            $course_cost = $request->course_cost;
        }
        else{
            $course_cost = 0;
        }

        if(isset($request->course_poster)){
            $file = $request->file('course_poster');
            $file_name = $file->hashName();
            $file->storeAs('/images/posters', $file_name);
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
        $new_course->school_id = 1;
        $new_course->course_cost = $course_cost;
        $new_course->save();

        return $this->json('success', 'Course create successful', 200, $new_course);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
