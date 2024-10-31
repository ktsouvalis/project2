<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Course;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use App\Mail\CourseRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class UsersCoursesController extends Controller
{
    public function index(){
        $courses = Cache::get('courses.all');
        $myCourses = Cache::get('my_courses_'.auth()->user()->id);
        return view('users_courses_index')
            ->with(['courses' => $courses, 'myCourses' => $myCourses]);
    }

    private function update_global_courses_cache(Course $course) {
        $allCourses = Cache::get('courses.all', Course::all());
        $courseIndex = $allCourses->search(function ($c) use ($course) {
            return $c->id == $course->id;
        });
        if ($courseIndex !== false) {
            $allCourses[$courseIndex]->open_seats = $course->open_seats;
        }
        Cache::put('courses.all', $allCourses, 3600);
    }

    private function update_my_courses_cache(Course $course, User $user){
        $myCourses = Cache::get('my_courses_' . $user->id);
        $found = false;
        $courseIndex = $myCourses->search(function ($c) use ($course){
            return $c->id == $course->id;
        });
        if(!$courseIndex){
            $myCourses->push($course);
        }
        else{
            $myCourses->forget($courseIndex);  
        }
        Cache::put('my_courses_' . $user->id, $myCourses, 3600);
    }

    private function hidden_register(Course $course, User $user){
        DB::beginTransaction();
        try {
            // Pessimistic locking
            $course->lockForUpdate();
        
            if ($course->open_seats > 0) {
                $course->open_seats--;
                $course->save();
                UserCourse::updateOrCreate([
                    'user_id' => $user->id,
                    'course_id' => $course->id
                ],
                [
                    'user_id' => $user->id,
                    'course_id' => $course->id
                ]);
            }
            else {
                throw new Exception('No open seats available');
            }
            DB::commit();
            return ['status' => 'success', 'message'=> 'User registered successfully'];
        }
        catch (Exception $e) {
            DB::rollBack();
            return ['status' => 'failure', 'message'=> $e->getMessage()];
        }
    }

    private function hidden_unregister(Course $course, User $user){
        DB::beginTransaction();
        try {
            // Pessimistic locking
            $course->lockForUpdate();
        
            $course->open_seats++;
            $course->save();
            $record = UserCourse::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            $record->delete();
            
            DB::commit();
            return ['status' => 'success', 'message'=> 'User unregistered successfully'];
        } 
        catch (Exception $e) {
            DB::rollBack();
            return ['status' => 'failure', 'message'=> $e->getMessage()];
        }
    }

    public function register(Course $course, User $user){
        $attempt = $this->hidden_register($course, $user);
        if($attempt['status'] == 'success'){
            $this->update_global_courses_cache($course);
            $this->update_my_courses_cache($course, $user);
            Mail::to($user->email)->queue(new CourseRegistration($course->name, $user->name));
        }
        return redirect()->route('users_courses.index')->with($attempt['status'], $attempt['message']);   
    }

    public function unregister(Course $course, User $user){
        $attempt = $this->hidden_unregister($course, $user);
        if($attempt['status'] == 'success'){
            $this->update_global_courses_cache($course);
            $this->update_my_courses_cache($course, $user);
        }
        return redirect()->route('users_courses.index')->with($attempt['status'], $attempt['message']);
    }

    public function api_register(Course $course){
        $user = auth()->guard('api')->user();
        $attempt = $this->hidden_register($course, $user);
        if($attempt['status'] == 'success'){
            $this->update_global_courses_cache($course);
        }
        return response()->json($attempt);
    }

    public function api_unregister(Course $course){
        $user = auth()->guard('api')->user();
        $attempt = $this->hidden_unregister($course, $user);
        if($attempt['status'] == 'success'){
            $this->update_global_courses_cache($course);
            Mail::to($user->email)->queue(new CourseRegistration($course->name, $user->name));
        }
        return response()->json($attempt);
    }
}
