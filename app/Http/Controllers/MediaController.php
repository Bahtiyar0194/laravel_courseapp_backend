<?php

namespace App\Http\Controllers;
use App\Models\UserOperation;
use App\Models\MediaFile;
use App\Models\MediaFileType;
use App\Models\UploadConfiguration;
use Iman\Streamer\VideoStreamer;
use App\Models\School;
use App\Models\SubscriptionPlan;
use App\Models\Language;

use Illuminate\Support\Facades\Response;
use File;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Validator;
use DB;

class MediaController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_school_files(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $per_page = $request->per_page ? $request->per_page : 10;

        $media_files = MediaFile::leftJoin('types_of_media_files','media_files.file_type_id','=','types_of_media_files.file_type_id')
        ->leftJoin('types_of_media_files_lang','types_of_media_files.file_type_id','=','types_of_media_files_lang.file_type_id')
        ->select(
            'media_files.file_id',
            'media_files.file_type_id',
            'media_files.file_target',
            'media_files.file_name',
            'media_files.size',
            'media_files.created_at',
            'types_of_media_files_lang.file_type_name'
        )
        ->where('media_files.school_id', '=', auth()->user()->school_id)
        ->where('types_of_media_files_lang.lang_id', $language->lang_id)
        ->orderBy('media_files.created_at', 'desc');

        $file_name = $request->file_name;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;
        $file_type_id = $request->file_type_id;

        if(!empty($file_name)){
            $media_files->where('media_files.file_name','LIKE','%'.$file_name.'%');
        }

        if($created_at_from && $created_at_to) {
            $media_files->whereBetween('media_files.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $media_files->where('media_files.created_at','>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $media_files->where('media_files.created_at','<=', $created_at_to.' 23:59:00');
        }

        if(!empty($file_type_id)){
            $media_files->where('media_files.file_type_id','=', $file_type_id);
        }

        $result = $media_files->paginate($per_page)->onEachSide(1);

        return response()->json($result, 200);
    }

    public function get_disk_space(){
        $media_files = MediaFile::where('school_id', '=', auth()->user()->school_id)->get();
        $media_files_size_sum = $media_files->sum('size');
        $school = School::find(auth()->user()->school_id);
        $subscription_plan = SubscriptionPlan::find($school->subscription_plan_id);

        $result = new \stdClass();
        $result->disk_space_mb = $subscription_plan->disk_space;
        $result->disk_space_gb = $subscription_plan->disk_space / 1024;

        $result->used_space_mb = $media_files_size_sum;
        $result->used_space_gb = $media_files_size_sum / 1024;

        $result->free_space_mb = $subscription_plan->disk_space - $media_files_size_sum;
        $result->free_space_gb = ($subscription_plan->disk_space - $media_files_size_sum) / 1024;

        $result->used_space_percent = ($media_files_size_sum * 100) / $subscription_plan->disk_space;
        $result->free_space_percent = 100 - $result->used_space_percent;

        $result->plan_name = $subscription_plan->subscription_plan_name;
        $result->files_count = count($media_files);

        return response()->json($result, 200);
    }

    public function types_of_media_files(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $types_of_media_files = MediaFileType::leftJoin('types_of_media_files_lang','types_of_media_files.file_type_id','=','types_of_media_files_lang.file_type_id')
        ->select(
            'types_of_media_files.file_type_id',
            'types_of_media_files_lang.file_type_name'
        )
        ->where('types_of_media_files_lang.lang_id', $language->lang_id)
        ->get();

        return response()->json($types_of_media_files, 200);
    }

    public function upload_video(Request $request){
        $file_type_id = 1;
        $max_video_file_size = UploadConfiguration::where('file_type_id', '=', $file_type_id)
        ->first()->max_file_size_mb;

        $school_id = auth()->user()->school_id;

        $validator = Validator::make($request->all(), [
            'video_type' => 'required',
            'video_name' => 'nullable|required_if:video_type,video_file|string|between:3, 100',
            'video_file' => 'nullable|required_if:video_type,video_file|file|mimes:mp4,ogx,oga,ogv,ogg,webm|max_mb:'.$max_video_file_size,
            'selected_video_id' => 'nullable|required_if:video_type,video_from_media|numeric'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Video upload error', 422, $validator->errors());
        }

        if($request->video_type == 'video_from_media'){
            $media_file = MediaFile::where('school_id', '=', $school_id)
            ->where('file_type_id', '=', 1)
            ->where('file_id', '=', $request->selected_video_id)
            ->first();
        }
        else{
            $media_file = new MediaFile();
            $media_file->file_name = $request->video_name;

            $file = $request->file('video_file');
            $file_target = $file->hashName();

            $media_file->file_target = $file_target;
            $media_file->file_type_id = $file_type_id;
            $media_file->size = $file->getSize() / 1048576;

            //App/Helpers
            if(lack_of_disk_space($media_file->size, $school_id)){
                return $this->json('error', 'Video upload error', 422, ['lack_of_disk_space' => true]);
            }

            $file->storeAs('/schools/'.$school_id.'/videos/', $file_target);  

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
            $host = str_replace('www.', '', $origin['host']);
            $parts = explode('.', $host);

            if(count($parts) >= 2){
                $subdomain = $parts[0];
                $school = School::where('school_domain', $subdomain)->first();

                if(isset($school)){
                    $file_id = $request->file_id;

                    $video_file = MediaFile::where('school_id', $school->school_id)
                    ->where('file_id', $file_id)
                    ->first();

                    if(isset($video_file)){
                        $path = storage_path('/app/schools/'.$school->school_id.'/videos/'.$video_file->file_target);

                        if (!File::exists($path)) {
                            return response()->json('Video not found', 404);
                        }

                        $response = VideoStreamer::streamFile($path);
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

    public function get_videos(Request $request){
        $videos = MediaFile::where('school_id', auth()->user()->school_id)
        ->where('file_type_id', 1)
        ->get();

        return response()->json($videos, 200);
    }


    public function upload_audio(Request $request){
        $file_type_id = 2;
        $max_audio_file_size = UploadConfiguration::where('file_type_id', '=', $file_type_id)
        ->first()->max_file_size_mb;

        $school_id = auth()->user()->school_id;

        $validator = Validator::make($request->all(), [
          'audio_type' => 'required',
          'audio_name' => 'nullable|required_if:audio_type,audio_file|string|between:3, 100',
          'audio_file' => 'nullable|required_if:audio_type,audio_file|file|mimes:mp3,m4a,opus,oga,flac,ogg,webm,weba,wav,wma|max_mb:'.$max_audio_file_size,
          'selected_audio_id' => 'nullable|required_if:audio_type,audio_from_media|numeric'
      ]);

        if($validator->fails()){
            return $this->json('error', 'Audio upload error', 422, $validator->errors());
        }

        if($request->audio_type == 'audio_from_media'){
            $media_file = MediaFile::where('school_id', '=', $school_id)
            ->where('file_type_id', '=', 2)
            ->where('file_id', '=', $request->selected_audio_id)
            ->first();
        }
        else{
            $media_file = new MediaFile();
            $media_file->file_name = $request->audio_name;

            $file = $request->file('audio_file');
            $file_target = $file->hashName();

            $media_file->file_target = $file_target;
            $media_file->file_type_id = $file_type_id;
            $media_file->size = $file->getSize() / 1048576;

            //App/Helpers
            if(lack_of_disk_space($media_file->size, $school_id)){
                return $this->json('error', 'Image upload error', 422, ['lack_of_disk_space' => true]);
            }

            $file->storeAs('/schools/'.$school_id.'/audios/', $file_target);  

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
            $host = str_replace('www.', '', $origin['host']);
            $parts = explode('.', $host);

            if(count($parts) >= 2){
                $subdomain = $parts[0];
                $school = School::where('school_domain', $subdomain)->first();

                if(isset($school)){
                    $file_id = $request->file_id;

                    $audio_file = MediaFile::where('school_id', $school->school_id)
                    ->where('file_id', $file_id)
                    ->first();

                    if(isset($audio_file)){
                        $path = storage_path('/app/schools/'.$school->school_id.'/audios/'.$audio_file->file_target);

                        if (!File::exists($path)) {
                            return response()->json('Audio not found', 404);
                        }

                        $response = VideoStreamer::streamFile($path);
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

    public function get_audios(Request $request){
        $audios = MediaFile::where('school_id', auth()->user()->school_id)
        ->where('file_type_id', 2)
        ->get();

        return response()->json($audios, 200);
    }

    public function upload_image(Request $request){
        $file_type_id = 3;
        $max_image_file_size = UploadConfiguration::where('file_type_id', '=', $file_type_id)
        ->first()->max_file_size_mb;

        $school_id = auth()->user()->school_id;

        $validator = Validator::make($request->all(), [
            'image_type' => 'required',
            'image_name' => 'nullable|required_if:image_type,image_file|string|between:3, 100',
            'image_file' => 'nullable|required_if:image_type,image_file|file|mimes:jpg,jpeg,png,gif,svg,webp|max_mb:'.$max_image_file_size,
            'selected_image_id' => 'nullable|required_if:image_type,image_from_media|numeric'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Image upload error', 422, $validator->errors());
        }

        if($request->image_type == 'image_from_media'){
            $media_file = MediaFile::where('school_id', '=', $school_id)
            ->where('file_type_id', '=', 3)
            ->where('file_id', '=', $request->selected_image_id)
            ->first();
        }
        else{

            $media_file = new MediaFile();
            $media_file->file_name = $request->image_name;

            $file = $request->file('image_file');
            $file_target = $file->hashName();

            $media_file->file_target = $file_target;
            $media_file->file_type_id = $file_type_id;
            $media_file->size = $file->getSize() / 1048576;

            //App/Helpers
            if(lack_of_disk_space($media_file->size, $school_id)){
                return $this->json('error', 'Image upload error', 422, ['lack_of_disk_space' => true]);
            }

            $file->storeAs('schools/'.$school_id.'/images/', $file_target);

            $media_file->school_id = $school_id;
            $media_file->save();

            $user_operation = new UserOperation();
            $user_operation->operator_id = auth()->user()->user_id;
            $user_operation->operation_type_id = 9;
            $user_operation->save();
        }
        
        return $this->json('success', 'Upload image successful', 200, $media_file);
    }

    public function get_images(Request $request){
        $images = MediaFile::where('school_id', auth()->user()->school_id)
        ->where('file_type_id', 3)
        ->get();

        return response()->json($images, 200);
    }

    public function get_image(Request $request){
        $origin = parse_url($request->header('Referer'));

        if(isset($origin['host'])){
            $host = str_replace('www.', '', $origin['host']);
            $parts = explode('.', $host);

            if(count($parts) >= 2){
                $subdomain = $parts[0];
                $school = School::where('school_domain', $subdomain)->first();

                if(isset($school)){
                    $image_file = MediaFile::where('school_id', $school->school_id)
                    ->where('file_id', $request->file_id)
                    ->first();

                    if(isset($image_file)){
                        $path = storage_path('/app/schools/'.$school->school_id.'/images/'.$image_file->file_target);

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

    public function update_file(Request $request){
        $validator = Validator::make($request->all(), [
            'file_name' => 'required|string|between:3, 300'
        ]);

        if($validator->fails()){
            return $this->json('error', 'File update error', 422, $validator->errors());
        }

        $find_file = MediaFile::where('file_id', $request->file_id)
        ->where('school_id', auth()->user()->school_id)
        ->first();

        if(isset($find_file)){
            $find_file->file_name = $request->file_name;
            $find_file->save();

            return $this->json('success', 'File update successful', 200, 'success');
        }
        else{
            return response()->json('File not found', 404);
        }
    }

    public function delete_file(Request $request){
        $school_id = auth()->user()->school_id;
        $find_file = MediaFile::where('file_id', $request->file_id)
        ->where('school_id', $school_id)
        ->first();

        if(isset($find_file)){
            $find_file->delete();

            if($find_file->file_type_id == 1){
                $folder = 'videos';
            }
            elseif($find_file->file_type_id == 2){
                $folder = 'audios';
            }
            elseif($find_file->file_type_id == 3){
                $folder = 'images';
            }

            $path = storage_path('/app/schools/'.$school_id.'/'.$folder.'/'.$find_file->file_target);

            if (!File::exists($path)) {
                return response()->json('File not found', 404);
            }

            File::delete($path);

            return $this->json('success', 'File delete successful', 200, 'success');
        }
        else{
            return response()->json('File not found', 404);
        }
    }
}