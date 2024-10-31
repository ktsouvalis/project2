<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Course;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    private function updateCache(User $user, Course $course) {
        // Update my_courses cache
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

        // Update all_courses cache
        $allCourses = Cache::get('courses.all', Course::all());
        $courseIndex = $allCourses->search(function ($c) use ($course) {
            return $c->id == $course->id;
        });
        if ($courseIndex !== false) {
            $allCourses[$courseIndex]->open_seats = $course->open_seats;
        }
        Cache::put('courses.all', $allCourses, 3600);
    }

    public function register(Request $request, Course $course, User $user){
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
            $this->updateCache($user, $course);
            return redirect()->route('users_courses.index')->with('success', 'User registered successfully');
        } 
        catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('users_courses.index')->with('failure', $e->getMessage());
        }
    }

    public function unregister(Request $request, Course $course, User $user){
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
            $this->updateCache($user, $course);
            return redirect()->route('users_courses.index')->with('success', 'User unregistered successfully');
        } 
        catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('users_courses.index')->with('failure',  $e->getMessage());
        }
    }
}
