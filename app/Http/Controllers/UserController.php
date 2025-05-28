<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;   
class UserController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request data
        $validate = Validator::make($request->all(),[
            'name' => 'required|string|',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors()], 422);
        }
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Email already exists'], 409);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        
       

        return response()->json([
            
            'user' => $user,
            'message' => 'User register successful'
        ], 200);

       

       
    }
    public function login(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required'
    ]);
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

        $credentials = $request->only('email', 'password');
        if( auth()->attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken('auth_token')->accessToken; // Create a token for the user
             // Add token to user object
            return response()->json([
                'token' => $token,
                'message' => 'Login successful',
                'user' => $user
                
            ], 200);
        }
        return response()->json(['message' => 'Invalid credentials'], 401);
        
        
    }
   public function user()
{
    $user = auth()->user();

    return response()->json([
        'user' => $user,
        'message' => 'User  retrieved successfully'
    ], 200);
}
public function logout(Request $request){
   $user = $request->user();
   $token = $user->token();
   $token->revoke(); // Revoke the token to log out the user
    return response()->json([
        'message' => 'User logged out successfully'
    ], 200);
}
public function resetPassword(Request $request)
{
    // Validate the request data
     $validator = Validator::make($request->all(), [
        'current_password' => 'required',
        'new_password' => 'required|min:6',
    ]);
    
    
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }
     $user = auth()->user();
    if (!password_verify($request->current_password, $user->password)) {
        return response()->json(['message' => 'Current password is incorrect'], 403);
    }
    if ($request->current_password === $request->new_password) {
        return response()->json(['message' => 'New password cannot be the same as current password'], 400);
    }
    $user->password = bcrypt($request->new_password);
    $user->save();
   
    return response()->json(['message' => 'Password updated successfully',
    'user'=>$user], 200);
}
    
    

    // Find the user by email
   

    

}
