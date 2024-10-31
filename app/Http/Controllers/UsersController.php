<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UsersController extends Controller
{
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            session()->regenerate();
            Cache::remember("my_courses_".$user->id, 3600, function(){
                return auth()->user()->courses;
            });
            return redirect('/');
        }
        abort(401);
    }

    public function logout(Request $request) {
        Cache::forget('my_courses_'.auth()->user()->id);
        Auth::logout();
        $request->session()->invalidate();
        
        return redirect('/');
    }

    public function issue_token() {
        $user = Auth::user();
        $existingToken = $user->tokens()
            ->where('name', 'Personal Access Token')
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingToken) {
            $existingToken->revoke();
        }
        
        $token = $user->createToken('Personal Access Token', ['manage-courses'])->accessToken;
        return redirect('/')->with('token', $token);
    }
}
