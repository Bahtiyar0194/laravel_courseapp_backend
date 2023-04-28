<?php

namespace App\Http\Controllers;
use App\Models\UserOperation;
use App\Models\MediaFile;
use App\Models\UploadConfiguration;
use Iman\Streamer\VideoStreamer;
use App\Models\School;

use Illuminate\Support\Facades\Response;
use File;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Validator;

class MediaController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_school_files(){

    }

    public function upload_image(Request $request){
        $file_type_id = 4;
        $max_image_file_size = UploadConfiguration::where('file_type_id', '=', $file_type_id)
        ->first()->max_file_size_mb;

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
        $media_file->file_type_id = $file_type_id;
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
        $file_type_id = 1;
        $max_video_file_size = UploadConfiguration::where('file_type_id', '=', $file_type_id)
        ->first()->max_file_size_mb;

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
                $media_file->file_type_id = $file_type_id;
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
                            
                            $response = VideoStreamer::streamFile($path);
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
        $file_type_id = 3;
        $max_audio_file_size = UploadConfiguration::where('file_type_id', '=', $file_type_id)
        ->first()->max_file_size_mb;

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
            $media_file->file_type_id = $file_type_id;
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
}