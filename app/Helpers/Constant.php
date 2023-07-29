<?php

namespace App\Helpers;

class Constant
{
    const timezone = 'Asia/Karachi';
    const OTP_EXPIRE_TIME = 5;
    const LocalCountryCode = 92;

    const unSerializableFields = [];

    const M3TechSMSResponseCodes = [
        'success'   => '00',
    ];

    const USER_TYPES = [
        'Admin'       => 1,
        'Closet'      => 2,
        'Customer'    => 3,
    ];
    const USER_TYPES_STYLE = [
        1 => "primary",
        2 => "warning",
        3 => "success",
    ];

    const APP_JOURNEY = [
        'LOGIN' => 0,
        'ONBOARDING' => 1,
        'GUEST_USER' => 2,
    ];

    const CRUD_STATES = [
        'created' => 0,
        'updated' => 1
    ];

//    const USER_STATUS = [
//        0 => "In Active",
//        1 => "Active",
//    ];
    const USER_STATUS = [
        "InActive" => 0,
        "Active" => 1,
    ];
    const USER_STATUS_STYLE = [
        0 => "danger",
        1 => "success"
    ];
    const CUSTOMER_STATUS = [
        "InActive" => 0,
        "Active" => 1,
        "Blocked" => 2
    ];
    const CUSTOMER_STATUS_STYLE = [
        0 => "danger",
        1 => "success"
    ];
    const CUSTOMER_SUBSCRIPTION_STATUS = [
        "enabled" => 1,
        "disabled" => 2
    ];
    const CUSTOMER_SUBSCRIPTION_STATUS_STYLE = [
        2 => "danger",
        1 => "success"
    ];
    const OTP_MODULES = [
        'users'     => 'User',
        'customers' => 'Customer'
    ];

    const OTP_EVENTS = [
        'send' => 1,
        'resend' => 2
    ];

    const OTP_PROVIDERS = [
        'SMS' => 1,
        'EMAIL' => 2,
        'BOTH_EMAIL_AND_SMS' => 3,
    ];

    const OTP_MESSAGE_TEXT = [
        'login'        => 'is your verification OTP for bSecure',
    ];

    const DISCOUNT_TYPE = [
        'flat'       => 1,
        'percentage' => 2,
    ];
    const CUSTOMER_APP_PRODUCT_LISTING = [
        'FEATURED_PRODUCTS'           => 1,
        'CATEGORY_PRODUCTS'           => 2,
        'STORES_PRODUCTS'             => 3,
        'RECENTLY_VIEWED_PRODUCTS'    => 4
    ];

    const CUSTOMER_APP_STORE_LISTING_TYPE = [
        'Featured' => 1,
        'All' => 2,
        'Filters' => 3,
    ];

    const SORT_BY_FILTERS = [
        'featured' => 'Featured',
        'newest_arrival' => 'New Arrival',
        'price_high_to_low' => 'Price:High to Low',
        'price_low_to_high' => 'Price: Low to High'
    ];

    const SIZE_BY_FILTERS = [
        'x_small' => 'Extra Small',
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large',
        'x_large' => 'Extra Large'
    ];

    const COLORS_BY_FILTERS = [
        "red" => '#C70039',
        "black" => '#000000',
        "beige" => '#F5F5DC',
        "almond" => '#EADDCA',
        'white' => '#FFFFF0',
        'pink' => '#DE3163',
        'purple' => '#702963',
        'blue' => '#191970',
        'yellow' => '#FFBF00',
        'orange' => '#CC5500',
        'brown' => '#C2B280',
        'charcoal' => '#36454F',
        'pistachio' => '#93C572',
        'gold' => '#C4B454',
        'peach' => '#FFE5B4',
    ];

    const GeneralError = "messages.general.failed";
    const Yes = 1;
    const No = 0;
    const DEFAULT_VARIANT = 'Default Variant';

}
