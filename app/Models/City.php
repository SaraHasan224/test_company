<?php

namespace App\Models;

use App\Http\Traits\LoggingTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constant;
use Illuminate\Support\Str;

class City extends Model
{
    protected $table = "cities";
    protected $guarded=[];

    public function areas()
    {
        return $this->hasMany(Area::class )
            ->select('id','city_id','name',
                DB::raw("trim(substring_index(name,' ',1) )as c1"),
                DB::raw("CAST(trim(substring_index(name,' ',-1) ) AS UNSIGNED INTEGER ) as c2")
            )
            ->orderBy('c1', 'ASC')
            ->orderBy('c2', 'ASC');
    }

    public function province()
    {
        return $this->belongsTo(Province::class,'province_id');
    }

    public function getNameAttribute($val)
    {
        return ucwords($val);
    }

    public static function findOrCreateNewEntries($name,$provinceId, $isManualEntry = false)
    {
        $result = [];
        $syncNewEntry = Constant::No;
        if(!empty($name)) {
            $queryName = str_replace("'", "\\'", $name);
            $result = self::where('province_id',$provinceId);
//            if($isManualEntry) {
                $result = $result->whereRaw('LOWER(name) = ?', [strtolower($queryName)])
                    ->whereRaw('UPPER(name) = ?', [strtoupper($queryName)]);
//            }else {
//                $result = $result->whereRaw(" LOWER(name) LIKE '%" . strtolower($queryName) . "%' ")
//                    ->whereRaw(" UPPER(name) LIKE '%" . strtoupper($queryName) . "%' ");
//            }
            $result = $result->first();

//            $result = self::where('province_id',$provinceId)->whereRaw("LOWER(name) LIKE '%" . strtolower($queryName) . "%' ")
//                ->whereRaw(" UPPER(name) LIKE '%" . ($queryName) . "%' ")
//                ->first();

            if(empty($result))
            {
                $syncNewEntry = Constant::Yes;

                $city = new self();
                $city->name = $name;
                $city->province_id = $provinceId;
                $city->is_active  = Constant::Yes;
                $city->weight     = Constant::Yes;
                $city->is_manual     = $isManualEntry ? Constant::Yes : Constant::No;
                $city->save();

                $result = $city;
            }
        }
        return [
            'result' => $result,
            'syncNewEntry' => $syncNewEntry,
        ];
    }


    public static function findOrCreate($name,$provinceId)
    {
        $queryName = str_replace("'", "\\'", $name);
        $city = self::where('province_id',$provinceId)->whereRaw(" LOWER(name) LIKE '%" . strtolower($queryName) . "%' ")
          ->whereRaw(" UPPER(name) LIKE '%" . strtoupper($queryName) . "%' ")
          ->first();

        if(empty($city))
        {
            $city = new City;
            $city->name = $name;
            $city->province_id = $provinceId;
            $city->save();
        }
        return $city;
    }

    public static function getCityByName($name)
    {
        $fields = [
            'id',
            'name'
        ];

        return self::where('name',$name)->select($fields)->first();
    }

    public static function createCity($data)
    {
        return self::create($data);
    }

    public static function getCityById($cityId)
    {
        return self::where('id', $cityId)->first();
    }

    public static function updateCityName($cityId, $name)
    {
        return self::whereId($cityId)->update(['name' => $name]);
    }

    public static function updateExistingOrCreateNewCity($address, $name)
    {
        $newCityAdded = '';
        $newAreaAdded = '';
        $newEntries = Constant::No;

        $cityId = $address->city_id;

        $existingAddressCount = EmployeeAddress::getAddressCountByReferrerId($cityId, "city_id");
        if($existingAddressCount > 0) {
            $data = [
                'name' => $name,
                'province_id' => $address->province_id ?? 0,
                'is_manual' => $address->city->is_manual
            ];
            $newCityAdded = City::createCity($data);
            // Save Corresponding City and Area now
            $data = [
                'name' => $address->area->name,
                'city_id' => $newCityAdded->id ?? 0,
                'is_manual' => $address->area->is_manual,
                'is_active' => Constant::Yes,
                'weight' => 0
            ];
            $newAreaAdded = Area::createArea($data);

            $newEntries = Constant::Yes;

            $address->city_id = $newCityAdded->id;
            $address->area_id = $newAreaAdded->id;
            $address->save();
        }else {
            City::updateCityName($address->city_id, $name);
        }

        return [
            'newEntries' => $newEntries,
            'city' => $newCityAdded,
            'area' => $newAreaAdded,
        ];
    }
}
