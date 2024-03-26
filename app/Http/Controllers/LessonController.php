<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Lesson;
use App\Models\LessonView;
use App\Models\Task;
use App\Models\LessonBlock;
use App\Models\UserCourse;
use App\Models\UserRole;
use App\Models\UserOperation;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use DB;

class LessonController extends Controller{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get_lesson(Request $request){
        $find_lesson = Lesson::leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->leftJoin('types_of_lessons','lessons.lesson_type_id','=','types_of_lessons.lesson_type_id')
        ->leftJoin('types_of_lessons_lang','types_of_lessons.lesson_type_id','=','types_of_lessons_lang.lesson_type_id')
        ->leftJoin('languages','types_of_lessons_lang.lang_id','=','languages.lang_id')
        ->select(
            'lessons.lesson_id',
            'lessons.lesson_name',
            'lessons.lesson_description',
            'lessons.created_at',
            'lessons.lesson_type_id',
            'types_of_lessons_lang.lesson_type_name',
            'courses.course_id',
            'courses.course_name'
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lessons.lesson_id', $request->lesson_id)
        ->where('languages.lang_tag', $request->header('Accept-Language'))
        ->first();

        if(isset($find_lesson)){

            if(isset($find_lesson->course_id)){
                $subscribed = UserCourse::where('recipient_id', '=', auth()->user()->user_id)
                ->where('course_id', '=', $find_lesson->course_id)
                ->first();

                $is_admin = UserRole::where('user_id', '=', auth()->user()->user_id)
                ->where('role_type_id', '=', 2)
                ->first();

                if(isset($subscribed) || isset($is_admin)){
                    $find_lesson->subscribed = true;
                }
                else{
                    $find_lesson->subscribed = false;
                }
            }

            //App/Helpers
            $lesson_blocks = get_blocks($find_lesson->lesson_id, 'lesson');

            $lessons = Lesson::where('course_id', '=', $find_lesson->course_id)
            ->where('lesson_type_id', '=', 1)
            ->orderBy('sort_num', 'asc')
            ->get();

            foreach ($lessons as $key => $value) {
                if($value->lesson_id == $find_lesson->lesson_id){
                    $current_lesson_key = $key;
                }
            }

            if($current_lesson_key > 0){
                $previous_lesson = $lessons[$current_lesson_key - 1];
                $find_lesson->previous_lesson = $previous_lesson;
            }

            if(($current_lesson_key + 1) < count($lessons)){
                $next_lesson = $lessons[$current_lesson_key + 1];
                $find_lesson->next_lesson = $next_lesson;
            }

            $lesson = [
                'lesson' => $find_lesson,
                'lesson_blocks' => $lesson_blocks
            ];

            $new_lesson_view = new LessonView();
            $new_lesson_view->lesson_id = $request->lesson_id;
            $new_lesson_view->viewer_id = auth()->user()->user_id;
            $new_lesson_view->save();

            return response()->json($lesson, 200);
        }
        else{
            return response()->json('Lesson not found', 404);
        }
    }

    public function my_lessons(Request $request){
        $my_lessons = Lesson::leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->select(
            'lessons.lesson_id',
            'lessons.lesson_name',
            'lessons.lesson_description',
            'lessons.created_at',
            'lessons.lesson_type_id',
        )
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lessons.course_id', $request['course_id'])
        ->orderBy('lessons.sort_num', 'asc')
        ->get();

        $materials_count = 0;
        $sections_count = 0;
        $total_count = 0;

        foreach ($my_lessons as $key => $value) {
            if($value->lesson_type_id != 2){
                $materials_count += 1;

                $views_count = count(LessonView::where('lesson_id', '=', $value->lesson_id)->get());
                $my_lessons[$key]->views_count = $views_count;

                $tasks_count = count(Task::where('lesson_id', '=', $value->lesson_id)->get());
                $my_lessons[$key]->tasks_count = $tasks_count;
            }
            else{
                $sections_count += 1;
            }
            $total_count += 1;
        }

        return response()->json([
            'my_lessons' => $my_lessons,
            'materials_count' => $materials_count,
            'sections_count' => $sections_count,
            'total_count' => $total_count
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){

        $validator = Validator::make($request->all(), [
            'lesson_name' => 'required|string|between:3, 300',
            'lesson_description' => 'required_unless:lesson_type_id,2|string|min:10',
            'lesson_type_id' => 'required',
            'lesson_blocks' => 'required_unless:lesson_type_id,2|min:3',
            'course_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Lesson create error', 422, $validator->errors());
        }

        $count_lessons = Lesson::where("course_id", $request->course_id)->count();
        $sort_num = $count_lessons + 1;

        $new_lesson = new Lesson();
        $new_lesson->lesson_name = $request->lesson_name;
        $new_lesson->lesson_description = $request->lesson_description;
        $new_lesson->course_id = $request->course_id;
        $new_lesson->lesson_type_id = $request->lesson_type_id;
        $new_lesson->sort_num = $sort_num;
        $new_lesson->save();

        if($request->lesson_type_id != 2){
            //App/Helpers
            create_blocks($new_lesson->lesson_id, json_decode($request->lesson_blocks), 'lesson');

            $user_operation = new UserOperation();
            $user_operation->operator_id = auth()->user()->user_id;
            $user_operation->operation_type_id = 4;
            $user_operation->save();
        }

        return $this->json('success', 'Lesson create successful', 200, $new_lesson);
    }


    public function edit(Request $request){
        $validator = Validator::make($request->all(), [
            'lesson_name' => 'required|string|between:3, 300',
            'lesson_description' => 'required_unless:lesson_type_id,2|string|min:10',
            'lesson_type_id' => 'required',
            'lesson_blocks' => 'required_unless:lesson_type_id,2|min:3'
        ]);

        if($validator->fails()){
            return $this->json('error', 'Lesson update error', 422, $validator->errors());
        }

        $find_lesson = Lesson::leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lessons.lesson_id', $request['lesson_id'])
        ->first();

        if(isset($find_lesson)){
            $edit_lesson = Lesson::find($find_lesson->lesson_id);
            $edit_lesson->lesson_name = $request->lesson_name;

            if($find_lesson->lesson_type_id != 2){
                $edit_lesson->lesson_description = $request->lesson_description;

                LessonBlock::where('lesson_id', $find_lesson->lesson_id)
                ->delete();

                //App/Helpers
                create_blocks($find_lesson->lesson_id, json_decode($request->lesson_blocks), 'lesson');

                $user_operation = new UserOperation();
                $user_operation->operator_id = auth()->user()->user_id;
                $user_operation->operation_type_id = 10;
                $user_operation->save();
            }

            $edit_lesson->save();

            return $this->json('success', 'Lesson update successful', 200, 'success');
        }
        else{
            return response()->json('Lesson not found', 404);
        }
    }


    public function delete(Request $request){
        $delete_lesson = Lesson::leftJoin('courses','lessons.course_id','=','courses.course_id')
        ->where('courses.school_id', auth()->user()->school_id)
        ->where('lessons.lesson_id', $request['lesson_id'])
        ->delete();

        return $this->json('success', 'Lesson delete successful', 200, $delete_lesson);
    }

    public function set_order(Request $request){
        $array = explode(',', $request->lessons_id);
        for ($i=0; $i < count($array); $i++) { 
            $lesson = Lesson::where('lesson_id', $array[$i])
            ->where('course_id', $request->course_id)
            ->first();
            $lesson->sort_num = $i+1;
            $lesson->save();
        }
        return response()->json('Success', 200);
    }
}
