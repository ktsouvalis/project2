<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersCoursesController extends Controller
{
    public function index(){
        $courses = Course::all();
        return view('users_courses_index')
            ->with(['courses' => $courses]);
    }

    public function register(Request $request, Course $course, User $user){
        DB::beginTransaction();
        try {
            // Pessimistic locking
            $course = $course->lockForUpdate()->first();
        
            if ($course->open_seats > 0) {
                $course->open_seats--;
                $course->save();
                UserCourse::updateOrCreate([
                    'user_id' => $user->id,
                    'course_id' => $course->id
                ]);
            }
        
            DB::commit();
            return redirect()->route('users_courses.index')->with('success', 'User registered successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users_courses.index')->with('failure', $e->getMessage());
        }
    }

    public function unregister(Request $request, Course $course, User $user){
        DB::beginTransaction();
        try {
            // Pessimistic locking
            $course = $course->lockForUpdate()->first();
        
            $course->open_seats++;
            $course->save();
            $record = UserCourse::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            $record->delete();
        
            DB::commit();
            return redirect()->route('users_courses.index')->with('success', 'User unregistered successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users_courses.index')->with('failure',  $e->getMessage());
        }
    }
}
