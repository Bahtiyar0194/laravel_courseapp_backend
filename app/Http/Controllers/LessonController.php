<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\LessonText;
use App\Models\LessonVideo;
use App\Models\UserOperation;
use App\Models\MediaFile;

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
        $find_lesson = Lesson::leftJoin('courses','lessons.course_id','=','courses.course_id')
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

        if(isset($find_lesson)){
            $block_id = 0;
            $blocks = [];
            $lesson_blocks = LessonBlock::where('lesson_id', $find_lesson->lesson_id)->get();

            foreach ($lesson_blocks as $key => $lesson_block) {

                if($lesson_block->lesson_block_type_id == 1){
                    $text = LessonText::where('lesson_block_id', $lesson_block->lesson_block_id)
                    ->first();
                    if(isset($text)){
                        $text_block = [
                            'block_id' => $block_id + 1,
                            'block_type_id' => $lesson_block->lesson_block_type_id,
                            'content' => $text->content
                        ];
                        array_push($blocks, $text_block);
                    }
                }
                elseif($lesson_block->lesson_block_type_id == 2){
                    $video = LessonVideo::leftJoin('media_files','lesson_videos.file_id','=','media_files.file_id')
                    ->where('lesson_videos.lesson_block_id', $lesson_block->lesson_block_id)
                    ->select(
                        'media_files.file_type_id',
                        'media_files.file_name',
                        'media_files.file_id'
                    )
                    ->first();
                    if(isset($video)){
                        $video_block = [
                            'block_id' => $block_id + 1,
                            'file_type_id' => $video->file_type_id,
                            'file_id' => $video->file_id,
                            'file_name' => $video->file_name
                        ];
                        array_push($blocks, $video_block);
                    }
                }

            }

            $lesson = [
                'lesson' => $find_lesson,
                'lesson_blocks' => $blocks
            ];

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

        $validator = Validator::make($request->all(), [
            'lesson_name' => 'required|string|between:3, 300',
            'lesson_description' => 'required_unless:lesson_type_id,2|string|min:10',
            'lesson_type_id' => 'required',
            'lesson_blocks' => 'required_unless:lesson_type_id,2|min:3',
            'course_id' => 'required'
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

        if($request->lesson_type_id != 2){

            $lesson_blocks = json_decode($request->lesson_blocks);

            foreach ($lesson_blocks as $key => $lesson_block) {

                if(isset($lesson_block->block_type_id)){
                    if($lesson_block->block_type_id == 1){
                        $block_type = 'text';
                    }
                }

                if(isset($lesson_block->file_type_id)){
                    if($lesson_block->file_type_id == 1 || $lesson_block->file_type_id == 2){
                        $block_type = 'video';
                    }
                }

                $new_lesson_block = new LessonBlock();

                if($block_type == 'text'){
                    $new_lesson_block->lesson_block_type_id = 1;
                }

                if($block_type == 'video'){
                    $new_lesson_block->lesson_block_type_id = 2;
                }

                $new_lesson_block->lesson_id = $new_lesson->lesson_id;
                $new_lesson_block->save();

                if($block_type == 'text'){
                    $new_lesson_text = new LessonText();
                    $new_lesson_text->lesson_block_id = $new_lesson_block->lesson_block_id;
                    $new_lesson_text->content = $lesson_block->content;
                    $new_lesson_text->save();
                }


                if($block_type == 'video'){
                    $new_lesson_video = new LessonVideo();
                    $new_lesson_video->lesson_block_id = $new_lesson_block->lesson_block_id;
                    $new_lesson_video->file_id = $lesson_block->file_id;
                    $new_lesson_video->save();
                }
            }

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


    public function upload_video(Request $request){
        $max_video_file_size = 100;
        $school_id = auth()->user()->school_id;

        $validator = Validator::make($request->all(), [
            'video_name' => 'required|string|between:3, 100',
            'video_type' => 'required',
            'video_file' => 'nullable|required_if:video_type,video_file|file|mimes:mp4,ogx,oga,ogv,ogg,webm|max_mb:'.$max_video_file_size,
            'video_link' => 'nullable|required_if:video_type,video_url|url',
        ]);

        if($validator->fails()){
            return $this->json('error', 'Video upload error', 422, $validator->errors());
        }

        if($request->video_type == 'video_from_media'){
            $media_file = [];
        }
        else{
            $media_file = new MediaFile();
            $media_file->file_name = $request->video_name;

            if($request->video_type == 'video_file'){ 
                $file = $request->file('video_file');
                $file_target = $file->hashName();

                $media_file->file_target = $file_target;
                $media_file->file_type_id = 1;
                $media_file->size = $file->getSize() / 1048576;

                $file->storeAs('/videos/schools/'.$school_id, $file_target);  
            }
            elseif($request->video_type == 'video_url'){
                $media_file->file_target = $request->video_link;
                $media_file->file_type_id = 2;
                $media_file->size = null;
            }

            $media_file->school_id = $school_id;
            $media_file->save();

            $user_operation = new UserOperation();
            $user_operation->operator_id = auth()->user()->user_id;
            $user_operation->operation_type_id = 7;
            $user_operation->save();
        }

        return $this->json('success', 'Upload video successful', 200, $media_file);
    }


    public function get_video(Request $request){

        $origin = parse_url($request->header('Referer'));

        if(isset($origin['host'])){
            $host = $origin['host'];
            $parts = explode('.', $host);

            if(count($parts) == 2){
                $subdomain = $parts[0];
                $school = School::where('school_domain', $subdomain)->first();

                if(isset($school)){
                    $file_id = $request->file_id;

                    $video_file = MediaFile::where('school_id', $school->school_id)
                    ->where('file_id', $file_id)
                    ->first();

                    if(isset($video_file)){
                        if($video_file->file_type_id == 1){
                            $path = storage_path('/app/videos/schools/'.$school->school_id.'/'.$video_file->file_target);

                            if (!File::exists($path)) {
                                return response()->json('Video not found', 404);
                            }

                            $file = File::get($path);
                            $type = File::mimeType($path);

                            $response = Response::make($file, 200);
                            $response->header("Content-Type", $type);
                        }
                        elseif($video_file->file_type_id == 2){
                            $response = $video_file->file_target;
                        }
                        return $response;
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
