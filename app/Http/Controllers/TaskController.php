<?php

namespace App\Http\Controllers;

use App\Models\UserOperation;
use App\Models\LessonTask;
use App\Models\TestQuestion;
use App\Models\TestQuestionAnswer;
use App\Models\TestQuestionUserAnswer;
use Illuminate\Http\Request;

use App\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Validator;

class TaskController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_task(Request $request){
        $find_task = LessonTask::leftJoin('lessons','lessons.lesson_id','=','lesson_tasks.lesson_id')
        ->leftJoin('courses','courses.course_id','=','lessons.course_id')
        ->select(
            'lesson_tasks.task_id',
            'lesson_tasks.task_name',
            'lesson_tasks.task_description',
            'lessons.lesson_id',
            'lessons.lesson_name',
            'courses.course_id',
            'courses.course_name'
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lesson_tasks.task_id', $request['task_id'])
        ->first();

        if(isset($find_task)){
            return response()->json($find_task, 200);
        }
        else{
            return response()->json('Task not found', 404);
        }

    }

    public function my_tasks(Request $request){
        $my_tasks = LessonTask::leftJoin('lessons','lessons.lesson_id','=','lesson_tasks.lesson_id')
        ->leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->leftJoin('schools','courses.school_id','=','schools.school_id')
        ->leftJoin('types_of_tasks','lesson_tasks.task_type_id','=','types_of_tasks.task_type_id')
        ->leftJoin('types_of_tasks_lang','types_of_tasks.task_type_id','=','types_of_tasks_lang.task_type_id')
        ->leftJoin('languages','types_of_tasks_lang.lang_id','=','languages.lang_id')
        ->select(
            'lesson_tasks.task_id',
            'lesson_tasks.task_name',
            'lesson_tasks.task_description',
            'lesson_tasks.lesson_id',
            'lesson_tasks.task_type_id',
            'types_of_tasks_lang.task_type_name'
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lesson_tasks.lesson_id', $request->lesson_id)
        ->where('languages.lang_tag', $request->header('Accept-Language'))
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

                if(isset($block->question_materials)){
                    if(count($block->question_materials) > 0){
                        //App/Helpers
                        create_test_question_blocks($new_question->question_id, $block->question_materials);
                    }
                }

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

        $user_operation = new UserOperation();
        $user_operation->operator_id = auth()->user()->user_id;
        $user_operation->operation_type_id = 6;
        $user_operation->save();

        return $this->json('success', 'Task create success', 200, 'success');

    }



    public function get_test_question(Request $request){

        $question = TestQuestion::where('task_id', '=', $request->task_id)             
        ->whereNotIn('test_questions.question_id', TestQuestionUserAnswer::select('test_questions.question_id')
            ->leftJoin('test_question_answers', 'test_question_user_answers.answer_id', '=', 'test_question_answers.answer_id')
            ->leftJoin('test_questions', 'test_question_answers.question_id', '=', 'test_questions.question_id')
            ->where('user_id', '=', auth()->user()->user_id)
            ->get()
            ->toArray())
        ->select(
            'test_questions.question_id',
            'test_questions.question'
        )
        ->first();

        if(isset($question)){
            $question_materials = get_test_question_materials($question->question_id);

            $question_answers = TestQuestionAnswer::where('question_id', '=', $question->question_id)
            ->select(
                'answer_id',
                'answer'
            )->get();

            $question = [
                'question' => $question->question,
                'question_materials' => $question_materials,
                'question_answers' => $question_answers
            ];
        }


        $all_questions = TestQuestion::where('task_id', '=', $request->task_id)->get();

        if(count($all_questions) > 0){
            $answered_questions = TestQuestionUserAnswer::select('test_questions.question_id')
            ->leftJoin('test_question_answers', 'test_question_user_answers.answer_id', '=', 'test_question_answers.answer_id')
            ->leftJoin('test_questions', 'test_question_answers.question_id', '=', 'test_questions.question_id')
            ->where('user_id', '=', auth()->user()->user_id)
            ->get();

            $progress = (count($answered_questions) * 100) / count($all_questions);

            if($progress < 100){
                $result = [
                    'all_questions_count' => count($all_questions),
                    'answered_questions_count' => count($answered_questions),
                    'progress' => $progress,
                    'question' => $question
                ];
            }
            else{
                $questions = [];
                $correct_answers_count = 0;

                foreach ($all_questions as $key => $question) {
                    $question_materials = get_test_question_materials($question->question_id);

                    $question_answers = TestQuestionAnswer::where('question_id', '=', $question->question_id)
                    ->select(
                        'answer_id',
                        'answer',
                        'is_correct'
                    )->get();

                    $answers = [];

                    foreach ($question_answers as $i => $question_answer) {
                        $find_user_answer = TestQuestionUserAnswer::where('answer_id', '=', $question_answer->answer_id)
                        ->where('user_id', '=', auth()->user()->user_id)
                        ->first();

                        $selected_by_user = false;

                        if(isset($find_user_answer)){
                            $selected_by_user = true;
                            if($question_answer->is_correct == 1){
                                $correct_answers_count += 1;
                            }
                        }

                        array_push($answers, [
                            'answer_id' => $question_answer->answer_id,
                            'answer' => $question_answer->answer,
                            'is_correct' => $question_answer->is_correct,
                            'selected_by_user' => $selected_by_user
                        ]);
                    }

                    array_push($questions, [
                        'question_id' => $question->question_id,
                        'question' => $question->question,
                        'question_materials' => $question_materials,
                        'question_answers' => $answers
                    ]);
                }

                $result = [
                    'all_questions' => $questions,
                    'all_questions_count' => count($all_questions),
                    'answered_questions_count' => count($answered_questions),
                    'progress' => $progress,
                    'correct_answers_count' => $correct_answers_count
                ];
            }

            return response()->json($result, 200);
        }
        else{
            return response()->json('Test questions not found', 404);
        }
    }

    public function save_user_answer(Request $request){
        $user_answer_exist = TestQuestionUserAnswer::where('answer_id', '=', $request->answer_id)
        ->where('user_id', '=', auth()->user()->user_id)
        ->first();

        if(!isset($user_answer_exist)){
            $save_user_answer = new TestQuestionUserAnswer();
            $save_user_answer->answer_id = $request->answer_id;
            $save_user_answer->user_id = auth()->user()->user_id;
            $save_user_answer->save();

            return response()->json('Success', 200);
        }
        else{
            return response()->json('User answer already exists', 409);
        }
    }
}