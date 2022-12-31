<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDetails;
use App\Models\Course;
use App\Models\Thread;
use Illuminate\Http\Request;
use Exception;

class InstructorController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        if(empty($email) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    public function register(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $phone_number = $request->phone_number;
        $address = $request->address;


        // Check if field is not empty
        if (empty($name) or empty($email) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 'error', 'message' => 'You must enter a valid email']);
        }

        // Check if password is greater than 5 character
        if (empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        }

        if (empty($phone_number)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        if (empty($address)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if user already exist
        if (User::where('email', '=', $email)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'User already exists with this email']);
        }

        // Create new user
        try {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = app('hash')->make($password);
            $user->type = "instructor";


            if ($user->save()) {
                $userDetails = new UserDetails();
                $userDetails->phone_number = $phone_number;
                $userDetails->address = $address;
                $userDetails->user_id = $user->id;
                $userDetails->save();
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function createCourse(Request $request) {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $userID = $user->id;
            $user = User::find($userID);
            $course = new Course();
            $course->name = $request->name;
            $course->description = $request->description;
            $course->user_id = $userID;

            $credentials = request([$user->email, $user->password]);


            if($user->type == 'instructor') {
                if ($course->save()) {
                    return response()->json(['status' => 'success', 'message' => 'Course created successfully']);
                }
            }
            else {
                return response()->json(['status' => 'error', 'message' => 'Sorry you dont have premissions to create courese']);
            }

        } catch (Exception $e) {
            return response()->json(['status' => 'error' , 'message' => $e->getMessage()]);
        }
    }

    public function createThread(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $course = Course::find($id);
        $userID = $user->id;
        $user = User::find($userID);
        $thread = new Thread();
        $thread->title = $request->title;
        $thread->body = $request->body;
        $thread->instructor_id = $user->id;
        $thread->course_id = $id;
        if($user->type == 'instructor' && $user->id == $course->user_id ) {
            if ($thread->save()) {
                return response()->json(['status' => 'success', 'message' => 'Threat in course is created created successfully']);
            }
        }
        else {
            return response()->json(['status' => 'error', 'message' => 'Sorry you dont have premissions to threat in coureses']);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
