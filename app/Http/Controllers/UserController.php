<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\ThreadReply;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Exception;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        if (empty($email) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
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


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function enrollCourse(Request $request, $id)
    {
        try {

            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $userID = $user->id;
            $enroll = new Enrollment();
            $enroll->user_id = $userID;
            $enroll->course_id = $id;

            if ($enroll->save()) {
                return response()->json(['status' => 'success', 'message' => 'Now you enroll for the course successfully']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Something bad happend']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function replyOnThread(Request $request, $id)
    {
        try {

            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $userID = $user->id;
            $threadReply = new ThreadReply();
            $threadReply->body = $request->body;
            $threadReply->thread_id = $id;
            $threadReply->user_id = $userID;
            if ($user->type == 'user') {
                if ($threadReply->save()) {
                    return response()->json(['status' => 'success', 'message' => 'You reply on this thread successfully']);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Something bad happend']);
                }
            } else {
                return response()->json(['status' => 'error', 'message' => 'Sorry you cannot reply here']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
