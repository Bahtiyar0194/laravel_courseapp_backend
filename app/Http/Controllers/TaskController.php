<?php

namespace App\Http\Controllers;

use App\Models\LessonTask;
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
            'task_description' => 'nullable|required_unless:task_type_id,1|string|max:1000',
            'task_type_id' => 'required',
            'lesson_id' => 'required',
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

    }


}