<?php

namespace App\Models;


use App\Helpers\Helper;
use App\Helpers\SmsHandler;
use Carbon\Carbon;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

use App\Helpers\ApiResponseHandler;
use App\Helpers\AppException;
use App\Helpers\Constant;
use App\Helpers\Http;

use Jenssegers\Agent\Agent;

class Otp extends Model
{
//    protected $fillable = [
//        'model',
//        'model_id',
//        'email_otp',
//        'phone_otp', 'action',
//    ];

    protected $guarded = [];

    public static $validationRules = [
        'send' => [
            'country_code' => 'required|string',
            'phone_number' => 'required|string',
        ],
        'verify' => [
            'otp' => 'required|min:6|max:6'
        ],
    ];

    public static function getOtpByUserId($action, $userId)
    {
        $expireTime = Carbon::now()->addMinute(Constant::OTP_EXPIRE_TIME);
        return self::where('model_id', $userId)
            ->where('action', $action)
            ->where('is_verified', Constant::No)
            ->where('is_used', Constant::No)
            ->where('created_at', '<=', $expireTime)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public static function getOtpByReferenceId($request_id)
    {
        return self::where('model', Constant::OTP_MODULES['employees'])
            ->where('reference_id', $request_id)
            ->where('is_verified', Constant::No)
            ->where('is_used', Constant::No)
            //->where('expire_at', '>', NOW())
            ->first();
    }

    public static function storeOtpInSession($emailOtp, $phoneOtp)
    {
        Session::put('email_otp', $emailOtp);
        Session::put('phone_otp', $phoneOtp);
        Session::put('opt_created_at', Carbon::now());
    }

    public static function getLastOtpSentToEmployee($customer_id, $identifier, $callFrom)
    {
        return self::where('model', Constant::OTP_MODULES['employees'])
            ->where('order_ref', $identifier)
            ->where('model_id', $customer_id)->latest('created_at')->first();

    }

    public static function createOtp($otpData, $request)
    {
        $allowEmailOtp = false;
        $allowSmsOtp = true;
        $otp = new self;

        if ($otpData['action'] == Constant::OTP_EVENTS['resend'] && isset($otpData['phone_otp'])) {
            $otp_encrypt = decrypt($otpData['phone_otp']);
        } else {
            $otp_encrypt = Helper::randomDigits();
        }

        $phoneNumber = $otpData['phone_number'];

        $timerVal = Constant::OTP_EXPIRE_TIME;
        $expireTime = Carbon::now()->addSeconds($timerVal);

        $agent = new Agent();
        $data = [
            'model' => Constant::OTP_MODULES['employees'],
            'model_id' => $otpData['customer_id'],
            'reference_id' => $otpData['identifier'],
            'action' => $otpData['action'],
            'phone_number' => $phoneNumber,
            'country_code' => $otpData['country_code'],
            'email_otp' => encrypt($otp_encrypt),
            'phone_otp' => encrypt($otp_encrypt),
            'expire_at' => $expireTime,
            'user_agent' => $agent->getUserAgent(),
            'email' => null,
        ];

        if (!array_key_exists('otp_provider', $data)) {
            if ($allowEmailOtp) {
                $data['otp_provider'] = Constant::OTP_PROVIDERS['EMAIL'];
            } elseif ($allowSmsOtp) {
                $data['otp_provider'] = Constant::OTP_PROVIDERS['SMS'];
            }
        }
        $ip = Helper::getUserIP($request);
        $data['ip'] = $ip;
        $otp->fill($data);
        if ($otp->save()) {
            if (env('ENABLE_SMS')) {
//               SmsHandler::otpSms($otp, $otpData, $otp_encrypt, $appType);
            }
        }
        return $otp;
    }

    public function markUserAttemptVerified()
    {
        $this->update([
            'is_verified' => Constant::Yes,
            'verified_at' => Carbon::now(),
        ]);
    }

    public static function revokeOldOtpForEmployee($model_id, $model, $ref)
    {
        self::where('model', $model)
            ->where('model_id', $model_id)
            ->where('is_verified', Constant::No)
            ->where(function ($query) use ($ref) {
                $query->where('reference_id', $ref);
            })->update(['is_used' => 1]);
    }

    public static function verifyEmployeeOtp($identifier, $phoneOtp)
    {
        $userOtp = Otp::getOtpByReferenceId($identifier);
        return self::__verify($userOtp, $phoneOtp);
    }

    private static function __verify($userOtp, $phoneOtp)
    {
        if ($userOtp) {
            if (!env('ENABLE_SMS')) {
                if ($phoneOtp == env('GeneralOTP')) {
                    $userOtp->markUserAttemptVerified();
                    return $userOtp;
                }
            } else {
                if (Helper::matchOtp($phoneOtp, $userOtp->phone_otp)) {
                    $userOtp->markUserAttemptVerified();
                    return $userOtp;
                }
            }
        }

        return false;
    }


    public static function allowOtpReSend($customer_id, $identifier)
    {
        $query = self::where('model_id', $customer_id)
            ->where('model', Constant::OTP_MODULES['employees'])
            ->where('is_verified', Constant::No)
            ->where('reference_id', $identifier);
        $attempts = $query->count();

        $max_tries = env('MAX_OTP_TRIES') ?? 3;

        return $attempts <= $max_tries;
    }

}
