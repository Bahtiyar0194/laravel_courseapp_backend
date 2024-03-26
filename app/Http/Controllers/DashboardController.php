<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Group;
use App\Models\Language;
use App\Models\School;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Task;
use App\Models\LessonView;
use App\Models\CompletedTask;

use Mail;
use App\Mail\WelcomeMail;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Str;
use Validator;
use DB;

class DashboardController extends Controller
{
    use ApiResponser;

    public function __construct(Request $request){
        app()->setLocale($request->header('Accept-Language'));
    }

    public function get(Request $request){
        $language = Language::where('lang_tag', '=', $request->header('Accept-Language'))->first();

        $users = User::where('users.school_id', '=', auth()->user()->school_id)
        ->where('users.status_type_id', '!=', 2)
        ->select(
            'users.last_name',
            'users.first_name',
            'users.status_type_id'
        )
        ->get();

        $users_count = count($users);
        $invites = [];

        foreach ($users as $key => $user) {
            if($user->status_type_id == 4){
                array_push($invites, $user);
            }
        }

        $roles = UserRole::leftJoin('users', 'users_roles.user_id', '=', 'users.user_id')
        ->where('users.school_id', '=', auth()->user()->school_id)
        ->where('users.status_type_id', '!=', 2)
        ->select(
            'users.last_name',
            'users.first_name',
            'users.status_type_id',
            'users_roles.role_type_id'
        )
        ->get();

        $users = new \stdClass();

        $admins = [];
        $instructors = [];
        $learners = [];

        foreach ($roles as $key => $role) {
            if($role->status_type_id == 1){
                if($role->role_type_id == 2){
                    array_push($admins, $role);
                }
                elseif($role->role_type_id == 3){
                    array_push($instructors, $role);
                }
                elseif($role->role_type_id == 4){
                    array_push($learners, $role);
                }
            }
        }

        $groups = Group::leftJoin('groups_members', 'groups.group_id', '=', 'groups_members.group_id')
        ->leftJoin('users', 'groups.mentor_id', '=', 'users.user_id')
        ->leftJoin('schools', 'schools.school_id', '=', 'users.school_id')
        ->where('users.school_id', '=', auth()->user()->school_id)
        ->where('show_status_id', 1)
        ->groupBy('groups.group_id')
        ->get();

        $users->users_count = $users_count;
        $users->admins = $admins;
        $users->instructors = $instructors;
        $users->learners = $learners;
        $users->invites = $invites;
        $users->groups = count($groups);



        $all_courses = Course::where('courses.school_id', auth()->user()->school_id)
        ->where('courses.show_status_id', 1)
        ->get();

        $my_courses = Course::leftJoin('users_courses','courses.course_id','=','users_courses.course_id')
        ->where('users_courses.recipient_id', '=', auth()->user()->user_id)
        ->where('courses.school_id', '=', auth()->user()->school_id)
        ->where('courses.show_status_id', '=', 1)
        ->get();

        $courses = new \stdClass();

        $courses->courses_count = count($all_courses);
        $courses->my_courses_count = count($my_courses);

        $my_lessons = Lesson::leftJoin('courses', 'lessons.course_id', '=', 'courses.course_id')
        ->leftJoin('users_courses','courses.course_id', '=', 'users_courses.course_id')
        ->where('users_courses.recipient_id', '=', auth()->user()->user_id)
        ->where('courses.school_id', '=', auth()->user()->school_id)
        ->where('courses.show_status_id', '=', 1)
        ->get();

        $my_viewed_lessons = LessonView::leftJoin('lessons', 'lessons_views.lesson_id', '=', 'lessons.lesson_id')
        ->leftJoin('courses', 'lessons.course_id', '=', 'courses.course_id')
        ->leftJoin('users_courses','courses.course_id', '=' ,'users_courses.course_id')
        ->where('lessons_views.viewer_id', '=', auth()->user()->user_id)
        ->where('users_courses.recipient_id', '=', auth()->user()->user_id)
        ->where('courses.school_id', '=', auth()->user()->school_id)
        ->where('courses.show_status_id', '=', 1)
        ->groupBy('lessons_views.lesson_id')
        ->get();

        $lessons = new \stdClass();

        $lessons->lessons_count = count($my_lessons);
        $lessons->viewed_lessons_count = count($my_viewed_lessons);

        $tests = 0;
        $lesson_tasks = 0;
        $personal = 0;
        $on_verification = 0;
        $completed = 0;

        $tests_and_lesson_tasks = Task::leftJoin('lessons', 'tasks.lesson_id', '=', 'lessons.lesson_id')
        ->leftJoin('courses', 'lessons.course_id', '=', 'courses.course_id')
        ->leftJoin('users_courses','courses.course_id','=','users_courses.course_id')
        ->where('users_courses.recipient_id', '=', auth()->user()->user_id)
        ->get();

        foreach ($tests_and_lesson_tasks as $key => $value) {
            if($value->task_type_id == 1 || $value->task_type_id == 4){
                $tests += 1;
            }
            elseif($value->task_type_id == 2){
                $lesson_tasks += 1;
            }
        }

        $personal_tasks = Task::where('executor_id', '=', auth()->user()->user_id)
        ->get();

        $completed_tasks = CompletedTask::where('executor_id', '=', auth()->user()->user_id)
        ->get();

        foreach ($completed_tasks as $key => $value) {
            if($value->status_type_id == 8){
                $on_verification += 1;
            }
            elseif($value->status_type_id == 10){
                $completed += 1;
            }
        }


        $tasks = new \stdClass();

        $tasks->all_tasks_count = (count($tests_and_lesson_tasks) + count($personal_tasks));
        $tasks->lesson_tasks_count = $lesson_tasks;
        $tasks->test_tasks_count = $tests;
        $tasks->personal_tasks_count = count($personal_tasks);
        $tasks->verification_tasks_count = $on_verification;
        $tasks->completed_tasks_count = $completed;



        $attributes = new \stdClass();

        $attributes->users = $users;
        $attributes->courses = $courses;
        $attributes->lessons = $lessons;
        $attributes->tasks = $tasks;


        return response()->json($attributes, 200);
    }
}
