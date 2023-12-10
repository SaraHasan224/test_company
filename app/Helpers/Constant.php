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
        'Super-Admin' => 0,
        'Admin'       => 1,
        'Office'      => 2,
        'Employee'    => 3,
    ];
    const USER_TYPES_STYLE = [
        0 => "primary",
        1 => "info",
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

    const PIM_PRODUCT_STATUS = [
        "InActive" => 0,
        "Active" => 1,
    ];
    const PIM_PRODUCT_STATUS_STYLE = [
        0 => "danger",
        1 => "success"
    ];

    const PIM_PRODUCT_FEATURED_STATUS = [
        "Non_featured" => 0,
        "Featured" => 1,
    ];
    const PIM_PRODUCT_FEATURED_STATUS_STYLE = [
        0 => "secondary",
        1 => "primary"
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
    const COMPANY_STATUS = [
        "enabled" => 1,
        "disabled" => 2
    ];
    const COMPANY_STATUS_STYLE = [
        2 => "danger",
        1 => "success"
    ];
    const OFFICE_STATUS = [
        "enabled" => 1,
        "disabled" => 2
    ];
    const OFFICE_STATUS_STYLE = [
        2 => "danger",
        1 => "success"
    ];
    const EMPLOYEE_STATUS = [
        "enabled" => 1,
        "disabled" => 2
    ];
    const EMPLOYEE_STATUS_STYLE = [
        2 => "danger",
        1 => "primary"
    ];
    const OTP_MODULES = [
        'users'     => 'User',
        'employees' => 'Employee'
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
    const PJ_PRODUCT_LIST = [
        'FEATURED_PRODUCTS'           => 1,
        'CATEGORY_PRODUCTS'           => 2,
        'CLOSET_PRODUCTS'             => 3,
        'RECENTLY_VIEWED_PRODUCTS'    => 4,
        'CLOSET_TRENDING_PRODUCTS'    => 5,
        'CLOSET_CATEGORY_PRODUCTS'    => 6,
        'ALL_PRODUCTS'                => 7
    ];

    const PJ_ORDER_LIST = [
        'CLOSET_ORDERS'    => 1,
    ];


    const PJ_CLOSETS_LIST_TYPES = [
        'Trending' => 1,
        'All' => 2,
    ];

    const SORT_BY_FILTERS = [
        'featured' => 'Featured',
        'newest_arrival' => 'New Arrival',
        'price_high_to_low' => 'Price:High to Low',
        'price_low_to_high' => 'Price: Low to High'
    ];

    const CONDITION_BY_FILTERS = [
        'new' => 'New',
        'old' => 'Old',
    ];

    const SIZE_BY_FILTERS = [
        'x_small' => 'Extra Small',
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large',
        'x_large' => 'Extra Large'
    ];

    const STANDARD_BY_FILTERS = [
        'uk' => 'UK',
        'us' => 'US',
        'intl' => 'International',
    ];

    const COLORS_BY_FILTERS = [
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
        'IndianRed' => '#CD5C5C',
        'LightCoral' => '#F08080',
        'Salmon' => '#36454F',
        'DarkSalmon' => '#FA8072',
        'LightSalmon' => '#E9967A',
        'Crimson' => '#FFA07A',
        "red" => '#C70039',
        'FireBrick' => '#B22222',
        'DarkRed' => '#8B0000',
        'Pink' => '#FFC0CB',
        'LightPink' => '#FFB6C1',
        'HotPink' => '#FF69B4',
        'DeepPink' => '#FF1493',
        'MediumVioletRed' => '#C71585',
        'PaleVioletRed' => '#DB7093',
        'Coral' => '#FF7F50',
        'Tomato' => '#C4B454',
        'OrangeRed' => '#FF4500',
        'DarkOrange' => '#FF8C00',
        'Orange' => '#FFA500',
        'Gold' => '#FFD700',
        'Yellow' => '#FFFF00',
        'LightYellow' => '#FFFFE0',
        'LemonChiffon' => '#FFFACD',
        'LightGoldenrodYellow' => '#FAFAD2',
        'PapayaWhip' => '#FFEFD5',
        'Moccasin' => '#FFE4B5',
        'PeachPuff' => '#FFDAB9',
        'PaleGoldenrod' => '#EEE8AA',
        'Khaki' => '#F0E68C',
        'DarkKhaki' => '#BDB76B',
        'Lavender' => '#E6E6FA',
        'Thistle' => '#D8BFD8',
        'Plum' => '#DDA0DD',
        'Violet' => '#EE82EE',
        'Orchid' => '#DA70D6',
        'Fuchsia' => '#FF00FF',
        'Magenta' => '#FF00FF',
        'MediumOrchid' => '#BA55D3',
        'MediumPurple' => '#9370DB',
        'RebeccaPurple' => '#663399',
        'BlueViolet' => '#8A2BE2',
        'DarkViolet' => '#9400D3',
        'DarkOrchid' => '#9932CC',
        'DarkMagenta' => '#8B008B',
        'Purple' => '#800080',
        'Indigo' => '#4B0082',
        'SlateBlue' => '#6A5ACD',
        'DarkSlateBlue' => '#483D8B',
        'MediumSlateBlue' => '#7B68EE',
        'GreenYellow' => '#ADFF2F',
        'Chartreuse' => '#7FFF00',
        'LawnGreen' => '#7CFC00',
        'Lime' => '#00FF00',
        'LimeGreen' => '#32CD32',
        'PaleGreen' => '#98FB98',
        'LightGreen' => '#90EE90',
        'MediumSpringGreen' => '#00FA9A',
        'SpringGreen' => '#3CB371',
        'MediumSeaGreen' => '#FFE5B4',
        'SeaGreen' => '#2E8B57',
        'ForestGreen' => '#228B22',
        'Green' => '#008000',
        'DarkGreen' => '#006400',
        'YellowGreen' => '#9ACD32',
        'OliveDrab' => '#6B8E23',
        'Olive' => '#808000',
        'DarkOliveGreen' => '#556B2F',
        'MediumAquamarine' => '#66CDAA',
        'DarkSeaGreen' => '#8FBC8B',
        'LightSeaGreen' => '#20B2AA',
        'DarkCyan' => '#008B8B',
        'Teal' => '#008080',
        'Aqua' => '#00FFFF',
        'Cyan' => '#E0FFFF',
        'LightCyan' => '#93C572',
        'PaleTurquoise' => '#AFEEEE',
        'Aquamarine' => '#7FFFD4',
        'Turquoise' => '#40E0D0',
        'MediumTurquoise' => '#48D1CC',
        'DarkTurquoise' => '#00CED1',
        'CadetBlue' => '#5F9EA0',
        'SteelBlue' => '#4682B4',
        'LightSteelBlue' => '#B0C4DE',
        'PowderBlue' => '#B0E0E6',
        'LightBlue' => '#ADD8E6',
        'SkyBlue' => '#87CEEB',
        'LightSkyBlue' => '#87CEFA',
        'DeepSkyBlue' => '#00BFFF',
        'DodgerBlue' => '#1E90FF',
        'CornflowerBlue' => '#6495ED',
        'RoyalBlue' => '#4169E1',
        'Blue' => '#0000FF',
        'Navy' => '#000080',
        'MidnightBlue' => '#191970',
        'Cornsilk' => '#FFF8DC',
        'BlanchedAlmond' => '#FFEBCD',
        'Bisque' => '#FFE4C4',
        'NavajoWhite' => '#FFDEAD',
        'Wheat' => '#F5DEB3',
        'BurlyWood' => '#DEB887',
        'Tan' => '#D2B48C',
        'RosyBrown' => '#BC8F8F',
        'SandyBrown' => '#F4A460',
        'Goldenrod' => '#DAA520',
        'DarkGoldenrod' => '#B8860B',
        'Peru' => '#CD853F',
        'Chocolate' => '#D2691E',
        'SaddleBrown' => '#8B4513',
        'Sienna' => '#A0522D',
        'Brown' => '#A52A2A',
        'Maroon' => '#800000',
        'White' => '#FFFFFF',
        'Snow' => '#FFFAFA',
        'HoneyDew' => '#F0FFF0',
        'MintCream' => '#F5FFFA',
        'Azure' => '#F0FFFF',
        'AliceBlue' => '#F0F8FF',
        'GhostWhite' => '#F8F8FF',
        'WhiteSmoke' => '#F5F5F5',
        'SeaShell' => '#FFF5EE',
        'Beige' => '#F5F5DC',
        'OldLace' => '#FDF5E6',
        'FloralWhite' => '#FFFAF0',
        'Ivory' => '#FFFFF0',
        'AntiqueWhite' => '#FAEBD7',
        'Linen' => '#FAF0E6',
        'LavenderBlush' => '#FFF0F5',
        'MistyRose' => '#FFE4E1',
        'Gainsboro' => '#DCDCDC',
        'LightGray' => '#D3D3D3',
        'Silver' => '#C0C0C0',
        'DarkGray' => '#A9A9A9',
        'Gray' => '#808080',
        'DimGray' => '#696969',
        'LightSlateGray' => '#778899',
        'SlateGray' => '#708090',
        'DarkSlateGray' => '#2F4F4F',
        'Black' => '#000000'
    ];

    const GeneralError = "messages.general.failed";
    const Yes = 1;
    const No = 0;
    const DEFAULT_VARIANT = 'Default Variant';

}
