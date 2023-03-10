<?php 
use App\Models\LessonBlock;
use App\Models\LessonText;
use App\Models\LessonTable;
use App\Models\LessonCode;
use App\Models\LessonImage;
use App\Models\LessonVideo;
use App\Models\LessonAudio;

use App\Models\TestQuestionBlock;
use App\Models\TestQuestionImage;
use App\Models\TestQuestionAudio;
use App\Models\TestQuestionCode;

if (!function_exists('create_lesson_blocks')){
	function create_lesson_blocks($lesson_id, $lesson_blocks){
		foreach ($lesson_blocks as $key => $lesson_block) {

			if(isset($lesson_block->block_type_id)){
				if($lesson_block->block_type_id == 1){
					$block_type = 'text';
				}
				
				if($lesson_block->block_type_id == 5){
					$block_type = 'table';
				}
				
				if($lesson_block->block_type_id == 6){
					$block_type = 'code';
				}
			}

			if(isset($lesson_block->file_type_id)){
				if($lesson_block->file_type_id == 1 || $lesson_block->file_type_id == 2){
					$block_type = 'video';
				}

				if($lesson_block->file_type_id == 3){
					$block_type = 'audio';
				}

				if($lesson_block->file_type_id == 4){
					$block_type = 'image';
				}
			}

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

			$new_lesson_block->lesson_id = $lesson_id;
			$new_lesson_block->save();

			if($block_type == 'text'){
				$new_lesson_text = new LessonText();
				$new_lesson_text->lesson_block_id = $new_lesson_block->lesson_block_id;
				$new_lesson_text->content = $lesson_block->content;
				$new_lesson_text->save();
			}

			if($block_type == 'image'){
				$new_lesson_image = new LessonImage();
				$new_lesson_image->lesson_block_id = $new_lesson_block->lesson_block_id;
				$new_lesson_image->file_id = $lesson_block->file_id;
				$new_lesson_image->image_width = $lesson_block->image_width;
				$new_lesson_image->save();
			}

			if($block_type == 'video'){
				$new_lesson_video = new LessonVideo();
				$new_lesson_video->lesson_block_id = $new_lesson_block->lesson_block_id;
				$new_lesson_video->file_id = $lesson_block->file_id;
				$new_lesson_video->save();
			}

			if($block_type == 'audio'){
				$new_lesson_audio = new LessonAudio();
				$new_lesson_audio->lesson_block_id = $new_lesson_block->lesson_block_id;
				$new_lesson_audio->file_id = $lesson_block->file_id;
				$new_lesson_audio->save();
			}

			if($block_type == 'table'){
				$new_lesson_table = new LessonTable();
				$new_lesson_table->lesson_block_id = $new_lesson_block->lesson_block_id;
				$new_lesson_table->content = str_replace(' contenteditable="true"', '', $lesson_block->content);
				$new_lesson_table->save();
			}

			if($block_type == 'code'){
				$new_lesson_code = new LessonCode();
				$new_lesson_code->lesson_block_id = $new_lesson_block->lesson_block_id;
				$new_lesson_code->code = $lesson_block->code;
				$new_lesson_code->code_language = $lesson_block->code_language;
				$new_lesson_code->code_theme = $lesson_block->code_theme;
				$new_lesson_code->save();
			}
		}
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
				// if($question_block->file_type_id == 1 || $question_block->file_type_id == 2){
				// 	$block_type = 'video';
				// }

				if($question_block->file_type_id == 3){
					$block_type = 'audio';
				}

				if($question_block->file_type_id == 4){
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