<?php
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SchoolController;
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

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::group([
        'prefix' => 'school'
    ], function ($router) {
        Route::get('/get', [SchoolController::class, 'get_school'])->middleware('check_subdomain');
    });

    Route::group([
        'prefix' => 'languages'
    ], function ($router) {
        Route::get('/get', [LanguageController::class, 'index']);
    });

    Route::group([
        'prefix' => 'courses'
    ], function ($router) {
        Route::get('/get_categories', [CourseCategoryController::class, 'index']);
        Route::get('/images/posters/{filename}', [CourseController::class, 'poster']);

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/my-courses', [CourseController::class, 'my_courses']);
            Route::get('/my-courses/{course_id}', [CourseController::class, 'course']);
            Route::post('/create', [CourseController::class, 'create']);
        });
    });

    Route::group([
        'prefix' => 'lessons'
    ], function ($router) {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/my-lessons/{course_id}', [LessonController::class, 'my_lessons']);
            Route::post('/set_order', [LessonController::class, 'set_order']);
            Route::post('/create', [LessonController::class, 'create']);
        });
    });
});