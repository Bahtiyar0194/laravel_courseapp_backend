<?php 
use App\Models\LessonBlock;
use App\Models\LessonText;
use App\Models\LessonImage;
use App\Models\LessonVideo;
use App\Models\LessonAudio;

if (!function_exists('create_lesson_blocks')){
	function create_lesson_blocks($lesson_id, $lesson_blocks){
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
		}
	}
}
?>