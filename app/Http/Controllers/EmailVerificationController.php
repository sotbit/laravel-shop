<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\User;
use Cache;
use Illuminate\Http\Request;
use App\Notifications\EmailVerificationNotification;
use Mail;

class EmailVerificationController extends Controller
{
    
    public function verify(Request $request)
    {
        
        $email = $request->input('email');
        $token = $request->input('token');
        
        if (!$email || !$token) {
            throw new InvalidRequestException('Verification link is incorrect');
        }
        
        
        if ($token != Cache::get('email_verification_'.$email)) {
            throw new InvalidRequestException('Verification link is incorrect or has expired');
        }
        
        
        
        
        if (!$user = User::where('email', $email)->first()) {
            throw new InvalidRequestException('User does not exist');
        }
        
        Cache::forget('email_verification_'.$email);
        
        $user->update(['email_verified' => true]);
        
        
        return view('pages.success', ['msg' => 'Email verification successful']);
    }
    
    
    public function send(Request $request)
    {
        $user = $request->user();
        
        if ($user->email_verified) {
            throw new InvalidRequestException('You have verified your email');
        }
        
        $user->notify(new EmailVerificationNotification());
        
        return view('pages.success', ['msg' => 'Mail sent successfully']);
    }
}