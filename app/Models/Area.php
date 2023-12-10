<?php

namespace App\Models;

use App\Helpers\Constant;
use App\Http\Traits\LoggingTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Area extends Model
{
    protected $table = "areas";
    protected $guarded=[];

    public function city()
    {
        return $this->belongsTo(City::class,'city_id');
    }

    public function getNameAttribute($val)
    {
        return ucwords($val);
    }

    public static function getAreaByName($name)
    {
        $fields = [
            'id',
            'name'
        ];

        return self::where('name',$name)->select($fields)->first();
    }

    public static function createArea($data)
    {
        return self::create($data);
    }

    public static function updateAreaName($areaId, $name)
    {
        return self::whereId($areaId)->update(['name' => $name]);
    }

    public static function findOrCreateNewEntries($name, $cityId, $isManualEntry = false)
    {
        $syncNewEntry = Constant::No;
        $result = [];

        if(!empty($name)) {
            $queryName = str_replace("'", "\\'", $name);
            $result = self::where('city_id',$cityId);
//                if($isManualEntry) {
                    $result = $result->whereRaw('LOWER(name) = ?', [strtolower($queryName)])
                                     ->whereRaw('UPPER(name) = ?', [strtoupper($queryName)]);
//                }else {
//                    $result = $result->whereRaw(" LOWER(name) LIKE '%" . strtolower($queryName) . "%' ")
//                                     ->whereRaw(" UPPER(name) LIKE '%" . strtoupper($queryName) . "%' ");
//                }
            $result = $result->first();

            if(empty($result))
            {
                $syncNewEntry = Constant::Yes;

                $area = new self();
                $area->name = $name;
                $area->city_id = $cityId;
                $area->is_active  = Constant::Yes;
                $area->weight     = Constant::Yes;
                $area->is_manual     = $isManualEntry ? Constant::Yes : Constant::No;
                $area->save();

                $result = $area;
            }
        }
        return [
            'result' => $result,
            'syncNewEntry' => $syncNewEntry,
        ];
    }

    public static function updateExistingOrCreateNewArea($address, $name)
    {
        $newAreaAdded = '';
        $newEntries = Constant::No;

        $areaId = $address->area_id;

        $existingAddressCount = EmployeeAddress::getAddressCountByReferrerId($areaId, "area_id");
        if($existingAddressCount > 0) {
            // Save Corresponding City and Area now
            $data = [
                'name' => $address->area->name,
                'city_id' => $address->area_id ?? 0,
                'is_manual' => $address->area->is_manual,
                'is_active' => Constant::Yes,
                'weight' => 0
            ];
            $newAreaAdded = self::createArea($data);

            $newEntries = Constant::Yes;
        }else {
            self::updateAreaName($address->area_id, $name);
        }

        return [
            'newEntries' => $newEntries,
            'area' => $newAreaAdded,
        ];
    }
}
