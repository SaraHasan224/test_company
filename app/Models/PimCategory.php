<?php

namespace App\Models;

use App\Helpers\Constant;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use function Ramsey\Uuid\v4;

class PimCategory extends Model
{
    protected $guarded = [];

    public function parentCategory()
    {
        return $this->belongsTo(PimCategory::class, 'parent_id', 'id');
    }

    public function parentBSCategory()
    {
        return $this->hasOne(PimBsCategory::class, 'slug', 'pim_cat_reference');
    }

    public static function addParentPimCategory($office, $name){
        $bsCategory = PimBsCategory::getCategoryBySlug($name);

        $category = self::updateOrCreate([
            'office_id' => $office->id,
            'parent_id' => Constant::No,
        ], [
            'position' => Constant::Yes,
            'pim_cat_reference' => $bsCategory->slug,
            'name' => $bsCategory->name,
        ]);

        //Link Office category to PimBsCategoryMapping
        PimBsCategoryMapping::mapPimCategory($category, $bsCategory);

        return $category;
    }

    public static function addChildPimCategory($office, $parent, $name){
        $bsCategory = PimBsCategory::getCategoryBySlug($name);

        $category = self::updateOrCreate([
            'office_id' => $office->id,
        ], [
            'position' => Constant::Yes,
            'parent_id' => $parent->id,
            'pim_cat_reference' => $bsCategory->slug,
            'name' => $bsCategory->name,
        ]);
        //Link Office category to PimBsCategoryMapping
        PimBsCategoryMapping::mapPimCategory($category, $bsCategory);
        return $category;
    }


    public static function getOfficeCategory($officeId)
    {
        return self::select('name', 'pim_cat_reference', 'image')->where('office_id', $officeId)->orderBy('position', "ASC")->get()->toArray();
    }


    public static function getOfficeCategoryByCategoryRef($catSlug,$officeId)
    {
        return self::where('office_id', $officeId)->where('pim_cat_reference', $catSlug)->first();
    }
}
