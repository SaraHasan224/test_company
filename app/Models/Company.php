<?php

namespace App\Models;


use App\Helpers\Constant;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use function Ramsey\Uuid\v4;

class Company extends Model
{
    protected $table ="company";

    protected $fillable = [
        'reference',
        'name' ,
        'domain',
    ];

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }
    public function offices()
    {
        return $this->belongsTo(Office::class);
    }

    public static function getValidationRules( $type, $params = [] )
    {
        $rules = [
            'create' => [
                'name' => 'required|string',
                'domain' => 'required|string',
            ],
            'update' => [
                'name' => 'required|string',
                'domain' => 'required|string',
            ],
        ];

        return $rules[ $type ];
    }

    public static $validationRules = [
        'image-upload' => [
            'banner' => 'required',
            'icon' => 'required',
        ],
    ];


    public static function findById($id)
    {
        return self::where('id', $id)->first();
    }

    public static function findByReference($ref){
        return self::where('reference', $ref)->first();
    }

    public static function getEmployeesByReference($ref){
        $companyIds = self::where('reference', $ref)->get()->pluck('id');
        $officeIds = Office::whereIn('company_id', $companyIds)->get()->pluck('id');

        $data = Employee::whereIn('office_id', $officeIds);


        $count = $data->count();

        return [
            'count'   => $count,
            'offset'  => 0,
            'records' => $data->get()
        ];
    }

    public static function getByFilters($filter)
    {

        $data = self::select('id', 'name', 'reference', 'domain', 'status', 'created_at','updated_at');
        $data = $data->orderBy('id', 'DESC');

        if (count($filter))
        {
            if (!empty($filter['name']))
            {
                $data = $data->where('name', 'LIKE', '%' . trim($filter['name']) . '%');
            }
            if (!empty($filter['domain']))
            {
                $data = $data->where('domain', $filter['domain']);
            }
            if (!empty($filter['reference']))
            {
                $data = $data->where('reference', $filter['reference']);
            }
        }

        $count = $data->count();

//        if (isset($filter['start']) && isset($filter['length']))
//        {
//            $data->skip($filter['start'])->limit($filter['length']);
//        }

        return [
            'count'   => $count,
            'offset'  => isset($filter['start']) ? $filter['start'] : 0,
            'records' => $data->get()
        ];
    }
}
