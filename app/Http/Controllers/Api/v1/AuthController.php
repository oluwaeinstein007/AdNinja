<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    //this method adds new users
    /**
     * Create an Account
    */

    //create Super Admin account
    public function createAccount(Request $request)
    {
        $attr =Validator::make($request->all(), [
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        // if there is errors  with the validation, return the errors
        if ($attr->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $attr->errors()
            ], 422);
        }

        $user = User::create([
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'user_role_id' => 1,
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'success',
            'token' => $token,
            'data' => $user
        ], 201);
    }


    //user/admin registration
    public function createAdminUser(Request $request){
        $attr =Validator::make($request->all(), [
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        // if there is errors  with the validation, return the errors
        if ($attr->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $attr->errors()
            ], 422);
        }

        $user = User::create([
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'user_role_id' => $request->role,
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'success',
            'token' => $token,
            'data' => $user
        ], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        // attempt to authenticate user
        if (auth()->attempt(['email' => $request->email, 'password' => $request->password],$request->remember)) {
            // if successful, return token
            return response()->json([
                'token' => auth()->user()->createToken('authToken')->plainTextToken,
                'user' => auth()->user()
            ]);
        } else {
            // if not successful, return error
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
    }


    public function socialAuth(Request $request){
        // Get user by email
        $user = User::where('email', $request->input('email'))->first();

        //Check if user has an account
        if($user){
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'success',
                'token' => $token,
                'user' => $user
            ], 200);
        }

        //make password
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $password = '';

        for ($i = 0; $i < 6; $i++) {
            $index = rand(0, strlen($chars) - 1);
            $password .= $chars[$index];
        }

        $user = User::create([
            'password' => Hash::make($password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'profile_picture' => $request->avatar,
            'user_role_id' => 4,
            'is_social' => true,
            'social_type' => $request->type,
        ]);
        $this->updateInfo($user);
        // Create token for the new user
        $token = $user->setConnection($this->db)->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'success',
            'token' => $token,
            'data' => $user
        ], 201);
    }


    // this method logs out users by removing tokens
    public function logout()
    {
        auth()->user()->setConnection($this->db)->tokens()->delete();

        return response()->json(['message' => 'success'], 200);
    }


    /* change User password */
    public function changePassword(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'failed', 'error'=>$validator->errors()], 400);
        }
        if(!password_verify($request->old_password, $user->password)) {
            return response()->json(['message' => 'failed', 'error'=>'Old password is incorrect'], 400);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['message' => 'success'], 200);
    }

}
