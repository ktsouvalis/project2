@php
    $courses = \App\Models\Course::all();
    $user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Courses Index</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>Courses</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Open Seats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                    <tr>
                        <td>{{ $course->name }}</td>
                        <td>{{$course->open_seats}}</td>
                        <td>
                            @if($user->courses->contains($course))
                                <form action="{{ route('users_courses.unregister', ['course'=> $course->id, 'user'=>$user->id ]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Unregister</button>
                                </form>
                            @else
                                <form action="{{ route('users_courses.register', ['course'=> $course->id, 'user'=>$user->id ]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Register</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if (session('failure'))
        <div class="alert alert-danger">
            {{ session('failure') }}
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>