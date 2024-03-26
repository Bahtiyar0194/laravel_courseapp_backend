<?php

namespace App\Http\Controllers;

use App\Models\UserOperation;
use App\Models\Task;
use App\Models\TestProperty;
use App\Models\TestQuestion;
use App\Models\TestQuestionAnswer;
use App\Models\TestQuestionUserAnswer;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\TaskBlock;
use App\Models\TaskText;
use App\Models\TaskTable;
use App\Models\TaskCode;
use App\Models\TaskImage;
use App\Models\TaskVideo;
use App\Models\TaskAudio;
use App\Models\Language;

use App\Models\CompletedTask;

use App\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Validator;
use DB;

class TaskController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_task(Request $request){
        $find_task = Task::leftJoin('lessons','lessons.lesson_id','=','tasks.lesson_id')
        ->leftJoin('courses','courses.course_id','=','lessons.course_id')
        ->select(
            'tasks.task_id',
            'tasks.task_name',
            'tasks.task_description',
            'tasks.task_type_id',
            'lessons.lesson_id',
            'lessons.lesson_name',
            'courses.course_id',
            'courses.course_name'
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('tasks.task_id', $request['task_id'])
        ->first();

        if(isset($find_task)){

            if($find_task->task_type_id != 1){
                //App/Helpers
                $find_task->task_blocks = get_blocks($find_task->task_id, 'task');
            }

            return response()->json($find_task, 200);
        }
        else{
            return response()->json('Task not found', 404);
        }

    }

    public function get_attributes(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $task_types = DB::table('types_of_tasks')
        ->leftJoin('types_of_tasks_lang','types_of_tasks.task_type_id', '=', 'types_of_tasks_lang.task_type_id')
        ->where('types_of_tasks_lang.lang_id', '=', $language->lang_id)
        ->select(
            'types_of_tasks.task_type_id',
            'types_of_tasks_lang.task_type_name'
        )
        ->get();

        $attributes = new \stdClass();

        $attributes->types_of_tasks = $task_types;

        return response()->json($attributes, 200);
    }

    public function my_tasks(Request $request){
        $my_tasks = Task::leftJoin('lessons','lessons.lesson_id','=','tasks.lesson_id')
        ->leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->leftJoin('schools','courses.school_id','=','schools.school_id')
        ->leftJoin('types_of_tasks','tasks.task_type_id','=','types_of_tasks.task_type_id')
        ->leftJoin('types_of_tasks_lang','types_of_tasks.task_type_id','=','types_of_tasks_lang.task_type_id')
        ->leftJoin('languages','types_of_tasks_lang.lang_id','=','languages.lang_id')
        ->select(
            'tasks.task_id',
            'tasks.task_name',
            'tasks.task_description',
            'tasks.lesson_id',
            'tasks.task_type_id',
            'types_of_tasks_lang.task_type_name'
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('tasks.lesson_id', $request->lesson_id)
        ->where('languages.lang_tag', $request->header('Accept-Language'))
        ->get();

        return response()->json($my_tasks, 200);
    }

    public function my_typical_tasks(Request $request){

        $per_page = $request->per_page ? $request->per_page : 10;

        $tasks = Task::leftJoin('types_of_tasks', 'tasks.task_type_id', '=', 'types_of_tasks.task_type_id')
        ->leftJoin('lessons', 'tasks.lesson_id', '=', 'lessons.lesson_id')
        ->leftJoin('courses', 'lessons.course_id', '=', 'courses.course_id')
        ->leftJoin('users_courses','courses.course_id','=','users_courses.course_id')
        ->select(
            'tasks.task_id',
            'tasks.task_name',
            'tasks.task_description',
            'tasks.lesson_id',
            'tasks.task_type_id',
            'types_of_tasks.task_type_slug',
            'tasks.created_at',
            'lessons.lesson_name'
        )
        ->where('users_courses.recipient_id', '=', auth()->user()->user_id)
        ->whereNotIn('tasks.task_id', CompletedTask::select('task_id')
            ->where('executor_id', '=', auth()->user()->user_id)
            ->get()
            ->toArray()
        )
        ->where('tasks.task_type_id', '=', 2)
        ->orderBy('tasks.created_at', 'desc');

        $task_name = $request->task_name;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if(!empty($task_name)){
            $tasks->where('tasks.task_name','LIKE','%'.$task_name.'%');
        }

        if($created_at_from && $created_at_to) {
            $tasks->whereBetween('tasks.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $tasks->where('tasks.created_at','>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $tasks->where('tasks.created_at','<=', $created_at_to.' 23:59:00');
        }

        return response()->json($tasks->paginate($per_page)->onEachSide(1), 200);
    }

    public function my_tests(Request $request){

        $per_page = $request->per_page ? $request->per_page : 10;

        $tasks = Task::leftJoin('types_of_tasks', 'tasks.task_type_id', '=', 'types_of_tasks.task_type_id')
        ->leftJoin('lessons', 'tasks.lesson_id', '=', 'lessons.lesson_id')
        ->leftJoin('courses', 'lessons.course_id', '=', 'courses.course_id')
        ->leftJoin('users_courses','courses.course_id','=','users_courses.course_id')
        ->select(
            'tasks.task_id',
            'tasks.task_name',
            'tasks.task_description',
            'tasks.lesson_id',
            'tasks.task_type_id',
            'types_of_tasks.task_type_slug',
            'tasks.created_at',
            'lessons.lesson_name'
        )
        ->where('users_courses.recipient_id', '=', auth()->user()->user_id)
        ->whereNotIn('tasks.task_id', CompletedTask::select('task_id')
            ->where('executor_id', '=', auth()->user()->user_id)
            ->get()
            ->toArray()
        )
        ->whereIn('tasks.task_type_id', [1,4])
        ->orderBy('tasks.created_at', 'desc');

        $task_name = $request->task_name;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if(!empty($task_name)){
            $tasks->where('tasks.task_name','LIKE','%'.$task_name.'%');
        }

        if($created_at_from && $created_at_to) {
            $tasks->whereBetween('tasks.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $tasks->where('tasks.created_at','>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $tasks->where('tasks.created_at','<=', $created_at_to.' 23:59:00');
        }

        return response()->json($tasks->paginate($per_page)->onEachSide(1), 200);
    }

    public function my_personal_tasks(Request $request){

        $per_page = $request->per_page ? $request->per_page : 10;

        $auth_user = auth()->user();

        $tasks = Task::leftJoin('types_of_tasks', 'tasks.task_type_id', '=', 'types_of_tasks.task_type_id')
        ->leftJoin('users as executor', 'tasks.executor_id', '=', 'executor.user_id')
        ->leftJoin('users as creator', 'tasks.creator_id', '=', 'creator.user_id')
        ->select(
            'tasks.task_id',
            'tasks.task_name',
            'tasks.task_description',
            'tasks.task_type_id',
            'types_of_tasks.task_type_slug',
            'executor.first_name as executor_first_name',
            'executor.last_name as executor_last_name',
            'executor.avatar as executor_avatar',
            'creator.first_name as creator_first_name',
            'creator.last_name as creator_last_name',
            'creator.avatar as creator_avatar',
            'tasks.created_at'
        )
        ->whereNotIn('tasks.task_id', CompletedTask::select('task_id')
            ->where('executor_id', '=', auth()->user()->user_id)
            ->get()
            ->toArray()
        )
        ->where('tasks.task_type_id', '=', 3)
        ->orderBy('tasks.created_at', 'desc');

        if($auth_user->current_role_id == 2){
            $tasks->where('executor.school_id', '=', auth()->user()->school_id);
        }
        elseif($auth_user->current_role_id == 3){
            $tasks->where('tasks.creator_id', '=', auth()->user()->user_id);
        }
        elseif($auth_user->current_role_id == 4){
            $tasks->where('tasks.executor_id', '=', auth()->user()->user_id);
        }

        $task_name = $request->task_name;
        $executor = $request->executor;
        $creator = $request->mentor;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if(!empty($task_name)){
            $tasks->where('tasks.task_name','LIKE','%'.$task_name.'%');
        }

        if(!empty($executor)){
            $tasks->whereRaw("CONCAT(executor.last_name, ' ', executor.first_name) LIKE ?", ['%'.$executor.'%']);
        }

        if(!empty($creator)){
            $tasks->whereRaw("CONCAT(creator.last_name, ' ', creator.first_name) LIKE ?", ['%'.$creator.'%']);
        }

        if($created_at_from && $created_at_to) {
            $tasks->whereBetween('tasks.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $tasks->where('tasks.created_at','>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $tasks->where('tasks.created_at','<=', $created_at_to.' 23:59:00');
        }

        return response()->json($tasks->paginate($per_page)->onEachSide(1), 200);
    }

    public function my_verification_tasks(Request $request){

        $per_page = $request->per_page ? $request->per_page : 10;

        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $auth_user = auth()->user();

        $tasks = CompletedTask::leftJoin('tasks', 'completed_tasks.task_id', '=', 'tasks.task_id')
        ->leftJoin('users as executor', 'completed_tasks.executor_id', '=', 'executor.user_id')
        ->leftJoin('users as inspector', 'completed_tasks.inspector_id', '=', 'inspector.user_id')
        ->leftJoin('types_of_tasks', 'tasks.task_type_id', '=', 'types_of_tasks.task_type_id')
        ->leftJoin('types_of_tasks_lang', 'types_of_tasks.task_type_id', '=', 'types_of_tasks_lang.task_type_id')
        ->select(
            'tasks.task_id',
            'tasks.task_name',
            'tasks.task_description',
            'tasks.task_type_id',
            'types_of_tasks.task_type_slug',
            'types_of_tasks_lang.task_type_name',
            'executor.first_name as executor_first_name',
            'executor.last_name as executor_last_name',
            'executor.avatar as executor_avatar',
            'inspector.first_name as inspector_first_name',
            'inspector.last_name as inspector_last_name',
            'inspector.avatar as inspector_avatar',
            'completed_tasks.created_at'
        )
        ->orderBy('completed_tasks.created_at', 'desc');

        if($auth_user->current_role_id == 2){
            $tasks->where('executor.school_id', '=', auth()->user()->school_id);
        }
        elseif($auth_user->current_role_id == 3){
            $tasks->where('completed_tasks.inspector_id', '=', auth()->user()->user_id);
        }
        elseif($auth_user->current_role_id == 4){
            $tasks->where('completed_tasks.executor_id', '=', auth()->user()->user_id);
        }


        $task_name = $request->task_name;
        $task_type_id = $request->task_type_id;
        $executor = $request->executor;
        $inspector = $request->mentor;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if(!empty($task_name)){
            $tasks->where('tasks.task_name','LIKE','%'.$task_name.'%');
        }

        if(!empty($task_type_id)){
            $tasks->where('tasks.task_type_id', '=', $task_type_id);
        }

        if(!empty($executor)){
            $tasks->whereRaw("CONCAT(executor.last_name, ' ', executor.first_name) LIKE ?", ['%'.$executor.'%']);
        }

        if(!empty($inspector)){
            $tasks->whereRaw("CONCAT(inspector.last_name, ' ', inspector.first_name) LIKE ?", ['%'.$inspector.'%']);
        }

        if($created_at_from && $created_at_to) {
            $tasks->whereBetween('completed_tasks.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $tasks->where('completed_tasks.created_at', '>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $tasks->where('completed_tasks.created_at', '<=', $created_at_to.' 23:59:00');
        }

        $tasks->where('completed_tasks.status_type_id', '=', 8)
        ->where('types_of_tasks_lang.lang_id', '=', $language->lang_id);

        return response()->json($tasks->paginate($per_page)->onEachSide(1), 200);
    }

    public function my_completed_tasks(Request $request){

        $per_page = $request->per_page ? $request->per_page : 10;

        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $auth_user = auth()->user();

        $tasks = CompletedTask::leftJoin('tasks', 'completed_tasks.task_id', '=', 'tasks.task_id')
        ->leftJoin('users as executor', 'completed_tasks.executor_id', '=', 'executor.user_id')
        ->leftJoin('users as inspector', 'completed_tasks.inspector_id', '=', 'inspector.user_id')
        ->leftJoin('types_of_tasks', 'tasks.task_type_id', '=', 'types_of_tasks.task_type_id')
        ->leftJoin('types_of_tasks_lang', 'types_of_tasks.task_type_id', '=', 'types_of_tasks_lang.task_type_id')
        ->select(
            'tasks.task_id',
            'tasks.task_name',
            'tasks.task_description',
            'tasks.task_type_id',
            'types_of_tasks.task_type_slug',
            'types_of_tasks_lang.task_type_name',
            'executor.user_id as executor_id',
            'executor.first_name as executor_first_name',
            'executor.last_name as executor_last_name',
            'executor.avatar as executor_avatar',
            'inspector.first_name as inspector_first_name',
            'inspector.last_name as inspector_last_name',
            'inspector.avatar as inspector_avatar',
            'completed_tasks.created_at'
        )
        ->orderBy('completed_tasks.created_at', 'desc');

        if($auth_user->current_role_id == 2){
            $tasks->where('executor.school_id', '=', auth()->user()->school_id);
        }
        elseif($auth_user->current_role_id == 3){
            $tasks->where('completed_tasks.inspector_id', '=', auth()->user()->user_id);
        }
        elseif($auth_user->current_role_id == 4){
            $tasks->where('completed_tasks.executor_id', '=', auth()->user()->user_id);
        }


        $task_name = $request->task_name;
        $task_type_id = $request->task_type_id;
        $executor = $request->executor;
        $inspector = $request->mentor;
        $created_at_from = $request->created_at_from;
        $created_at_to = $request->created_at_to;

        if(!empty($task_name)){
            $tasks->where('tasks.task_name','LIKE','%'.$task_name.'%');
        }

        if(!empty($task_type_id)){
            $tasks->where('tasks.task_type_id', '=', $task_type_id);
        }

        if(!empty($executor)){
            $tasks->whereRaw("CONCAT(executor.last_name, ' ', executor.first_name) LIKE ?", ['%'.$executor.'%']);
        }

        if(!empty($inspector)){
            $tasks->whereRaw("CONCAT(inspector.last_name, ' ', inspector.first_name) LIKE ?", ['%'.$inspector.'%']);
        }

        if($created_at_from && $created_at_to) {
            $tasks->whereBetween('completed_tasks.created_at', [$created_at_from.' 00:00:00', $created_at_to.' 23:59:00']);
        }
        
        if($created_at_from){
            $tasks->where('completed_tasks.created_at', '>=', $created_at_from.' 00:00:00');
        }
        
        if($created_at_to){
            $tasks->where('completed_tasks.created_at', '<=', $created_at_to.' 23:59:00');
        }

        $tasks->where('completed_tasks.status_type_id', '=', 10)
        ->where('types_of_tasks_lang.lang_id', '=', $language->lang_id);

        return response()->json($tasks->paginate($per_page)->onEachSide(1), 200);
    }

    public function create(Request $request){

        if($request->task_type_id == 1){
            $validator = Validator::make($request->all(), [
                'task_name' => 'required|string|between:3, 300',
                'task_description' => 'required|string|max:3000',
                'task_type_id' => 'required',
                'test_question_blocks' => 'required|min:3',
                'test_question_blocks_error' => 'required|declined',
                'operation_type_id' => 'required'
            ]);
        }
        elseif($request->task_type_id == 2){
            $validator = Validator::make($request->all(), [
                'task_name' => 'required|string|between:3, 300',
                'task_description' => 'required|string|max:3000',
                'task_type_id' => 'required',
                'operation_type_id' => 'required'
            ]);
        }
        elseif($request->task_type_id == 4){
            $validator = Validator::make($request->all(), [
                'task_name' => 'required|string|between:3, 300',
                'task_description' => 'required|string|max:3000',
                'task_type_id' => 'required',
                'properties' => 'required|min:3',
                'test_question_blocks' => 'required|min:3',
                'test_question_blocks_error' => 'required|declined',
                'operation_type_id' => 'required'
            ]);
        }

        if($validator->fails()){
            return $this->json('error', 'Task create error', 422, $validator->errors());
        }

        $new_task = new Task();
        $new_task->task_name = $request->task_name;
        $new_task->task_description = $request->task_description;
        $new_task->task_type_id = $request->task_type_id;
        $new_task->lesson_id = $request->lesson_id;
        $new_task->creator_id = auth()->user()->user_id;
        $new_task->save();


        if($request->task_type_id == 4){
            $properties = json_decode($request->properties);

            foreach ($properties as $key => $property) {
                $new_property = new TestProperty();
                $new_property->property_name = $property->item_value;
                $new_property->property_description = $property->item_description;
                $new_property->task_id = $new_task->task_id;
                $new_property->save();
            }
        }

        if($request->task_type_id == 1 || $request->task_type_id == 4){
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

                    if($request->task_type_id == 1){
                        if($answer->checked == true){
                            $new_question_answer->is_correct = 1;
                        }
                        else{
                            $new_question_answer->is_correct = 0;
                        }
                    }
                    elseif($request->task_type_id == 4){
                        $property = TestProperty::where('property_name', '=', $answer->answer_property)
                        ->where('task_id', '=', $new_task->task_id)
                        ->first();

                        if(isset($property)){
                            $new_question_answer->property_id = $property->property_id;
                        }
                    }

                    $new_question_answer->save();
                }
            }
        }
        elseif($request->task_type_id == 2){
            //App/Helpers
            create_blocks($new_task->task_id, json_decode($request->task_blocks), 'task');
        }

        $user_operation = new UserOperation();
        $user_operation->operator_id = auth()->user()->user_id;
        $user_operation->operation_type_id = 6;
        $user_operation->save();

        return $this->json('success', 'Task create success', 200, 'success');

    }


    public function create_answer(Request $request){

        $validator = Validator::make($request->all(), [
            'task_answer' => 'required|string|between:3, 3000',
            'operation_type_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Task answer create error', 422, $validator->errors());
        }

        $completed_task = new CompletedTask();
        $completed_task->task_id = $request->task_id;
        $completed_task->executor_id = auth()->user()->user_id;
        $completed_task->answer = $request->task_answer;
        $completed_task->save();

        return $this->json('success', 'Task answer create success', 200, 'success');
    }

    public function get_test_question(Request $request){

        $test = Task::find($request->task_id);

        if(isset($request->executor_id)){
            $executor_id = $request->executor_id;
            $executor = User::find($executor_id);
        }
        else{
            $executor_id = auth()->user()->user_id;
            $executor = auth()->user();
        }

        if(isset($test)){
            $question = TestQuestion::where('task_id', '=', $request->task_id)             
            ->whereNotIn('test_questions.question_id', TestQuestionUserAnswer::select('test_questions.question_id')
                ->leftJoin('test_question_answers', 'test_question_user_answers.answer_id', '=', 'test_question_answers.answer_id')
                ->leftJoin('test_questions', 'test_question_answers.question_id', '=', 'test_questions.question_id')
                ->where('user_id', '=', $executor_id)
                ->get()
                ->toArray()
            )
            ->select(
                'test_questions.question_id',
                'test_questions.question'
            )
            ->inRandomOrder()
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
                ->where('user_id', '=', $executor_id)
                ->where('test_questions.task_id', '=', $request->task_id)
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

                    $analytic_test_result = [];

                    if($test->task_type_id == 4){
                        $properties = TestProperty::where('task_id', '=', $test->task_id)
                        ->select(
                            'property_id',
                            'property_name',
                            'property_description'
                        )
                        ->get();

                        $answers_count = count(TestQuestionAnswer::leftJoin('test_questions', 'test_question_answers.question_id', '=', 'test_questions.question_id')
                            ->where('test_questions.task_id', '=', $request->task_id)
                            ->get());

                        foreach ($properties as $key => $property) {
                            $property_count = TestQuestionUserAnswer::leftJoin('test_question_answers', 'test_question_user_answers.answer_id', '=', 'test_question_answers.answer_id')
                            ->where('test_question_answers.property_id', '=', $property->property_id)
                            ->where('test_question_user_answers.user_id', '=', $executor_id)
                            ->get();

                            array_push($analytic_test_result, [
                                'property_name' => $property->property_name, 
                                'property_description' => $property->property_description,
                                'property_count' => count($property_count),
                                'prop_percentage' => (count($property_count) / ($answers_count / count($properties))) * 100
                            ]);
                        }

                        usort($analytic_test_result, function ($a, $b) {
                            return $b['property_count'] <=> $a['property_count'];
                        });
                    }

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
                            ->where('user_id', '=', $executor_id)
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
                        'correct_answers_count' => $correct_answers_count,
                        'analytic_test_result' => $analytic_test_result,
                        'executor' => $executor->last_name.' '.$executor->first_name
                    ];

                    $completed_task = CompletedTask::where('task_id', '=', $request->task_id)
                    ->where('executor_id', '=', $executor_id)
                    ->first();

                    $mentor = Task::leftJoin('lessons', 'tasks.lesson_id', '=', 'lessons.lesson_id')
                    ->leftJoin('courses', 'lessons.course_id', '=', 'courses.course_id')
                    ->leftJoin('users_courses','courses.course_id','=','users_courses.course_id')
                    ->where('users_courses.recipient_id', '=', $executor_id)
                    ->select(
                        'users_courses.mentor_id'
                    )->first();


                    if(!isset($completed_task)){
                        $new_completed_task = new CompletedTask();
                        $new_completed_task->task_id = $request->task_id;
                        $new_completed_task->executor_id = $executor_id;
                        $new_completed_task->inspector_id = $mentor->mentor_id;
                        $new_completed_task->status_type_id = 10;
                        $new_completed_task->save();
                    }
                }

                return response()->json($result, 200);
            }
            else{
                return response()->json('Test questions not found', 404);
            }
        }
        else{
            return response()->json('Test not found', 404);
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