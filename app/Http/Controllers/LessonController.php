<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonVideo;
use App\Models\School;
use App\Models\UserOperation;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use File;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Validator;

class LessonController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_lesson(Request $request){
        $lesson = Lesson::leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->leftJoin('types_of_lessons','lessons.lesson_type_id','=','types_of_lessons.lesson_type_id')
        ->leftJoin('types_of_lessons_lang','types_of_lessons.lesson_type_id','=','types_of_lessons_lang.lesson_type_id')
        ->leftJoin('languages','types_of_lessons_lang.lang_id','=','languages.lang_id')
        ->select(
            'lessons.lesson_id',
            'lessons.lesson_name',
            'lessons.lesson_description',
            'lessons.created_at',
            'lessons.lesson_type_id',
            'types_of_lessons_lang.lesson_type_name',
            'courses.course_id',
            'courses.course_name'
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lessons.lesson_id', $request['lesson_id'])
        ->where('languages.lang_tag', $request->header('Accept-Language'))
        ->first();

        if(isset($lesson)){
            return response()->json($lesson, 200);
        }
        else{
            return response()->json('Not found', 404);
        }
    }

    public function my_lessons(Request $request){
        $my_lessons = Lesson::leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->leftJoin('types_of_lessons','lessons.lesson_type_id','=','types_of_lessons.lesson_type_id')
        ->leftJoin('types_of_lessons_lang','types_of_lessons.lesson_type_id','=','types_of_lessons_lang.lesson_type_id')
        ->leftJoin('languages','types_of_lessons_lang.lang_id','=','languages.lang_id')
        ->select(
            'lessons.lesson_id',
            'lessons.lesson_name',
            'lessons.lesson_description',
            'lessons.created_at',
            'lessons.lesson_type_id',
            'types_of_lessons_lang.lesson_type_name'
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lessons.course_id', $request['course_id'])
        ->where('languages.lang_tag', $request->header('Accept-Language'))
        ->orderBy('lessons.sort_num', 'asc')
        ->get();

        $materials_count = 0;
        $sections_count = 0;
        $total_count = 0;

        foreach ($my_lessons as $key => $value) {
            if($value->lesson_type_id != 3){
                $materials_count += 1;
            }
            else{
                $sections_count += 1;
            }
            $total_count += 1;
        }

        return response()->json([
            'my_lessons' => $my_lessons,
            'materials_count' => $materials_count,
            'sections_count' => $sections_count,
            'total_count' => $total_count
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $max_video_file_size = 100;
        $validator = Validator::make($request->all(), [
            'lesson_name' => 'required|string|between:3, 300',
            'lesson_description' => 'required_unless:lesson_type_id,3|string|max:1000',
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

        if($request->lesson_type_id != 3){
            $user_operation = new UserOperation();
            $user_operation->operator_id = auth()->user()->user_id;
            $user_operation->operation_type_id = 4;
            $user_operation->save();
        }

        return $this->json('success', 'Lesson create successful', 200, $new_lesson);
    }


    public function set_order(Request $request){
        $array = explode(',', $request->lessons_id);
        for ($i=0; $i < count($array); $i++) { 
            $lesson = Lesson::where('lesson_id', $array[$i])
            ->where('course_id', $request->course_id)
            ->first();
            $lesson->sort_num = $i+1;
            $lesson->save();
        }
        return response()->json('Success', 200);
    }


    public function get_video(Request $request){

        $origin = parse_url($request->header('Referer'));

        if(isset($origin['host'])){
            $host = $origin['host'];
            $parts = explode('.', $host);

            if(count($parts) >= 2){
                $subdomain = 'test';
                $school = School::where('school_domain', $subdomain)->first();

                if(isset($school)){
                    $lesson_id = $request->lesson_id;

                    $lesson = Lesson::leftJoin('courses', 'lessons.course_id', '=', 'courses.course_id')
                    ->leftJoin('schools', 'courses.school_id', '=', 'schools.school_id')
                    ->where('schools.school_id', $school->school_id)
                    ->where('lessons.lesson_id', $lesson_id)
                    ->first();

                    if(isset($lesson)){
                        $lesson_video = LessonVideo::where('lesson_id', $lesson->lesson_id)
                        ->first();

                        if(isset($lesson_video)){
                            $path = storage_path('/app/videos/lessons/'.$lesson_video->lesson_id.'/'.$lesson_video->content);

                            if (!File::exists($path)) {
                                return response()->json('Video not found', 404);
                            }

                            $file = File::get($path);
                            $type = File::mimeType($path);

                            $response = Response::make($file, 200);
                            $response->header("Content-Type", $type);
                            return $response;

                        }
                        else{
                           return response()->json('Lesson video not found', 404);
                       }
                   }
                   else{
                    return response()->json('Access denied', 403);
                }
            }
            else{
                return response()->json('Access denied', 403);
            }
        }
        else{
            return response()->json('Access denied', 403);
        }
    }
    else{
        return response()->json('Access denied', 403);
    }
}



public function store(Request $request){

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
