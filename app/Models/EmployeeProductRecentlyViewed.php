<?php

namespace App\Models;

use App\Helpers\Constant;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProductRecentlyViewed extends Model
{
//    use SoftDeletes;

    protected $table = "customer_product_recently_viewed";
    protected $guarded = [];

    public static function viewProduct($requestData)
    {
        return self::updateOrCreate([
            'customer_id'     => $requestData['customer_id'],
            'product_id'      => $requestData['product_id'],
        ], [
            'referrer_type'   => array_key_exists('referrer_type', $requestData) ? $requestData['referrer_type'] : Constant::PJ_PRODUCT_LIST['FEATURED_PRODUCTS'],
            'viewed_at'       => Carbon::now()
        ]);
    }

    public static function findByEmployeeId($customerId)
    {
        return self::where('customer_id',$customerId)->get();
    }
    public static function findCountByOfficeProductIds($productIds)
    {
        return self::whereIn('product_id',$productIds)->count();
    }


    public static function getRecentlyViewedResultsForEmployee($customerId)
    {
        return self::where('customer_id',$customerId)->orderBy('viewed_at','ASC')->latest()->take(5)->pluck('product_id')->toArray();
    }

    public static function updateRecentlyViewedEmployeeId( $oldEmployeeId, $newEmployeeId )
    {
        $productIds = self::findByEmployeeId($newEmployeeId)->pluck('product_id')->toArray();
        self::where('customer_id', $oldEmployeeId)->whereIn('product_id', $productIds)->delete();
        self::where('customer_id', $oldEmployeeId)->update(['customer_id' => $newEmployeeId]);
    }
}