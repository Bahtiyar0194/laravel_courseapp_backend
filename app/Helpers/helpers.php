<?php 
use App\Models\LessonBlock;
use App\Models\LessonText;
use App\Models\LessonTable;
use App\Models\LessonCode;
use App\Models\LessonImage;
use App\Models\LessonVideo;
use App\Models\LessonAudio;

use App\Models\TaskBlock;
use App\Models\TaskText;
use App\Models\TaskTable;
use App\Models\TaskCode;
use App\Models\TaskImage;
use App\Models\TaskVideo;
use App\Models\TaskAudio;

use App\Models\TestQuestionBlock;
use App\Models\TestQuestionImage;
use App\Models\TestQuestionAudio;
use App\Models\TestQuestionCode;

use App\Models\TaskAnswerBlock;
use App\Models\TaskAnswerText;
use App\Models\TaskAnswerTable;
use App\Models\TaskAnswerCode;
use App\Models\TaskAnswerImage;
use App\Models\TaskAnswerVideo;
use App\Models\TaskAnswerAudio;

use App\Models\SubscriptionPlan;
use App\Models\MediaFile;
use App\Models\School;

// if (!function_exists('check_subomain')){
// 	function check_subomain($full_domain){
// 		$origin = parse_url($full_domain);
// 		$host = str_replace(['.kz', '.ru', '.com', '.app'], '', $origin['host']);
// 		$parts = explode('.', $host);
// 		if(count($parts) >= 2){
// 			return ['subdomain' => $parts[0]];
// 		}

// 		return false;
// 	}
// }

if (!function_exists('lack_of_disk_space')){
	function lack_of_disk_space($file_size, $school_id){
		$school = School::find($school_id);
		$subscription_plan = SubscriptionPlan::find($school->subscription_plan_id);
		$media_files = MediaFile::where('school_id', '=', $school_id)->get();
		$media_files_size_sum = $media_files->sum('size');

		$free_space_mb = $subscription_plan->disk_space - $media_files_size_sum;

		if($file_size > $free_space_mb){
			return true;
		}
		else{
			return false;
		}
	}
}

if (!function_exists('create_blocks')){
	function create_blocks($target_id, $blocks, $operation_type){
		foreach ($blocks as $key => $block) {

			if(isset($block->block_type_id)){
				if($block->block_type_id == 1){
					$block_type = 'text';
				}
				
				if($block->block_type_id == 5){
					$block_type = 'table';
				}
				
				if($block->block_type_id == 6){
					$block_type = 'code';
				}
			}

			if(isset($block->file_type_id)){
				if($block->file_type_id == 1){
					$block_type = 'video';
				}

				if($block->file_type_id == 2){
					$block_type = 'audio';
				}

				if($block->file_type_id == 3){
					$block_type = 'image';
				}
			}

			if($operation_type == 'lesson'){
				$new_lesson_block = new LessonBlock();

				if($block_type == 'text'){
					$new_lesson_block->lesson_block_type_id = 1;
				}

				if($block_type == 'video'){
					$new_lesson_block->lesson_block_type_id = 2;
				}

				if($block_type == 'audio'){
					$new_lesson_block->lesson_block_type_id = 3;
				}

				if($block_type == 'image'){
					$new_lesson_block->lesson_block_type_id = 4;
				}

				if($block_type == 'table'){
					$new_lesson_block->lesson_block_type_id = 5;
				}

				if($block_type == 'code'){
					$new_lesson_block->lesson_block_type_id = 6;
				}

				$new_lesson_block->lesson_id = $target_id;
				$new_lesson_block->save();

				if($block_type == 'text'){
					$new_lesson_text = new LessonText();
					$new_lesson_text->lesson_block_id = $new_lesson_block->lesson_block_id;
					$new_lesson_text->content = $block->content;
					$new_lesson_text->save();
				}

				if($block_type == 'image'){
					$new_lesson_image = new LessonImage();
					$new_lesson_image->lesson_block_id = $new_lesson_block->lesson_block_id;
					$new_lesson_image->file_id = $block->file_id;
					$new_lesson_image->image_width = $block->image_width;
					$new_lesson_image->save();
				}

				if($block_type == 'video'){
					$new_lesson_video = new LessonVideo();
					$new_lesson_video->lesson_block_id = $new_lesson_block->lesson_block_id;
					$new_lesson_video->file_id = $block->file_id;
					$new_lesson_video->save();
				}

				if($block_type == 'audio'){
					$new_lesson_audio = new LessonAudio();
					$new_lesson_audio->lesson_block_id = $new_lesson_block->lesson_block_id;
					$new_lesson_audio->file_id = $block->file_id;
					$new_lesson_audio->save();
				}

				if($block_type == 'table'){
					$new_lesson_table = new LessonTable();
					$new_lesson_table->lesson_block_id = $new_lesson_block->lesson_block_id;
					$new_lesson_table->content = str_replace(' contenteditable="true"', '', $block->content);
					$new_lesson_table->save();
				}

				if($block_type == 'code'){
					$new_lesson_code = new LessonCode();
					$new_lesson_code->lesson_block_id = $new_lesson_block->lesson_block_id;
					$new_lesson_code->code = $block->code;
					$new_lesson_code->code_language = $block->code_language;
					$new_lesson_code->code_theme = $block->code_theme;
					$new_lesson_code->save();
				}
			}

			elseif($operation_type == 'task') {
				$new_task_block = new TaskBlock();

				if($block_type == 'text'){
					$new_task_block->task_block_type_id = 1;
				}

				if($block_type == 'video'){
					$new_task_block->task_block_type_id = 2;
				}

				if($block_type == 'audio'){
					$new_task_block->task_block_type_id = 3;
				}

				if($block_type == 'image'){
					$new_task_block->task_block_type_id = 4;
				}

				if($block_type == 'table'){
					$new_task_block->task_block_type_id = 5;
				}

				if($block_type == 'code'){
					$new_task_block->task_block_type_id = 6;
				}

				$new_task_block->task_id = $target_id;
				$new_task_block->save();

				if($block_type == 'text'){
					$new_task_text = new TaskText();
					$new_task_text->task_block_id = $new_task_block->task_block_id;
					$new_task_text->content = $block->content;
					$new_task_text->save();
				}

				if($block_type == 'image'){
					$new_task_image = new TaskImage();
					$new_task_image->task_block_id = $new_task_block->task_block_id;
					$new_task_image->file_id = $block->file_id;
					$new_task_image->image_width = $block->image_width;
					$new_task_image->save();
				}

				if($block_type == 'video'){
					$new_task_video = new TaskVideo();
					$new_task_video->task_block_id = $new_task_block->task_block_id;
					$new_task_video->file_id = $block->file_id;
					$new_task_video->save();
				}

				if($block_type == 'audio'){
					$new_task_audio = new TaskAudio();
					$new_task_audio->task_block_id = $new_task_block->task_block_id;
					$new_task_audio->file_id = $block->file_id;
					$new_task_audio->save();
				}

				if($block_type == 'table'){
					$new_task_table = new TaskTable();
					$new_task_table->task_block_id = $new_task_block->task_block_id;
					$new_task_table->content = str_replace(' contenteditable="true"', '', $block->content);
					$new_task_table->save();
				}

				if($block_type == 'code'){
					$new_task_code = new TaskCode();
					$new_task_code->task_block_id = $new_task_block->task_block_id;
					$new_task_code->code = $block->code;
					$new_task_code->code_language = $block->code_language;
					$new_task_code->code_theme = $block->code_theme;
					$new_task_code->save();
				}
			}

			elseif($operation_type == 'task_answer') {
				$new_task_answer_block = new TaskAnswerBlock();

				if($block_type == 'text'){
					$new_task_answer_block->task_answer_block_type_id = 1;
				}

				if($block_type == 'video'){
					$new_task_answer_block->task_answer_block_type_id = 2;
				}

				if($block_type == 'audio'){
					$new_task_answer_block->task_answer_block_type_id = 3;
				}

				if($block_type == 'image'){
					$new_task_answer_block->task_answer_block_type_id = 4;
				}

				if($block_type == 'table'){
					$new_task_answer_block->task_answer_block_type_id = 5;
				}

				if($block_type == 'code'){
					$new_task_answer_block->task_answer_block_type_id = 6;
				}

				$new_task_answer_block->completed_task_id = $target_id;
				$new_task_answer_block->save();

				if($block_type == 'text'){
					$new_task_answer_text = new TaskAnswerText();
					$new_task_answer_text->task_answer_block_id = $new_task_answer_block->task_answer_block_id;
					$new_task_answer_text->content = $block->content;
					$new_task_answer_text->save();
				}

				if($block_type == 'image'){
					$new_task_answer_image = new TaskAnswerImage();
					$new_task_answer_image->task_answer_block_id = $new_task_answer_block->task_answer_block_id;
					$new_task_answer_image->file_id = $block->file_id;
					$new_task_answer_image->image_width = $block->image_width;
					$new_task_answer_image->save();
				}

				if($block_type == 'video'){
					$new_task_answer_video = new TaskAnswerVideo();
					$new_task_answer_video->task_answer_block_id = $new_task_answer_block->task_answer_block_id;
					$new_task_answer_video->file_id = $block->file_id;
					$new_task_answer_video->save();
				}

				if($block_type == 'audio'){
					$new_task_answer_audio = new TaskAnswerAudio();
					$new_task_answer_audio->task_answer_block_id = $new_task_answer_block->task_answer_block_id;
					$new_task_answer_audio->file_id = $block->file_id;
					$new_task_answer_audio->save();
				}

				if($block_type == 'table'){
					$new_task_answer_table = new TaskAnswerTable();
					$new_task_answer_table->task_answer_block_id = $new_task_answer_block->task_answer_block_id;
					$new_task_answer_table->content = str_replace(' contenteditable="true"', '', $block->content);
					$new_task_answer_table->save();
				}

				if($block_type == 'code'){
					$new_task_answer_code = new TaskAnswerCode();
					$new_task_answer_code->task_answer_block_id = $new_task_answer_block->task_answer_block_id;
					$new_task_answer_code->code = $block->code;
					$new_task_answer_code->code_language = $block->code_language;
					$new_task_answer_code->code_theme = $block->code_theme;
					$new_task_answer_code->save();
				}
			}
		}
	}
}

if (!function_exists('get_blocks')){
	function get_blocks($target_id, $operation_type){
		$blocks = [];

		if($operation_type == 'lesson'){
			$lesson_blocks = LessonBlock::where('lesson_id', $target_id)->get();

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

				if($lesson_block->lesson_block_type_id == 2){
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

				if($lesson_block->lesson_block_type_id == 3){
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

				if($lesson_block->lesson_block_type_id == 4){
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
		}

		elseif($operation_type == 'task'){
			$task_blocks = TaskBlock::where('task_id', $target_id)->get();

			foreach ($task_blocks as $key => $task_block) {

				if($task_block->task_block_type_id == 1){
					$text = TaskText::where('task_block_id', $task_block->task_block_id)
					->first();
					if(isset($text)){
						$text_block = [
							'block_id' => $key + 1,
							'block_type_id' => $task_block->task_block_type_id,
							'content' => $text->content
						];
						array_push($blocks, $text_block);
					}
				}

				if($task_block->task_block_type_id == 2){
					$video = TaskVideo::leftJoin('media_files','task_videos.file_id','=','media_files.file_id')
					->where('task_videos.task_block_id', $task_block->task_block_id)
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

				if($task_block->task_block_type_id == 3){
					$audio = TaskAudio::leftJoin('media_files','task_audios.file_id','=','media_files.file_id')
					->where('task_audios.task_block_id', $task_block->task_block_id)
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

				if($task_block->task_block_type_id == 4){
					$image = TaskImage::leftJoin('media_files','task_images.file_id','=','media_files.file_id')
					->where('task_images.task_block_id', $task_block->task_block_id)
					->select(
						'media_files.file_type_id',
						'media_files.file_name',
						'media_files.file_id',
						'task_images.image_width'
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

				if($task_block->task_block_type_id == 5){
					$table = TaskTable::where('task_block_id', $task_block->task_block_id)
					->first();
					if(isset($table)){
						$table_block = [
							'block_id' => $key + 1,
							'block_type_id' => $task_block->task_block_type_id,
							'content' => $table->content
						];
						array_push($blocks, $table_block);
					}
				}

				if($task_block->task_block_type_id == 6){
					$code = TaskCode::where('task_block_id', $task_block->task_block_id)
					->first();
					if(isset($code)){
						$code_block = [
							'block_id' => $key + 1,
							'block_type_id' => $task_block->task_block_type_id,
							'code' => $code->code,
							'code_language' => $code->code_language,
							'code_theme' => $code->code_theme
						];
						array_push($blocks, $code_block);
					}
				}
			}
		}

		elseif($operation_type == 'task_answer'){
			$task_answer_blocks = TaskAnswerBlock::where('completed_task_id', $target_id)->get();

			foreach ($task_answer_blocks as $key => $task_answer_block) {

				if($task_answer_block->task_answer_block_type_id == 1){
					$text = TaskAnswerText::where('task_answer_block_id', $task_answer_block->task_answer_block_id)
					->first();
					if(isset($text)){
						$text_block = [
							'block_id' => $key + 1,
							'block_type_id' => $task_answer_block->task_answer_block_type_id,
							'content' => $text->content
						];
						array_push($blocks, $text_block);
					}
				}

				if($task_answer_block->task_answer_block_type_id == 2){
					$video = TaskAnswerVideo::leftJoin('media_files','task_answer_videos.file_id','=','media_files.file_id')
					->where('task_answer_videos.task_answer_block_id', $task_answer_block->task_answer_block_id)
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

				if($task_answer_block->task_answer_block_type_id == 3){
					$audio = TaskAnswerAudio::leftJoin('media_files','task_answer_audios.file_id','=','media_files.file_id')
					->where('task_answer_audios.task_answer_block_id', $task_answer_block->task_answer_block_id)
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

				if($task_answer_block->task_answer_block_type_id == 4){
					$image = TaskAnswerImage::leftJoin('media_files','task_answer_images.file_id','=','media_files.file_id')
					->where('task_answer_images.task_answer_block_id', $task_answer_block->task_answer_block_id)
					->select(
						'media_files.file_type_id',
						'media_files.file_name',
						'media_files.file_id',
						'task_answer_images.image_width'
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

				if($task_answer_block->task_answer_block_type_id == 5){
					$table = TaskAnswerTable::where('task_answer_block_id', $task_answer_block->task_answer_block_id)
					->first();
					if(isset($table)){
						$table_block = [
							'block_id' => $key + 1,
							'block_type_id' => $task_answer_block->task_answer_block_type_id,
							'content' => $table->content
						];
						array_push($blocks, $table_block);
					}
				}

				if($task_answer_block->task_answer_block_type_id == 6){
					$code = TaskAnswerCode::where('task_answer_block_id', $task_answer_block->task_answer_block_id)
					->first();
					if(isset($code)){
						$code_block = [
							'block_id' => $key + 1,
							'block_type_id' => $task_answer_block->task_answer_block_type_id,
							'code' => $code->code,
							'code_language' => $code->code_language,
							'code_theme' => $code->code_theme
						];
						array_push($blocks, $code_block);
					}
				}
			}
		}

		return $blocks;
	}
}  

if (!function_exists('create_test_question_blocks')){
	function create_test_question_blocks($question_id, $question_blocks){
		foreach ($question_blocks as $key => $question_block) {

			if(isset($question_block->block_type_id)){
				// if($question_block->block_type_id == 1){
				// 	$block_type = 'text';
				// }

				// if($question_block->block_type_id == 5){
				// 	$block_type = 'table';
				// }

				if($question_block->block_type_id == 6){
					$block_type = 'code';
				}
			}

			if(isset($question_block->file_type_id)){
				// if($question_block->file_type_id == 1){
				// 	$block_type = 'video';
				// }

				if($question_block->file_type_id == 2){
					$block_type = 'audio';
				}

				if($question_block->file_type_id == 3){
					$block_type = 'image';
				}
			}

			$new_question_block = new TestQuestionBlock();

			// if($block_type == 'text'){
			// 	$new_question_block->test_question_block_type_id = 1;
			// }

			// if($block_type == 'video'){
			// 	$new_question_block->test_question_block_type_id = 2;
			// }

			if($block_type == 'audio'){
				$new_question_block->test_question_block_type_id = 3;
			}

			if($block_type == 'image'){
				$new_question_block->test_question_block_type_id = 4;
			}

			// if($block_type == 'table'){
			// 	$new_question_block->test_question_block_type_id = 5;
			// }

			if($block_type == 'code'){
				$new_question_block->test_question_block_type_id = 6;
			}

			$new_question_block->question_id = $question_id;
			$new_question_block->save();

			// if($block_type == 'text'){
			// 	$new_lesson_text = new LessonText();
			// 	$new_lesson_text->test_question_block_id = $new_question_block->test_question_block_id;
			// 	$new_lesson_text->content = $question_block->content;
			// 	$new_lesson_text->save();
			// }

			if($block_type == 'image'){
				$new_lesson_image = new TestQuestionImage();
				$new_lesson_image->test_question_block_id = $new_question_block->test_question_block_id;
				$new_lesson_image->file_id = $question_block->file_id;
				$new_lesson_image->save();
			}

			// if($block_type == 'video'){
			// 	$new_lesson_video = new LessonVideo();
			// 	$new_lesson_video->test_question_block_id = $new_question_block->test_question_block_id;
			// 	$new_lesson_video->file_id = $question_block->file_id;
			// 	$new_lesson_video->save();
			// }

			if($block_type == 'audio'){
				$new_lesson_audio = new TestQuestionAudio();
				$new_lesson_audio->test_question_block_id = $new_question_block->test_question_block_id;
				$new_lesson_audio->file_id = $question_block->file_id;
				$new_lesson_audio->save();
			}

			// if($block_type == 'table'){
			// 	$new_lesson_table = new LessonTable();
			// 	$new_lesson_table->test_question_block_id = $new_question_block->test_question_block_id;
			// 	$new_lesson_table->content = str_replace(' contenteditable="true"', '', $question_block->content);
			// 	$new_lesson_table->save();
			// }

			if($block_type == 'code'){
				$new_lesson_code = new TestQuestionCode();
				$new_lesson_code->test_question_block_id = $new_question_block->test_question_block_id;
				$new_lesson_code->code = $question_block->code;
				$new_lesson_code->code_language = $question_block->code_language;
				$new_lesson_code->code_theme = $question_block->code_theme;
				$new_lesson_code->save();
			}
		}
	}
}

if (!function_exists('get_test_question_materials')){
	function get_test_question_materials($question_id){
		$blocks = [];
		$test_question_blocks = TestQuestionBlock::where('question_id', $question_id)->get();

		foreach ($test_question_blocks as $key => $test_question_block) {

			// if($test_question_block->test_question_block_type_id == 1){
			// 	$text = LessonText::where('test_question_block_id', $test_question_block->test_question_block_id)
			// 	->first();
			// 	if(isset($text)){
			// 		$text_block = [
			// 			'block_id' => $key + 1,
			// 			'block_type_id' => $test_question_block->test_question_block_type_id,
			// 			'content' => $text->content
			// 		];
			// 		array_push($blocks, $text_block);
			// 	}
			// }
			
			// if($test_question_block->test_question_block_type_id == 2){
			// 	$video = LessonVideo::leftJoin('media_files','test_question_videos.file_id','=','media_files.file_id')
			// 	->where('test_question_videos.test_question_block_id', $test_question_block->test_question_block_id)
			// 	->select(
			// 		'media_files.file_type_id',
			// 		'media_files.file_name',
			// 		'media_files.file_id'
			// 	)
			// 	->first();
			// 	if(isset($video)){
			// 		$video_block = [
			// 			'block_id' => $key + 1,
			// 			'file_type_id' => $video->file_type_id,
			// 			'file_id' => $video->file_id,
			// 			'file_name' => $video->file_name
			// 		];
			// 		array_push($blocks, $video_block);
			// 	}
			// }
			
			if($test_question_block->test_question_block_type_id == 3){
				$audio = TestQuestionAudio::leftJoin('media_files','test_question_audios.file_id','=','media_files.file_id')
				->where('test_question_audios.test_question_block_id', $test_question_block->test_question_block_id)
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
			
			if($test_question_block->test_question_block_type_id == 4){
				$image = TestQuestionImage::leftJoin('media_files','test_question_images.file_id','=','media_files.file_id')
				->where('test_question_images.test_question_block_id', $test_question_block->test_question_block_id)
				->select(
					'media_files.file_type_id',
					'media_files.file_name',
					'media_files.file_id',
					'test_question_images.image_width'
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
			
			// if($test_question_block->test_question_block_type_id == 5){
			// 	$table = LessonTable::where('test_question_block_id', $test_question_block->test_question_block_id)
			// 	->first();
			// 	if(isset($table)){
			// 		$table_block = [
			// 			'block_id' => $key + 1,
			// 			'block_type_id' => $test_question_block->test_question_block_type_id,
			// 			'content' => $table->content
			// 		];
			// 		array_push($blocks, $table_block);
			// 	}
			// }

			if($test_question_block->test_question_block_type_id == 6){
				$code = TestQuestionCode::where('test_question_block_id', $test_question_block->test_question_block_id)
				->first();
				if(isset($code)){
					$code_block = [
						'block_id' => $key + 1,
						'block_type_id' => $test_question_block->test_question_block_type_id,
						'code' => $code->code,
						'code_language' => $code->code_language,
						'code_theme' => $code->code_theme
					];
					array_push($blocks, $code_block);
				}
			}
		}

		return $blocks;
	}
}
?>