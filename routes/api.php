<?php
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\MediaController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1'
], function ($router) {

    Route::group([
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/get_activation_user/{hash}', [AuthController::class, 'get_activation_user']);
        Route::post('/activate_user/{hash}', [AuthController::class, 'activate_user']);
        Route::post('/accept_invitation/{hash}', [AuthController::class, 'accept_invitation']);
        Route::post('/forgot_password', [AuthController::class, 'forgot_password']);
        Route::post('/password_recovery', [AuthController::class, 'password_recovery']);
        Route::get('/get_avatar/{avatar_file}', [AuthController::class, 'get_avatar']);

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/change_mode/{role_type_id}', [AuthController::class, 'change_mode']);
            Route::post('/update', [AuthController::class, 'update']);
            Route::post('/upload_avatar', [AuthController::class, 'upload_avatar']);
            Route::post('/delete_avatar', [AuthController::class, 'delete_avatar']);
            Route::post('/change_password', [AuthController::class, 'change_password']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::group([
        'prefix' => 'school'
    ], function ($router) {
        Route::get('/get', [SchoolController::class, 'get_school'])->middleware('check_subdomain');
        Route::get('/get_logo/{logo_file}/{logo_variable}', [SchoolController::class, 'get_logo']);

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/get_attributes', [SchoolController::class, 'get_school_attributes']);
            Route::post('/set_attributes', [SchoolController::class, 'set_school_attributes']);
            Route::post('/update', [SchoolController::class, 'update']);
            Route::post('/upload_logo', [SchoolController::class, 'upload_logo']);
            Route::post('/delete_logo/{logo_variable}', [SchoolController::class, 'delete_logo']);
        });
    });

    Route::group([
        'prefix' => 'subscription_plan'
    ], function ($router) {
        Route::get('/get', [SubscriptionPlanController::class, 'get']);
    });

    Route::group([
        'prefix' => 'contacts'
    ], function ($router) {
                //Route::view('/email', 'emails/welcome');
        Route::post('/send_feedback', [ContactController::class, 'send_feedback']);
    });

    Route::group([
        'prefix' => 'languages'
    ], function ($router) {
        Route::get('/get', [LanguageController::class, 'index']);
    });

    Route::group([
        'prefix' => 'users'
    ], function ($router) {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::post('/get', [UserController::class, 'get_users']);
            Route::get('/get/{user_id}', [UserController::class, 'get_user']);
            Route::get('/get_roles', [UserController::class, 'get_roles']);
            Route::post('/invite', [UserController::class, 'invite_user'])->middleware('check_roles'); 
            Route::post('/update/{user_id}', [UserController::class, 'update_user'])->middleware('check_roles');
        });
    });

    Route::group([
        'prefix' => 'groups'
    ], function ($router) {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/get_group_attributes', [GroupController::class, 'get_group_attributes']);
            Route::post('/get', [GroupController::class, 'get_groups']);
            Route::post('/create', [GroupController::class, 'create'])->middleware('check_roles');
            Route::get('/get/{group_id}', [GroupController::class, 'get_group']);
            Route::post('/update/{group_id}', [GroupController::class, 'update'])->middleware('check_roles');
        });
    });

    Route::group([
        'prefix' => 'media'
    ], function ($router) {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/get_disk_space', [MediaController::class, 'get_disk_space']);
            Route::post('/get', [MediaController::class, 'get_school_files']);
            Route::get('/types_of_media_files', [MediaController::class, 'types_of_media_files']);

            Route::post('/upload_video', [MediaController::class, 'upload_video'])->middleware('check_roles');
            Route::post('/upload_audio', [MediaController::class, 'upload_audio'])->middleware('check_roles');
            Route::post('/upload_image', [MediaController::class, 'upload_image'])->middleware('check_roles');

            Route::get('/get_videos', [MediaController::class, 'get_videos']);
            Route::get('/get_audios', [MediaController::class, 'get_audios']);
            Route::get('/get_images', [MediaController::class, 'get_images']);

            Route::post('/update/{file_id}', [MediaController::class, 'update_file'])->middleware('check_roles');
            Route::post('/delete/{file_id}', [MediaController::class, 'delete_file'])->middleware('check_roles');
        });

        Route::get('/video/{file_id}', [MediaController::class, 'get_video']);
        Route::get('/audio/{file_id}', [MediaController::class, 'get_audio']);
        Route::get('/image/{file_id}', [MediaController::class, 'get_image']);
    });

    Route::group([
        'prefix' => 'courses'
    ], function ($router) {
        Route::get('/images/posters/{filename}', [CourseController::class, 'poster']);
        Route::get('/videos/trailers/{filename}', [CourseController::class, 'trailer']);
        Route::get('/get_invitation/{hash}', [CourseController::class, 'get_invitation']);

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/get_course_attributes', [CourseController::class, 'get_course_attributes']);
            Route::get('/get-courses', [CourseController::class, 'get_courses']);
            Route::get('/my-courses', [CourseController::class, 'my_courses']);
            Route::get('/my-courses/{course_id}', [CourseController::class, 'course']);
            Route::post('/free_subscribe/{course_id}', [CourseController::class, 'free_subscribe']);
            Route::post('/get_subscribers/{course_id}', [CourseController::class, 'get_subscribers']);
            Route::post('/get_invites/{course_id}', [CourseController::class, 'get_invites']);
            Route::post('/invite_subscriber/{course_id}', [CourseController::class, 'invite_subscriber'])->middleware('check_roles');
            Route::post('/get_requests/{course_id}', [CourseController::class, 'get_requests']);
            Route::post('/accept_request/{request_id}', [CourseController::class, 'accept_request'])->middleware('check_roles');
            Route::post('/create', [CourseController::class, 'create'])->middleware('check_roles');
            Route::post('/update/{course_id}', [CourseController::class, 'update'])->middleware('check_roles');
            Route::post('/create_review/{course_id}', [CourseController::class, 'create_review']);
        });
    });

    Route::group([
        'prefix' => 'lessons'
    ], function ($router){
     Route::group(['middleware' => ['auth:sanctum']], function () {
         Route::post('/create', [LessonController::class, 'create'])->middleware('check_roles');
         Route::post('/update/{lesson_id}', [LessonController::class, 'edit'])->middleware('check_roles');
         Route::post('/delete/{lesson_id}', [LessonController::class, 'delete'])->middleware('check_roles');
         Route::get('/my-lessons/{course_id}', [LessonController::class, 'my_lessons']);
         Route::get('/{lesson_id}', [LessonController::class, 'get_lesson']);
         Route::post('/set_order', [LessonController::class, 'set_order'])->middleware('check_roles');
     });
 });

    Route::group([
        'prefix' => 'tasks'
    ], function ($router){
        Route::group(['middleware' => ['auth:sanctum']], function () {
         Route::get('/{task_id}', [TaskController::class, 'get_task']);
         Route::get('/my-tasks/{lesson_id}', [TaskController::class, 'my_tasks']);
         Route::post('/create/{lesson_id}', [TaskController::class, 'create'])->middleware('check_roles');
         Route::post('/create_answer/{task_id}', [TaskController::class, 'create_answer'])->middleware('check_roles');
         Route::get('/test/get_test_question/{task_id}', [TaskController::class, 'get_test_question']);
         Route::post('/test/save_user_answer/{answer_id}', [TaskController::class, 'save_user_answer']);
     });
    });
});