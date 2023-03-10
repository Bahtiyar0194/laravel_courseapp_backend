<?php

namespace App\Http\Controllers;

use App\Models\LessonTask;
use App\Models\TestQuestion;
use App\Models\TestQuestionAnswer;
use Illuminate\Http\Request;

use App\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Validator;

class TaskController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function my_tasks(Request $request){
        $my_tasks = LessonTask::leftJoin('lessons','lessons.lesson_id','=','lesson_tasks.lesson_id')
        ->leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->leftJoin('schools','courses.school_id','=','schools.school_id')
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lesson_tasks.lesson_id', $request->lesson_id)
        ->get();

        return response()->json($my_tasks, 200);
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'task_name' => 'required|string|between:3, 300',
            'task_description' => 'required|string|max:1000',
            'task_type_id' => 'required',
            'test_question_blocks' => 'required_unless:task_type_id,1|min:3',
            'test_question_blocks_error' => 'required|declined',
            'operation_type_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Task create error', 422, $validator->errors());
        }

        $new_task = new LessonTask();
        $new_task->task_name = $request->task_name;
        $new_task->task_description = $request->task_description;
        $new_task->lesson_id = $request->lesson_id;
        $new_task->task_type_id = $request->task_type_id;
        $new_task->save();

        if($request->task_type_id == 1){
            $test_question_blocks = json_decode($request->test_question_blocks);

            foreach ($test_question_blocks as $key => $block) {
                $new_question = new TestQuestion();
                $new_question->question = $block->question;
                $new_question->task_id = $new_task->task_id;
                $new_question->save();

                foreach ($block->answers as $key => $answer) {
                    $new_question_answer = new TestQuestionAnswer();
                    $new_question_answer->answer = $answer->answer_value;
                    $new_question_answer->question_id = $new_question->question_id;
                    if($answer->checked == true){
                        $new_question_answer->is_correct = 1;
                    }
                    else{
                        $new_question_answer->is_correct = 0;
                    }

                    $new_question_answer->save();
                }
            }
        }

        return $this->json('success', 'Task create success', 200, 'success');

    }


}