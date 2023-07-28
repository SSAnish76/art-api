<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Sanctum\HasApiTokens;

class Contact extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory;


    protected $fillable = [
        'full_name', 'mobile', 'email', 'password', 'type', 'about_your_self', 'is_resident_india', 'pan_no', 'aadhaar_no', 'gst_no', 'cin_no', 'cv'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    protected $hidden = ['password', 'otp', 'send_on', 'expires_on', 'last_try', 'retry_count'];

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function canSend()
    {
        if (!$this->send_on) {
            return true;
        }

        $lastTry = strtotime($this->send_on);

        if (($lastTry + (1.5 * 60)) > time()) {
            return false;
        }

        return true;
    }

    public function generateCode()
    {
        $this->otp = rand(100000, 999999);
        $this->send_on = date("Y-m-d H:i:s", time());
        $this->expires_on = date("Y-m-d H:i:s", time() + (10 * 60));
        $this->last_try = null;
        $this->retry_count = 0;
    }

    public function validateOtp($code)
    {
        if ($this->isExpired()) {
            throw new \Exception("Otp Expired");
        }

        if ($this->retry_count > 5) {
            $lastTry = strtotime($this->last_try);

            if (($lastTry + (15 * 60)) > time()) {
                throw new \Exception("Maximum retry count reached. Please try again after some time.");
            }

            $this->retry_count = -1;
        }

        if ($this->otp != $code) {
            $this->last_try = date("Y-m-d H:i:s", time());
            $this->retry_count++;
            throw new \Exception("Invalid Otp.");
        }

        $this->is_mobile_verified = date("Y-m-d H:i:s", time());

        return true;
    }

    public function isExpired()
    {
        $expiresOn = strtotime($this->expires_on);

        if ($expiresOn > time()) {
            return false;
        }

        return true;
    }


}
