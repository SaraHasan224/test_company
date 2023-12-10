<?php

namespace App\Models;

// use Illuminate\Contracts\auth\MustVerifyEmail;
use App\Helpers\Constant;
use App\Helpers\Helper;
use App\Helpers\Http;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use phpseclib3\System\SSH\Agent;
use function Ramsey\Uuid\v4; // include this

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'country_code',
        'phone_number',
        'country_id',
        'status',
        'identifier',
        'last_login',
        'login_attempts',
        'email_verified_at',
        'phone_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function office()
    {
        return $this->belongsTo(Office::class, 'id', 'customer_id');
    }

    public function country()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    public static $validationRules = [
        'register' => [
            'country' => 'required|string',
            'email_address' => 'required|email|email:rfc,dns|unique:employees,email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ],
        'login' => [
            'email_address' => 'required|email|email:rfc,dns',
            'password' => 'required|string',
        ],
        'create-office' => [
            'name' => 'required|string|max:255',
            'logo' => 'required',
            'banner' => 'required'
        ],
    ];

    public static function getValidationRules($type, $params = [])
    {
        $rules = [
            'create'        => [
                'first_name'  => 'required',
                'last_name'  => 'required',
                'email' => 'required|email:rfc,dns|unique:employees,email',
                'phone' => [
                    'required',
//                    'regex:/^[0-9]+$/'
                ],
                'username' => 'required',
            ],
            'update'        => [
                'name'     => 'required',
                'email'    => 'required|email:rfc,dns',//|unique:users,email,' . $user_id,
                'phone'    => [
                    'required',
                    'string',
                    'regex:/^[0-9]+$/'
                ],
            ],
            'update-office' => [
                'name' => 'required|string|max:255',
            ]
        ];

        return $rules[$type];
    }

    public function getPhoneNumberInternationalFormat()
    {
        return "+{$this->country_code}{$this->phone_number}";
    }

    public function getPhoneNumberHiddenInternationalFormat()
    {
        $phone = Helper::obfuscateString($this->phone_number);
        return "+({$this->country_code}){$phone}";
    }

    public static function findById($id){
        return self::where('id', $id)->first();
    }

    public static function findByEmail($email){
        return self::where('email', $email)->first();
    }

    public static function findByRef($ref){
        return self::where('identifier', $ref)->first();
    }

    public static function findByPhoneNumber($code, $phone)
    {
        return self::where('country_code',$code)
            ->where('phone_number',$phone)
            ->first();
    }

    public static function findNonAnonymousEmployeeByOtp( $otp )
    {
        return self::where('country_code', $otp->country_code)
            ->where('phone_number', $otp->phone_number)
            ->whereNull('deleted_at')
            ->first();
    }


    public static function removeEmployee( $customer_id )
    {
        self::where('id', $customer_id )->delete();
    }

    public function updateNonVerifiedEmployee( $verifiedOtp )
    {
        $updateCols = [
            'is_verified' => Constant::Yes,
            'country_code' => $verifiedOtp->country_code,
            'phone_number' => $verifiedOtp->phone_number,
            'phone_verified_at' => Now(),
        ];
        $this->update($updateCols);
    }

    public static function createEmployee( $requestData, $identifier )
    {
        $emptyString = "";

        $data = [
            'first_name'            => $requestData['first_name'],
            'last_name'             => $requestData['last_name'],
            'email'                 => $requestData['email_address'],
            'country_code'          => array_key_exists("country_code", $requestData) ? $requestData['country_code'] : $emptyString,
            'phone_number'          => array_key_exists("phone_number", $requestData) ? $requestData['phone_number'] : $emptyString,
            'country_id'            => $requestData['country_id'],
            'password'              => $requestData['password'],
            'status'                => Constant::CUSTOMER_STATUS['Active'],
            'subscription_status'   => $requestData['subscription_status'] ?? 0,
            'identifier'            => $identifier,
        ];

        return self::create($data);
    }

    public static function getByFilters($filter)
    {
        $data = self::select('id', 'first_name', 'last_name', 'username', 'email', 'email_verified_at', 'country_code', 'phone_number', 'phone_verified_at', 'country_id', 'identifier', 'last_login', 'status',  'subscription_status', 'created_at','updated_at','deleted_at');
        $data = $data->orderBy('id', 'DESC');

        if (count($filter)) {
            if (!empty($filter['first_name']) || !empty($filter['last_name']))
            {
                $data = $data->where('first_name', 'LIKE', '%' . trim($filter['first_name']) . '%')
                             ->orWhere('last_name', 'LIKE', '%' . trim($filter['last_name']) . '%');
            }

            if (!empty($filter['user_name']))
            {
                $data = $data->where('username', 'LIKE', '%' . trim($filter['user_name']) . '%');
            }

            if (!empty($filter['phone']))
            {
                $phone = trim($filter['phone']);
                $phone = Helper::formatPhoneNumber($phone);
                $data = $data->where('phone', 'LIKE', '%' . $phone . '%');
            }

            if (!empty($filter['email']))
            {
                $data = $data->where('email', 'LIKE', '%' . trim($filter['email']) . '%');
            }

            if (!empty($filter['last_login']))
            {
                $memberSince = trim($filter['last_login']);
                $data = $data->whereDate('last_login', '>=', date('Y-m-d', strtotime($memberSince)));
            }

            if (isset($filter['status']))
            {
                $data = $data->where('status', $filter['status']);
            }

            if (isset($filter['subscription_status']))
            {
                $data = $data->where('subscription_status', $filter['subscription_status']);
            }
        }

        $count = $data->count();
//        if (isset($filter['start']) && isset($filter['length']))
//        {
//            $data->skip($filter['start'])->limit($filter['length']);
//        }

        return [
            'count'   => $count,
            'offset'  => isset($filter['start']) ? $filter['start'] : 0,
            'records' => $data->get()
        ];
    }


    public static function getOfficesEmployees($officeId) {
        $data = Employee::select(
            'id',
            'first_name',
            'last_name',
            'username',
            'email',
            'country_code',
            'phone_number',
            'country_id',
            'identifier',
            'status',
            'created_at',
            'updated_at'
        )->where('office_id', $officeId);

        $data = $data->orderBy('id', 'DESC');

        $count = $data->count();

        return [
            'count'   => $count,
            'offset'  => 0,
            'records' => $data->get()
        ];
    }
}
