<?php

namespace App\Models;


use App\Helpers\Constant;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use function Ramsey\Uuid\v4;

class Office extends Model
{
    protected $table ="offices";

    protected $fillable = [
        'company_id',
        'office_name' ,
        'office_reference',
        "about_office",
        'status',
    ];

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }

    public function products()
    {
        return $this->hasMany(PimProduct::class, 'office_id', 'id');
    }
//    public function orders()
//    {
//        return $this->hasMany(Employee::class, 'office_id', 'id');
//    }

    public static function getValidationRules( $type, $params = [] )
    {
        $rules = [
            'create' => [
                'office_name' => 'required|string',
                'about_office' => 'required|string',
            ],
            'update' => [
                'office_name' => 'required|string',
                'about_office' => 'required|string',
            ],
        ];

        return $rules[ $type ];
    }

    public static $validationRules = [

        'image-upload' => [
            'banner' => 'required',
            'icon' => 'required',
        ],
        'office_categories' => [
            'office_ref' => 'required|string|exists:offices,office_reference',
            'category_slug' => 'required|exists:pim_categories,pim_cat_reference',
        ],
    ];


    public static function findById($id)
    {
        return self::where('id', $id)->first();
    }

    public static function findByReference($ref){
        return self::where('office_reference', $ref)->first();
    }

    public static function getByFilters($filter)
    {
        $data = self::select('id', 'office_name', 'office_reference', 'status', 'created_at','updated_at');
        $data = $data->with('employees')->orderBy('id', 'DESC');

        if (count($filter))
        {
            if (!empty($filter['office_name']))
            {
                $data = $data->where('office_name', 'LIKE', '%' . trim($filter['office_name']) . '%');
            }
            if (!empty($filter['office_reference']))
            {
                $data = $data->where('office_reference', $filter['office_reference']);
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

    public static function getOfficeListing($perPage = "", $type, $disablePagination = false)
    {
        $fields = [
            'id',
            'office_name',
            'office_reference',
        ];
        $query = self::select($fields)->where('status', Constant::Yes)
            ->orderBy('office_name', 'ASC');


        $officeList = $query
            ->paginate($perPage);

        $officeTransformed = $officeList
            ->getCollection()
            ->map(function ($item) use($type){
                unset($item->id);
                return $item;
            })->toArray();
        if($disablePagination) {
            return $officeTransformed;
        }
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $officeTransformed,
            $officeList->total(),
            $officeList->perPage(),
            $officeList->currentPage(), [
                'path' => \Request::url(),
                'query' => [
                    'page' => $officeList->currentPage()
                ]
            ]
        );
    }

    public static function updateOffice( $reference, $requestData )
    {
        $office = Office::findByReference($reference);

        $office->update([
            'about_office' => $requestData['about'],
            'office_name' => $requestData['name'],
        ]);
        $office->fresh();

        return $office;
    }

    public static function getOfficesByReference($company) {
        $data = self::select('id', 'office_name', 'office_reference', 'about_office', 'status', 'created_at','updated_at')
            ->where('company_id', $company->id);

        $count = $data->count();

        return [
            'count'   => $count,
            'offset'  => 0,
            'records' => $data->get()
        ];
    }


    public static function getEmployeesByOffice($company) {
        $officeIds = self::where('company_id', $company->id)->get()->pluck('id');
        $data = Employee::select('id', 'first_name', 'last_name', 'username', 'email', 'created_at','updated_at')
            ->whereIn('office_id', $officeIds);

        $count = $data->count();

        return [
            'count'   => $count,
            'offset'  => 0,
            'records' => $data->get()
        ];
    }
}
