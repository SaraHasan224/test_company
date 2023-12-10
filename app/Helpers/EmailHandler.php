<?php

namespace App\Helpers;
use App\Mail\AgencyApproval;
use App\Mail\EmptyWalletReminder;
use App\Mail\MerchantInvoice;
use App\Mail\ZoodpayCsvExport;
use App\Models\Agencies;
use App\Models\Employee;
use PDF;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\UserCreated;
use App\Mail\NewMerchant;
use App\Mail\UserBlocked;
use App\Mail\ResetPassword;
use App\Mail\TopUpReminder;
use App\Mail\NewUserCreated;
use App\Models\UserPasswords;
use App\Mail\AdminUserBlocked;
use App\Mail\ChangeEmailAddress;
use App\Mail\WalletLimitReached;
use App\Mail\AdminNewUserCreated;
use App\Mail\WalletTopUpComplete;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\WalletWithdrawalComplete;
use App\Mail\WalletWithdrawalRequested;
use App\Mail\MerchantActivationCompleted;
use App\Mail\MerchantActivationSubmitted;

class EmailHandler
{

    const EMAIL_SUBJECTS = [
        'signin' => [
            'Admin' => 'Your Sign in OTP!',
        ],

    ];

    const EMAIL_EVENTS = [
        'signin'        => 'Sign in',
    ];

    public static function getActionLinks()
    {
        return [
            'support' => env('SUPPORT_LINK'),
            'contact' => env('CONTACT_LINK'),
            'view_in_browser' => env('EMAIL_BROWSER_VIEW_LINK'),
            'privacy_policy' => env('PRIVACY_POLICY_LINK'),
            'unsubscribe' => env('UNSUBSCRIBE_LINK'),
        ];
    }

    public static function getSocialLinks()
    {
        return [
            'facebook_link' => env('SOCIAL_FACEBOOK_LINK'),
            'twitter_link' => env('SOCIAL_TWITTER_LINK'),
            'linkedin_link' => env('SOCIAL_LINKEDIN_LINK'),
        ];
    }


    public static function merchantSignin_upEmail($user, $otp, $event)
    {
        try {

            $roleType = array_keys(Constant::USER_TYPES, $user->roles[0]->type_id);
            $user_role = implode(" ", $roleType);
            $portal_link = ($user->roles[0]->type_id == Constant::USER_TYPES['Admin']) ? env('ADMIN_PORTAL_LINK') : env('MERCHANT_PORTAL_LINK');
            $to_name = $user->name;
            $to_email = $user->email;
            $title = "Let's get you ".' '.EmailHandler::EMAIL_EVENTS[$event];
            if ($event == 'signin'){
                $title = "Let’s get you signed in";
            }
            $data = [
                'subject' => EmailHandler::EMAIL_SUBJECTS[$event][$user_role],
                'title' => $title,
                'event' => EmailHandler::EMAIL_EVENTS[$event],
                'portal_title' => $user_role . ' Portal',
                'portal_link' => $portal_link,
                'merchant_name' => $to_name,
                'username' => $to_name,
                'otp_code' => $otp->non_hashed_email_otp,
                'links' => EmailHandler::getActionLinks(),
                'social' => EmailHandler::getSocialLinks(),
                'header_icon' => Helper::getAssetPath('otp-top-img.png'),
                'header_description' => "You are nearly there! Kindly enter your one time password (OTP) to complete your bSecure ". strtolower(EmailHandler::EMAIL_EVENTS[$event])."."
            ];
            Mail::to($to_email, $to_name)->send(new NewMerchant($data));
        } catch (\Exception $e) {
            AppException::log($e);
            return ApiResponseHandler::serverError($e);
        }
    }

    public static function otpResendEmail($user, $otp, $event, $email= null)
    {
        try {
            $roleType = array_keys(Constant::USER_TYPES, $user->roles[0]->type_id);
            $user_role = implode(" ", $roleType);
            $portal_link = ($user->roles[0]->type_id == Constant::USER_TYPES['Admin']) ? env('ADMIN_PORTAL_LINK') : env('WEBSITE_LINK');
            $to_name = $user->name;
            $to_email = $user->email;
            if($email){
                $to_email = $email;
            }
            $title = "Let's get you ".' '.EmailHandler::EMAIL_EVENTS[$event];
            if ($event == 'signin'){
                $title = "Let’s get you signed in";
            }
            $data = [
                'subject' => EmailHandler::EMAIL_SUBJECTS['otp_retry'][$event][$user_role],
                'title' => $title,
                'event' => EmailHandler::EMAIL_EVENTS[$event],
                'portal_title' => $user_role . ' Portal',
                'portal_link' => $portal_link,
                'username' => $to_name,
                'merchant_name' => $to_name,
                'otp_code' => $otp->non_hashed_email_otp,
                'links' => EmailHandler::getActionLinks(),
                'social' => EmailHandler::getSocialLinks(),
                'header_icon' => Helper::getAssetPath('otp-top-img.png'),
                'header_description' => "You are nearly there! Kindly enter your one time password (OTP) to complete your bSecure ". strtolower(EmailHandler::EMAIL_EVENTS[$event])."."
            ];
            Mail::to($to_email, $to_name)->send(new NewMerchant($data));
        } catch (\Exception $e) {
            AppException::log($e);
            return ApiResponseHandler::serverError($e);
        }
    }

    public static function createUser($user)
    {
        try {

            $roleType = array_keys(Constant::USER_TYPES, $user->roles[0]->type_id);
            $user_role = implode(" ", $roleType);

            $portal_link = ($user->roles[0]->type_id == Constant::USER_TYPES['Admin']) ? config('app.ADMIN_PORTAL_LINK') : config('app.WEBSITE_LINK');
            $to_name = $user->name;
            $to_email = $user->email;
            //Check user admin/merchant admin

            if ($user->roles[0]->type_id == Constant::USER_TYPES['Admin']) {
                //Then send email to super-admin : condition paused

                // Then send email to user whose created this account
                $from_name = Auth::user()->name;
                $from_email = Auth::user()->email;
            } else {

                //Then send email to merchant-admin : condition paused
                //Then send email to who created this account
                $from_name = Auth::user()->name;
                $from_email = Auth::user()->email;

            }

            $converted_time = Helper::convertTime($user->created_at);

            $password_reset_link = UserPasswords::generateResetPasswordLink($to_email);

            $admin_data = [
                'subject' => EmailHandler::EMAIL_SUBJECTS['create_user']['admin'][$user_role],
                'title' => EmailHandler::EMAIL_SUBJECTS['create_user']['admin'][$user_role],
                'portal_title' => $user_role . ' Portal',
                'portal_link' => $portal_link,
                'merchant_name' => $from_name,
                'user_created_time' => $converted_time['time'],
                'user_created_date' => $converted_time['date'],
                'user_name' => $to_name,
                'user_email' => $to_email,
                'user_role' => str_replace(config('permission.merchant_prefix') . (Auth::user()->merchant_id ?? 0), '', $user->roles[0]->name),
                'unauthorized_user_created' => $portal_link.'users/'.$user->id.'/edit',
                'links' => EmailHandler::getActionLinks(),
                'social' => EmailHandler::getSocialLinks(),
            ];



            $user_data = [
                'subject' => EmailHandler::EMAIL_SUBJECTS['create_user']['user'][$user_role],
                'title' => EmailHandler::EMAIL_SUBJECTS['create_user']['user'][$user_role],
                'portal_title' => $user_role . ' portal',
                'portal_link' => $portal_link,
                'merchant_name' => $to_name,
                'administrator_email' => $from_email,
                'login_link' => $portal_link,
                'user_name' => $to_name,
                'user_email' => $to_email,
                'user_role' =>  str_replace(config('permission.merchant_prefix') . (Auth::user()->merchant_id ?? 0), '', $user->roles[0]->name),
                'links' => EmailHandler::getActionLinks(),
                'social' => EmailHandler::getSocialLinks(),
                'password_reset_link' => $portal_link. $password_reset_link,
            ];


            // send email to user whose account has been created this account.
            Mail::to($to_email, $to_name)->send(new NewUserCreated($user_data));
            // send email to admin that user account has been created.
            Mail::to($from_email, $from_name)->send(new AdminNewUserCreated($admin_data));
        } catch (\Exception $e) {
            AppException::log($e);
            return ApiResponseHandler::serverError($e);
        }
    }

    public static function accountActivationCompleted($merchant)
    {
        try {
            $user = $merchant->user;

            $tutorial_link = env('CUSTOMER_EXPERIENCE_TUTORIAL_LINK');
            $to_name = $user->name;
            $to_email = $user->email;

            //Check if user is an admin/merchant admin

            // If Admin send email that a new account is registered : condition paused
            // If Merchant send email that you have successfully activated your merchant account
            $data = [
                'subject' => EmailHandler::EMAIL_SUBJECTS['activation_completed'],
                'title' => EmailHandler::EMAIL_SUBJECTS['activation_completed'],
                'portal_title' => 'Merchant Portal',
                'portal_link' => env('WEBSITE_LINK'),
                'tutorial_link' => $tutorial_link,
                'merchant_name' => $to_name,
                'links' => EmailHandler::getActionLinks(),
                'social' => EmailHandler::getSocialLinks(),
            ];
            Mail::to($to_email, $to_name)->send(new MerchantActivationCompleted($data));
        } catch (\Exception $e) {
            AppException::log($e);
            return ApiResponseHandler::serverError($e);
        }
    }

    public static function resetPasswordEmail($resetPasswordLink, $user){
//        $userRoleTypes = array_flip(Constant::USER_TYPES);
//        $roleType = $userRoleTypes[User::userRoleType($user)];
        try{
            $roleType = array_keys(Constant::USER_TYPES, $user->roles[0]->type_id);
            $userRole = implode(" ", $roleType);
            $portalLink = Helper::getPortalLink($user->roles[0]->type_id);
            $toName = $user->name;
            $toEmail = $user->email;

            $reset_password_data = [
                'subject'             => EmailHandler::EMAIL_SUBJECTS['reset_password'],
                'title'               => EmailHandler::EMAIL_SUBJECTS['reset_password'],
                'portal_title'        => $userRole . ' portal','merchant_name' => $toName,
                'portal_link'         => $portalLink,
                'user_name'           => $toName,
                'user_email'          => $toEmail,
                'user_role'           => $user->roles[0]->name,
                'password_reset_link' => $portalLink. $resetPasswordLink,
                'links'               => EmailHandler::getActionLinks(),
                'social'              => EmailHandler::getSocialLinks(),
            ];

            Mail::to($toEmail)->send(new ResetPassword($reset_password_data));
        } catch (\Exception $e) {
            AppException::log($e);
            return ApiResponseHandler::serverError($e);
        }

    }
    public static function userBlockEmail($email) {
        try{
            $user = User::getUserByEmail($email);
            if($user){
                $roleType = array_keys(Constant::USER_TYPES, $user->roles[0]->type_id);
                $user_role = implode(" ", $roleType);
                $portal_link = ($user->roles[0]->type_id == Constant::USER_TYPES['Admin']) ? config('app.ADMIN_PORTAL_LINK') : config('app.WEBSITE_LINK');
                $to_name = $user['name'];
                $to_email = $user['email'];
                //Check user admin/merchant admin
                $merchantAdmin = User::getMerchantAdmin($user);

                $admin_data = [
                    'subject' => $merchantAdmin['subject'],
                    'title' => $merchantAdmin['subject'],
                    'portal_title' => $user_role . ' Portal',
                    'portal_link' => $portal_link,
                    'merchant_name' => $merchantAdmin['name'],
                    'user_name' => $to_name,
                    'user_email' => $to_email,
                    'user_role' => $user->roles[0]->name,
                    'unauthorized_user_created' => $merchantAdmin['unauthorized_user_created'],
                    'links' => EmailHandler::getActionLinks(),
                    'social' => EmailHandler::getSocialLinks(),
                ];

                $user_data = $admin_data;
                $user_data['merchant_name'] = $to_name;
                $user_data['administrator_email'] = $merchantAdmin['email'];
                $user_data['login_link'] = $portal_link;

//                dd($admin_data, $user_data);


                // send email to user whose account has been created this account.
                Mail::to($to_email, $to_name)->send(new UserBlocked($user_data));
                // send email to admin that user account has been created.
                Mail::to($merchantAdmin['email'], $merchantAdmin['name'])->send(new AdminUserBlocked($admin_data));
            }
        }catch (\Exception $e){
            AppException::log($e);
            return ApiResponseHandler::serverError($e);
        }
    }

    public static function changeEmailOrPassword($user, $newEmail= null, $otp, $event){
        try {
            $roleType = array_keys(Constant::USER_TYPES, $user->roles[0]->type_id);
            $user_role = implode(" ", $roleType);
            $portal_link = ($user->roles[0]->type_id == Constant::USER_TYPES['Admin']) ? env('ADMIN_PORTAL_LINK') : env('WEBSITE_LINK');
            $to_name = $user->name;
            $to_email = $user->email;
            if($newEmail){
                $to_email = $newEmail;
            }
            $data = [
                'subject' => EmailHandler::EMAIL_SUBJECTS[$event][$user_role],
                'title' => $user_role.' '.EmailHandler::EMAIL_EVENTS[$event],
                'event' => EmailHandler::EMAIL_EVENTS[$event],
                'portal_title' => $user_role . ' Portal',
                'portal_link' => $portal_link,
                'merchant_name' => $to_name,
                'username' => $to_name,
                'otp_code' => $otp->non_hashed_email_otp,
                'links' => EmailHandler::getActionLinks(),
                'social' => EmailHandler::getSocialLinks(),
            ];
            Mail::to($to_email, $to_name)->send(new ChangeEmailAddress($data));
        } catch (\Exception $e) {
            AppException::log($e);
            return ApiResponseHandler::serverError($e);
        }

    }
}
