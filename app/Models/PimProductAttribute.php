<?php

namespace App\Models;

use App\Helpers\Constant;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;

class PimProductAttribute extends Model
{
    protected $guarded = [];
    protected $table = 'pim_product_attributes';

    public function product()
    {
        return $this->hasOne(PimProduct::class, 'product_id');
    }

    public function attributeOptions()
    {
        return $this->hasMany(PimProductVariantOption::class, 'attribute_id', 'attribute_id');
    }

    public function attribute()
    {
        return $this->belongsTo(PimAttribute::class, 'attribute_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(PimProductAttributeOption::class, 'pim_product_attribute_id', 'id');
    }

    public static function saveAttribute($office, $pimProduct, $attribute, $attributeName)
    {
        $pimAttribute =  self::firstOrCreate([
            'office_id' => $office->id,
            'product_id' => $pimProduct->id,
            'attribute_id' => $attribute->id,
            'attribute_value' => $attributeName,
        ]);

        return $pimAttribute;
    }

    public static function mapProductAttribute($productId, $attributeId, $attributeValue)
    {
        return self::updateOrCreate([
          'product_id' => $productId,
          'attribute_id' => $attributeId
        ],
          [
            'attribute_value' => $attributeValue
          ])->id;
    }

}
