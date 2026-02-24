<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendVerificationEmailJob;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetPasswordMail;
use App\Jobs\SendResetPasswordJob;
use App\Models\Shop;

class AuthService{
    public function register(array $data)
    {
        if($data['role']=='admin' && empty($data['shop_id'])){
            $shop=Shop::create([
                'name' => $data['name'].'\'s Shop',
            ]);
          $shopId=$shop->id;

        }else if($data['role']=='cashier'){
            $shopId=$data['shop_id'] ?? null;
            throw_if(empty($shopId), new \Exception('Shop ID is required for cashier role'));
        }
        $user=User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'cashier',
            'shop_id' => $shopId,
            'email_verification_token' => Str::random(60),
        ]);  
        
        // Mail::to($user->email)->send(new VerifyEmail($user));
        SendVerificationEmailJob::dispatch($user);
    }

    public function verifyEmail($token){
        $user = User::where('email_verification_token', $token)->firstOrFail();
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        return view('success');
    }

    public function login(array $credentials)
     {
        $user=User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Invalid credentials');
        }
        if(!$user->hasVerifiedEmail()){
            throw new \Exception('Email not verified');
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'user' => $user,
            'token' => $token
        ];
     }

     public function logout($user)
     {
        $user->tokens()->delete();
     }

     public function forgotPassword($email){
        $user = User::where('email', $email)->firstOrFail();
        $code=rand(100000,999999);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $code, 'created_at' => now()]
        );
        SendResetPasswordJob::dispatch($email, $code);

     }

     public function resetPassword($email,$code,$newPassword){
         $record=DB::table('password_reset_tokens')->where('email', $email)
         ->where('token', $code)
         ->first();
         if(!$record){
            throw new \Exception('Invalid code');
         }
         if(now()->diffInMinutes($record->created_at) > 15){
            throw new \Exception('Code expired');
         }
         $user=User::where('email', $email)->firstOrFail();
         $user->password=Hash::make($newPassword);
         $user->save();
         DB::table('password_reset_tokens')->where('email', $email)->delete();
     }

     public function changePassword($user, $currentPassword, $newPassword){
        if(!Hash::check($currentPassword, $user->password)){
            throw new \Exception('Current password is incorrect');
        }
        if($currentPassword === $newPassword){
            throw new \Exception('New password cannot be the same as current password');
        }
        $user->password=Hash::make($newPassword);
        $user->save();
        
     }
}