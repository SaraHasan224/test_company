<?php

namespace App\Models;

use App\Http\Traits\LoggingTrait;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Constant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Province extends Model
{

    protected $table = "provinces";
    protected $guarded=[];

    public function getNameAttribute($val)
    {
        return ucwords($val);
    }

    public function cities()
    {
        return $this->hasMany(City::class )
            ->orderBy('name', 'ASC');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public static function findOrCreateNewEntries($name,$countryId, $isManualEntry = false)
    {
        $result = [];
        $syncNewEntry = Constant::No;

        if(!empty($name)) {
            $queryName = str_replace("'", "\\'", $name);
            $result = self::where('country_id',$countryId);
//            if($isManualEntry) {
                $result = $result->whereRaw('LOWER(name) = ?', [strtolower($queryName)])
                    ->whereRaw('UPPER(name) = ?', [strtoupper($queryName)]);
//            }else {
//                $result = $result->whereRaw(" LOWER(name) LIKE '%" . strtolower($queryName) . "%' ")
//                    ->whereRaw(" UPPER(name) LIKE '%" . strtoupper($queryName) . "%' ");
//            }
            $result = $result->first();

            if(empty($result))
            {
                $syncNewEntry = Constant::Yes;

                $province = new self();
                $province->name = $name;
                $province->country_id = $countryId;
                $province->is_active  = Constant::Yes;
                $province->weight     = Constant::Yes;
                $province->is_manual  = $isManualEntry ? Constant::Yes : Constant::No;
                $province->save();

                $result = $province;
            }
        }

        return [
            'result' => $result,
            'syncNewEntry' => $syncNewEntry,
        ];
    }

    public static function findOrCreate($name,$countryId)
    {
        $queryName = str_replace("'", "\\'", $name);
        $result = self::where('country_id',$countryId)
            ->whereRaw('LOWER(name) = ?', [strtolower($queryName)])
            ->whereRaw('UPPER(name) = ?', [strtoupper($queryName)])
            ->first();
//        $province = self::where('country_id',$countryId)->whereRaw(" LOWER(name) LIKE '%" . strtolower($queryName) . "%' ")
//          ->whereRaw(" UPPER(name) LIKE '%" . strtoupper($queryName) . "%' ")
//          ->first();

        if(empty($result))
        {
            $result = new Province();
            $result->name = $name;
            $result->country_id = $countryId;
            $result->save();
        }
        return $result;
    }

    public static function getProvinceByName($name)
    {
        $fields = [
            'id',
            'name',
        ];

        $province = self::where('name',$name)->select($fields);

        return $province->first();
    }

    public static function getProvinceById($provinceId)
    {
        return self::where('id', $provinceId)->first();
    }

    public static function createProvince($data)
    {
        return self::create($data);
    }

    public static function updateProvinceName($provinceId, $name)
    {
        return self::whereId($provinceId)->update(['name' => $name]);
    }

    public static function updateExistingOrCreateNewProvince($address, $name)
    {
        $newProvinceAdded = '';
        $newCityAdded = '';
        $newAreaAdded = '';
        $newEntries = Constant::No;

        $provinceId = $address->province_id;

        $existingAddressCount = EmployeeAddress::getAddressCountByReferrerId($provinceId, "province_id");
        if($existingAddressCount > 0) {
            $data = [
                'name' => $name,
                'country_id' => $countryId ?? 0,
                'is_manual' => $address->province->is_manual
            ];
            $newProvinceAdded = Province::createProvince($data);
            // Save Corresponding City and Area now
            $data = [
                'name' => $address->city->name,
                'province_id' => $newProvinceAdded->id ?? 0,
                'is_manual' => $address->city->is_manual
            ];
            $newCityAdded = City::createCity($data);
            // Save Corresponding City and Area now
            $data = [
                'name' => $address->area->name,
                'city_id' => $address->area_id ?? 0,
                'is_manual' => $address->area->is_manual,
                'is_active' => Constant::Yes,
                'weight' => 0
            ];
            $newAreaAdded = Area::createArea($data);

            $newEntries = Constant::Yes;
        }else {
            Province::updateProvinceName($address, $name);
        }

        return [
            'newEntries' => $newEntries,
            'province' => $newProvinceAdded,
            'city' => $newCityAdded,
            'area' => $newAreaAdded,
        ];
    }
}
