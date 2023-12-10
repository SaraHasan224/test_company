<?php

namespace App\Models;

use App\Helpers\Constant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAppSession extends Model
{
    use SoftDeletes;

    protected $table = "customer_app_sessions";
    protected $guarded=[];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id',
        'last_activity_time',
        'customer_id',
        'ip',
        'location',
        'device_details',
        'otp_verified',
        'app_journey'
    ];

    public function customer()
    {
        return $this->belongsTo(Employee::class, 'customer_id', 'id');
    }

    public static function findRecentSessionByEmployeeId($customerId)
    {
        return self::where('customer_id',$customerId)->latest()->first();
    }

    public static function findByEmployeeId($customerId)
    {
        return self::where('customer_id',$customerId)
            ->first();
    }

    public static function findLastActiveSessionBySessionId($sessionId)
    {
        $session = self::where('session_id',$sessionId)->orderBy('id', 'DESC');
        if( $session->count() == 1 ) {
            return $session->first();
        }
        return $session->skip(1)->take(1)->first();
    }

    public static function findBySessionId($sessionId)
    {
        return self::where('session_id',$sessionId)
            ->first();
    }

    public static function findLatestBySessionId($sessionId)
    {
        return self::where('session_id',$sessionId)
            ->orderBy('id','DESC')
            ->first();
    }

    public static function updateLastActivityTime($sessionId)
    {
        $session = self::where('session_id',$sessionId)->first();
        if($session){
            $session->last_activity_time = Carbon::now();
            $session->save();
        }
    }

    public static function updateJourney($sessionId, $journeyId)
    {
        $session = self::where('session_id',$sessionId)->first();
        $session->app_journey = $journeyId;
        $session->save();
        return $session;
    }

    public static function updateOtpVerified($sessionId)
    {
        $session = self::where('session_id',$sessionId)->orderBy('id', 'DESC')->first();
        $journey = Constant::APP_JOURNEY['ONBOARDING'];
        if($session->customer->addresses->count() > 0 && !empty($session->customer->default_payment_method_id)){
            $journey = Constant::APP_JOURNEY['LOGIN'];
        }

        $session->otp_verified = Constant::Yes;
        $session->app_journey = $journey;
        $session->save();
        return $session;
    }

    public static function createSession( $sessionData, $refreshOldSession )
    {
        $ip = $sessionData['ip'];
        $iPDetails = $sessionData['ip_details'];
        $token = $sessionData['token'];
        $customer = $sessionData['customer'];
        $revokeOldToken = $sessionData['revokeOldToken'];
        $userAgent = $sessionData['user_agent'];

        $record= [
            'session_id' => $token->token->name,
            'last_activity_time' => Carbon::now(),
            'customer_id' => $customer->id,
            'ip' => $ip,
            'location' => json_encode($iPDetails),
            'device_details' => $userAgent,
            'app_journey' => Constant::APP_JOURNEY['GUEST_USER'],
            'otp_verified' => Constant::No,
        ];
        // Delete prev session
        $prevSession = self::findLastActiveSessionBySessionId( $record['session_id'] );
        if($refreshOldSession){
            $record['app_journey']  = Constant::APP_JOURNEY['LOGIN'];
            $record['otp_verified'] = Constant::Yes;
            self::where('session_id',$record['session_id'])->delete();
            self::create($record);
        }elseif($prevSession || $revokeOldToken){
            $record['app_journey']  = $refreshOldSession ? Constant::APP_JOURNEY['LOGIN'] : $prevSession->app_journey;
            $record['otp_verified'] = $refreshOldSession ? Constant::Yes : $prevSession->otp_verified;
            self::where('session_id',$record['session_id'])->delete();
            self::create($record);
        }else{
            self::create($record);
        }
    }
}
