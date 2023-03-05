<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\LessonText;
use App\Models\LessonTable;
use App\Models\LessonCode;
use App\Models\LessonImage;
use App\Models\LessonVideo;
use App\Models\LessonAudio;
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
            $blocks = [];
            $lesson_blocks = LessonBlock::where('lesson_id', $find_lesson->lesson_id)->get();

            foreach ($lesson_blocks as $key => $lesson_block) {

                if($lesson_block->lesson_block_type_id == 1){
                    $text = LessonText::where('lesson_block_id', $lesson_block->lesson_block_id)
                    ->first();
                    if(isset($text)){
                        $text_block = [
                            'block_id' => $key + 1,
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
                            'block_id' => $key + 1,
                            'file_type_id' => $video->file_type_id,
                            'file_id' => $video->file_id,
                            'file_name' => $video->file_name
                        ];
                        array_push($blocks, $video_block);
                    }
                }
                elseif($lesson_block->lesson_block_type_id == 3){
                    $audio = LessonAudio::leftJoin('media_files','lesson_audios.file_id','=','media_files.file_id')
                    ->where('lesson_audios.lesson_block_id', $lesson_block->lesson_block_id)
                    ->select(
                        'media_files.file_type_id',
                        'media_files.file_name',
                        'media_files.file_id'
                    )
                    ->first();
                    if(isset($audio)){
                        $audio_block = [
                            'block_id' => $key + 1,
                            'file_type_id' => $audio->file_type_id,
                            'file_id' => $audio->file_id,
                            'file_name' => $audio->file_name,
                        ];
                        array_push($blocks, $audio_block);
                    }
                }
                elseif($lesson_block->lesson_block_type_id == 4){
                    $image = LessonImage::leftJoin('media_files','lesson_images.file_id','=','media_files.file_id')
                    ->where('lesson_images.lesson_block_id', $lesson_block->lesson_block_id)
                    ->select(
                        'media_files.file_type_id',
                        'media_files.file_name',
                        'media_files.file_id',
                        'lesson_images.image_width'
                    )
                    ->first();
                    if(isset($image)){
                        $image_block = [
                            'block_id' => $key + 1,
                            'file_type_id' => $image->file_type_id,
                            'file_id' => $image->file_id,
                            'file_name' => $image->file_name,
                            'image_width' => $image->image_width
                        ];
                        array_push($blocks, $image_block);
                    }
                }
                if($lesson_block->lesson_block_type_id == 5){
                    $table = LessonTable::where('lesson_block_id', $lesson_block->lesson_block_id)
                    ->first();
                    if(isset($table)){
                        $table_block = [
                            'block_id' => $key + 1,
                            'block_type_id' => $lesson_block->lesson_block_type_id,
                            'content' => $table->content
                        ];
                        array_push($blocks, $table_block);
                    }
                }

                if($lesson_block->lesson_block_type_id == 6){
                    $code = LessonCode::where('lesson_block_id', $lesson_block->lesson_block_id)
                    ->first();
                    if(isset($code)){
                        $code_block = [
                            'block_id' => $key + 1,
                            'block_type_id' => $lesson_block->lesson_block_type_id,
                            'code' => $code->code,
                            'code_language' => $code->code_language,
                            'code_theme' => $code->code_theme
                        ];
                        array_push($blocks, $code_block);
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
            return response()->json('Lesson not found', 404);
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
            //App/Helpers
            create_lesson_blocks($new_lesson->lesson_id, json_decode($request->lesson_blocks));

            $user_operation = new UserOperation();
            $user_operation->operator_id = auth()->user()->user_id;
            $user_operation->operation_type_id = 4;
            $user_operation->save();
        }

        return $this->json('success', 'Lesson create successful', 200, $new_lesson);
    }


    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lesson_name' => 'required|string|between:3, 300',
            'lesson_description' => 'required_unless:lesson_type_id,2|string|min:10',
            'lesson_type_id' => 'required',
            'lesson_blocks' => 'required_unless:lesson_type_id,2|min:3'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Lesson update error', 422, $validator->errors());
        }

        $find_lesson = Lesson::leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lessons.lesson_id', $request['lesson_id'])
        ->first();

        if(isset($find_lesson)){
            $edit_lesson = Lesson::find($find_lesson->lesson_id);
            $edit_lesson->lesson_name = $request->lesson_name;

            if($find_lesson->lesson_type_id != 2){
                $edit_lesson->lesson_description = $request->lesson_description;

                LessonBlock::where('lesson_id', $find_lesson->lesson_id)
                ->delete();

                //App/Helpers
                create_lesson_blocks($find_lesson->lesson_id, json_decode($request->lesson_blocks));

                $user_operation = new UserOperation();
                $user_operation->operator_id = auth()->user()->user_id;
                $user_operation->operation_type_id = 10;
                $user_operation->save();
            }

            $edit_lesson->save();

            return $this->json('success', 'Lesson update successful', 200, 'success');
        }
        else{
            return response()->json('Lesson not found', 404);
        }
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


    public function upload_image(Request $request){
        $max_image_file_size = 1;
        $school_id = auth()->user()->school_id;

        $validator = Validator::make($request->all(), [
            'image_name' => 'required|string|between:3, 100',
            'image_file' => 'required|file|mimes:jpg,jpeg,png,gif,svg,webp|max_mb:'.$max_image_file_size
        ]);

        if($validator->fails()){
            return $this->json('error', 'Image upload error', 422, $validator->errors());
        }

        $media_file = new MediaFile();
        $media_file->file_name = $request->image_name;

        $file = $request->file('image_file');
        $file_target = $file->hashName();

        $media_file->file_target = $file_target;
        $media_file->file_type_id = 4;
        $media_file->size = $file->getSize() / 1048576;

        $file->storeAs('/images/', $file_target);

        $media_file->school_id = $school_id;
        $media_file->save();

        $user_operation = new UserOperation();
        $user_operation->operator_id = auth()->user()->user_id;
        $user_operation->operation_type_id = 9;
        $user_operation->save();
        
        return $this->json('success', 'Upload image successful', 200, $media_file);
    }

    public function get_image(Request $request){
        $image_file = MediaFile::where('file_id', $request->file_id)
        ->first();

        if(isset($image_file)){
            $path = storage_path('/app/images/'.$image_file->file_target);

            if (!File::exists($path)) {
                return response()->json('Image not found', 404);
            }

            $file = File::get($path);
            $type = File::mimeType($path);

            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);

            return $response;
        }
        else{
            return response()->json('Access denied', 403);
        }
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


    public function upload_audio(Request $request){
        $max_audio_file_size = 10;
        $school_id = auth()->user()->school_id;

        $validator = Validator::make($request->all(), [
            'audio_name' => 'required|string|between:3, 100',
            'audio_file' => 'required|file|mimes:mp3,m4a,opus,oga,flac,ogg,webm,weba,wav,wma|max_mb:'.$max_audio_file_size,
        ]);

        if($validator->fails()){
            return $this->json('error', 'Audio upload error', 422, $validator->errors());
        }

        if($request->video_type == 'audio_from_media'){
            $media_file = [];
        }
        else{
            $media_file = new MediaFile();
            $media_file->file_name = $request->audio_name;

            $file = $request->file('audio_file');
            $file_target = $file->hashName();

            $media_file->file_target = $file_target;
            $media_file->file_type_id = 3;
            $media_file->size = $file->getSize() / 1048576;

            $file->storeAs('/audios/schools/'.$school_id, $file_target);  

            $media_file->school_id = $school_id;
            $media_file->save();

            $user_operation = new UserOperation();
            $user_operation->operator_id = auth()->user()->user_id;
            $user_operation->operation_type_id = 8;
            $user_operation->save();
        }

        return $this->json('success', 'Upload audio successful', 200, $media_file);
    }

    public function get_audio(Request $request){

        $origin = parse_url($request->header('Referer'));

        if(isset($origin['host'])){
            $host = $origin['host'];
            $parts = explode('.', $host);

            if(count($parts) == 2){
                $subdomain = $parts[0];
                $school = School::where('school_domain', $subdomain)->first();

                if(isset($school)){
                    $file_id = $request->file_id;

                    $audio_file = MediaFile::where('school_id', $school->school_id)
                    ->where('file_id', $file_id)
                    ->first();

                    if(isset($audio_file)){
                        $path = storage_path('/app/audios/schools/'.$school->school_id.'/'.$audio_file->file_target);

                        if (!File::exists($path)) {
                            return response()->json('Audio not found', 404);
                        }

                        $file = File::get($path);
                        $type = File::mimeType($path);

                        $response = Response::make($file, 200);
                        $response->header("Content-Type", $type);
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

    public function destroy($id)
    {
        //
    }
}
