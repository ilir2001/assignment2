<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Exception;

class AdminController extends Controller
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
            $user->type = "admin";


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
    public function deleteCourse(Request $request, $id)
    {
        try {
            $course = Course::findOrFail($id);
            if($request->user()->can('delete', $course)) {
                if ($course->delete()) {
                    return response()->json(['status' => 'success', 'message' => 'Post deleted successfully']);
                }
            }
            else {
                return response()->json(['status' => 'error', 'message' => 'You cannot delete this post besouse is not yours']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error' , 'message' => $e->getMessage()]);
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
}
