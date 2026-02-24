<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function register(Request $request)
    {
        $data=$request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|string|email|max:255|unique:users',
            'password'=>'required|string|min:8|confirmed',
            'role'=>'required|in:admin,cashier',
            'shop_id'=>'nullable|exists:shops,id',
        ]);
        $this->authService->register($data);
        return response()->json(['message'=>'Registration successful. Please check your email to verify your account.'],201);

    }

    public function verifyEmail($token)
    {
        return $this->authService->verifyEmail($token);
    }

    public function login(Request $request)
    {
        $credentials=$request->validate([
            'email'=>'required|string|email',
            'password'=>'required|string',
        ]);
        try{
            $result=$this->authService->login($credentials);
            return response()->json($result,200);
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],401);
        }
    }

  public function logout(Request $request)
    {
        $user = $request->user();
        $this->authService->logout($user);
        return response()->json(['message'=>'Logged out successfully'],200);
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email',
        ]);
        try{
            $this->authService->forgotPassword($data['email']);
            return response()->json(['message'=>'Reset password email sent. Please check your inbox.'],200);
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],400);
        }
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email',
            'token' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);
        try{
            $this->authService->resetPassword($data['email'],$data['token'],$data['password']);
            return response()->json(['message'=>'Password reset successful. You can now log in with your new password.'],200);
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],400);
        }
    }
    public function changePassword(Request $request){
        $data=$request->validate([
            'current_password'=>'required|string',
            'password'=>'required|string|min:8|confirmed',
        ]);
        $user=$request->user();
        try{
            $this->authService->changePassword($user, $data['current_password'], $data['password']);
            return response()->json(['message'=>'Password changed successfully'],200);
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],400);
        }
    }
    
}
