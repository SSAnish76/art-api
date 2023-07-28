<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Contact;


class OnboardController extends Controller
{

    public function onboard(Request $request) {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:artist,business,collector',
            'full_name' => 'required|string',
            'mobile' => 'required',
            'email' => 'required|email|unique:contacts,email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
            return $this->returnError($validator->errors());
        try {
            $user = new Contact();
            $user->type = $request->input('type');
            $user->full_name = $request->input('full_name');
            $user->email = $request->input('email');
            $user->mobile = $request->input('mobile');
            $user->password = bcrypt($request->input('password'));
            $user->generateCode();
            $user->save();

            $credentials = $request->only('email', 'password');
            if (!$token = auth()->attempt($credentials)) {
                return $this->returnError("Email or password is invalid");
            }
            return $this->respondWithToken($token, 'OTP sent successfully');

        } catch (\Throwable $e) {
            return $this->returnError($this->error ?? $e->getMessage());
        }
    }

}
