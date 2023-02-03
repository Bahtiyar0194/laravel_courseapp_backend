<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonVideo;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class LessonController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request) 
    {
        app()->setLocale($request->header('Accept-Language'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function my_lessons(Request $request)
    {
        $my_lessons = Lesson::where('course_id', $request['course_id'])
        ->get();
        return response()->json($my_lessons, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $max_video_file_size = 10;
        $validator = Validator::make($request->all(), [
            'lesson_name' => 'required|string|between:3, 300',
            'lesson_description' => 'required|string|max:1000',
            'lesson_type_id' => 'required',
            'course_id' => 'required',
            'video_type' => 'required_if:lesson_type_id,2',
            'video_file' => 'nullable|required_if:video_type,video_file|file|mimes:mp4,ogx,oga,ogv,ogg,webm|max_mb:'.$max_video_file_size,
            'video_link' => 'nullable|required_if:video_type,video_url|url',
        ]);

        if($validator->fails()){
            return $this->json('error', 'Lesson create error', 422, $validator->errors());
        }

        $count_lessons = Lesson::where("course_id", $request->course_id)->count();
        $sort_num = $count_lessons + 1;

        $new_lesson = new Lesson();
        $new_lesson->lesson_name = $request->lesson_name;
        $new_lesson->lesson_description = $request->lesson_description;
        $new_lesson->course_id = $request->course_id;
        $new_lesson->lesson_type_id = $request->lesson_type_id;
        $new_lesson->sort_num = $sort_num;
        $new_lesson->save();


        //Если тип урока видеоурок
        if($request->lesson_type_id == 2){
            if($request->video_type == 'video_file'){
                $file = $request->file('video_file');
                $file_size = $request->file('video_file')->getSize() / 1048576;
                $file_name = $file->hashName();
                $file->storeAs('/videos/lessons/'.$new_lesson->lesson_id, $file_name);                
                $content = $file_name;
                $lesson_video_type_id = 1;
            }
            elseif($request->video_type == 'video_url'){
                $file_size = null;
                $content = $request->video_link;
                $lesson_video_type_id = 2;
            }

            $new_lesson_video = new LessonVideo();
            $new_lesson_video->lesson_id = $new_lesson->lesson_id;
            $new_lesson_video->lesson_video_type_id = $lesson_video_type_id;
            $new_lesson_video->content = $content;
            $new_lesson_video->size = $file_size;
            $new_lesson_video->save();
        }
        
        return $this->json('success', 'Lesson create successful', 200, $new_lesson);
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
