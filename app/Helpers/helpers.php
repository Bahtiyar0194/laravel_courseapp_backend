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
?>