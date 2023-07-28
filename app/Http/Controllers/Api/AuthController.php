<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Contact;
use DB;
use Auth;
use Hash;

class AuthController extends Controller
{
    protected function respondWithToken($token, $message = null)
    {
        try {
            return response()->json([
                'user' => auth()->user(),
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'message' => $message ?? 'Login successfully',
            ]);
        } catch (\Throwable $e) {
            return $this->returnError($this->error ?? $e->getMessage());
        }
    }

    public function register(Request $request)
    {

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

    public function reSendOtp(Request $request)
    {
        $user_id = auth()->user()->id;

        $otp = Contact::find($user_id);
        if (!$otp)
            return $this->returnError("User", 'User not found');

        if ($otp->canSend()) {
            $otp->generateCode();
            $otp->saveOrFail();
            return $this->returnSuccess('', 'OTP sent successfully');
        } else {
            return $this->returnError("OTP", "Maximum try reached. Please try after sometimes.");
        }
    }

    public function verifyOtp(Request $request)
    {

        $otp = $request->input('otp');
        if(!$otp)
            return $this->returnError("", 'OTP is required');
        $user_id = auth()->user()->id;
        $user = Contact::find($user_id);

        try {
            $user->validateOtp($otp);
            return $this->returnSuccess('', 'OTP verified successfully');
        } catch (\Throwable $e) {
            $user->saveOrFail();
            return $this->returnError($this->error ?? $e->getMessage());
        }
    }


    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->returnError($validator->errors());
            }

            $credentials = $request->only('email', 'password');
            if (!$token = auth()->attempt($credentials)) {
                return $this->returnError("", 'Email or password is invalid');
            }

            if(auth()->user()->is_mobile_verified == null)
                return $this->returnError("", 'Mobile number is not verified');

            return $this->respondWithToken($token);
        } catch (\Throwable $e) {
            return $this->returnError($this->error ?? $e->getMessage());
        }
    }

    public function me(): array
    {
        return $this->returnSuccess(auth()->user());
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required|string',
                'new_password' => 'required|string|min:6',
            ]);
            if ($validator->fails()) {
                return $this->returnError($validator->errors());
            }
            $user = Auth::user();
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->returnError('','Old password is incorrect');
            }
            $user->password = bcrypt($request->new_password);
            $user->save();
            return $this->returnSuccess([
                'id' => $user->id,
            ], 'Password changed successfully');
        } catch (\Throwable $e) {
            return $this->returnError($this->error ?? $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth()->logout();
            return $this->returnSuccess([], 'logout successfully');
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }


}
