<?php

namespace App\Models;

use App\Helpers\Constant;
use App\Helpers\Helper;
use Faker\Core\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PimProductImage extends Model
{
    protected $guarded = [];

    public function getUrlAttribute($value)
    {
        $filePath = asset($value);
        return $filePath;
    }

    public static function getPlaceholder(){
        return self::where('product_id', Constant::No)->first();
    }

    public static function saveImage($imageData)
    {
        return self::updateOrCreate(
          [
            'product_id'    => $imageData['product_id'],
            'imported_image_id' => $imageData['image_id']
          ],
          [
            'url'           => $imageData['url'],
            'alt'           => $imageData['alt'],
            'position'      => $imageData['position'],
            'is_imported'   => Constant::Yes,
            'is_default'    => $imageData['is_default']
          ]
        );
    }

    public static function deletePimImages( $productId, $imageIds = [] )
    {
        return self::where('product_id', $productId)
          ->whereNotIn('imported_image_id', $imageIds)
          ->delete();
    }

    public static function getImageByImportedId($productId, $importedImageId)
    {
        return self::select('id')
          ->where('product_id', $productId)
          ->where('imported_image_id', $importedImageId)
          ->first();
    }

    public static function getDefaultProductImage($productId){
        $defaultImage = self::where('product_id', $productId)->where('is_default', Constant::Yes)->first();
        if($defaultImage){
            return $defaultImage->id;
        }
        return self::getPlaceholder()->id;
    }
}
