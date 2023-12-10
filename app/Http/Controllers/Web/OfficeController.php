<?php

namespace App\Http\Controllers\Web;

use App\Helpers\ApiResponseHandler;
use App\Helpers\AppException;
use App\Helpers\Constant;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Office;
use App\Models\EmployeeProductRecentlyViewed;
use App\Models\PimProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function Ramsey\Uuid\v4;
use Yajra\DataTables\DataTables;

class OfficeController extends Controller
{
    /**
     * Show the application office orders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try{
            return view('office.index');
        }catch (\Exception $e){
            AppException::log($e);
            return ApiResponseHandler::failure(__('messages.general.failed'), $e->getMessage());
        }
    }

    /**
     * Get list of the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getListingRecord(Request $request)
    {
        try {
            $filter = $request->all();
            $usersRecord = Office::getByFilters($filter);
            $response = $this->makeDatatable($usersRecord);
            return $response;
        } catch (\Exception $e) {
            AppException::log($e);
            dd($e->getTraceAsString());
        }
    }

    private function makeDatatable($data)
    {
        return DataTables::of($data['records'])
            ->addColumn('check', function ($rowdata) {
                $disabled = '';
                if (!empty($rowdata->deleted_at))
                {
                    $disabled = 'disabled="disabled"';
                }
                return '<input type="checkbox" ' . $disabled . ' name="data_raw_id[]"  class="theClass" value="' . $rowdata->id . '">';
            })
            ->addColumn('office_name', function ($rowdata) {
                $disabledClass = "";
                $url = url("/offices/" . $rowdata->office_reference.'/edit');
                $target = "_blank";
                return '<a target="'.$target.'" href="'.$url.'" class="'.$disabledClass.'" >' . $rowdata->office_name . '</a>';
            })
            ->addColumn('status', function ($rowdata) {
                $isActive = $rowdata->status;
                $userStatus = array_flip(Constant::OFFICE_STATUS);
                return '<label class="badge badge-' . Constant::OFFICE_STATUS_STYLE[$isActive] . '"> ' . $userStatus[$isActive] . '</label>';
            })
            ->addColumn('created_at', function ($rowdata) {
//                optional($rowdata->created_record)->name
                return Helper::dated_by(null,$rowdata->created_at);
            })
            ->addColumn('updated_at', function ($rowdata) {
                return Helper::dated_by(null,$rowdata->updated_at);
            })
            ->rawColumns(['check', 'office_name', 'status','created_at','updated_at'])
            ->setOffset($data['offset'])
            ->with([
                "recordsTotal" => $data['count'],
                "recordsFiltered" => $data['count'],
            ])
            ->setTotalRecords($data['count'])
            ->make(true);
    }

    /**
     * Show the form for adding the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add()
    {
        $companies = Company::select('id', 'name')->get();
        return view('office.create', ['module' => "office", "data" => [
            'companies' => $companies
        ]]);
    }

    /**
     * Show the form for adding the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addCompanyOffice($ref)
    {
        $companies = Company::select('id', 'name')->get();
        $company = Company::findByReference($ref);
        return view('office.create', ['module' => "office", "data" => [
            'companies' => $companies,
            'company' => $company
        ]]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $companyRef = "")
    {
        // Check if the incoming request is valid...
        $requestData = $request->all();
        $validationRule = Office::getValidationRules('create', $requestData);
        $validator = Validator::make($requestData, $validationRule);
        if ($validator->fails())
        {
            return ApiResponseHandler::validationError($validator->errors());
        }
        // Retrieve the validated input data...
        $data = $this->storeOrUpdate($requestData, Constant::CRUD_STATES['created'], $companyRef);
        return ApiResponseHandler::success($data);
    }

    private function storeOrUpdate($validated, $state, $companyRef = "", $id = false)
    {
        $validated  = (array) $validated;
        DB::beginTransaction();
        if ($state == Constant::CRUD_STATES['created']) {
            $office = new Office();
            if(!empty($companyRef)) {
                $companyRef = Company::findByReference($companyRef)->id;
            }
        } else {
            if(!empty($companyRef)) {
                $office = Office::findByReference($id);
                $companyRef = Company::findByReference($companyRef)->id;
            }else {
                $office = Office::findByReference($id);
            }
        }
        try {
            $office->company_id = !empty($companyRef) ? $companyRef : $validated['company_id'];
            $office->office_name = array_key_exists("office_name", $validated) ? $validated['office_name'] : "";
            $office->about_office = $validated['about_office'];
            $office->office_reference = v4();
            $office->status = array_key_exists('is_active', $validated) && $validated['is_active'] == 1 ? Constant::OFFICE_STATUS['enabled'] : Constant::OFFICE_STATUS['disabled'];
            $office->save();
            if ((!$office->save())) //|| (!$mapped)
            {
                throw new \Exception("Oopss we are facing some hurdle right now to process this action, please try again");
            }
            DB::commit();
            $return['type'] = 'success';
            $action = array_flip(Constant::CRUD_STATES);
            $return['message'] = 'Office has been ' . $action[$state] . ' successfully.';
            return $return;
        } catch (\Exception $e) {
            AppException::log($e);
            DB::rollback();
            $return['type'] = 'errors';
            $get_environment = env('APP_ENV', 'local');
//            if ($get_environment == 'local') {
                $return['message'] = $e->getMessage();
//            } else {
//                $return['message'] = "Oopss we are facing some hurdle right now to process this action, please try again";
//            }
            return $return;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $ref
     * @return \Illuminate\Http\Response
     */
    public function edit($ref)
    {
        $companies = Company::select('id', 'name')->get();
        $office = Office::findByReference($ref);
        if(empty($office))
        {
            return redirect('/office')->with('warning_msg', "Record not found.");
        }else{
            $data['office'] = $office;
            $data['dashboard'] = (object) [
                'order_count' => 0,
            ];
            $data['companies'] = $companies;
            return view('office.edit', [
                'module' => "office",
                'data' => $data,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id, $companyRef = "")
    {

        // Check if the incoming request is valid...
        $requestData = $request->all();
        $validationRule = Office::getValidationRules('update', $requestData);
        $validator = Validator::make($requestData, $validationRule);
        if ($validator->fails())
        {
            return ApiResponseHandler::validationError($validator->errors());
        }
//
        $data = $this->storeOrUpdate($requestData, Constant::CRUD_STATES['updated'], $companyRef, $id);
        return ApiResponseHandler::success($data);
    }

    public function toggleProductStatus(Request $request)
    {
        try {
            $requestData = $request->all();
            $handle = $requestData['handle'];
            $status = $requestData['status'];

            PimProduct::updateStatus($handle, $status);
            $return['type'] = 'success';
            $return['message'] = 'Product status has been updated successfully';
            return $return;
        } catch (\Exception $e) {
            AppException::log($e);
            $return['type'] = 'errors';
            $get_environment = env('APP_ENV', 'local');
            if ($get_environment == 'local') {
                $return['message'] = $e->getMessage();
            } else {
                $return['message'] = "Oopss we are facing some hurdle right now to process this action, please try again";
            }
            return $return;
        }
    }


    public function toggleProductFeaturedStatus(Request $request)
    {
        try {
            $requestData = $request->all();
            $handle = $requestData['handle'];
            $status = $requestData['is_featured'];
            $position = array_key_exists('position', $requestData) ? $requestData['position'] : 0;

            PimProduct::updateFeaturedStatus($handle, $status, $position);
            $return['type'] = 'success';
            $return['message'] = 'Product feature status has been updated successfully';
            return $return;
        } catch (\Exception $e) {
            AppException::log($e);
            $return['type'] = 'errors';
            $get_environment = env('APP_ENV', 'local');
            if ($get_environment == 'local') {
                $return['message'] = $e->getMessage();
            } else {
                $return['message'] = "Oopss we are facing some hurdle right now to process this action, please try again";
            }
            return $return;
        }
    }

    /**
     * Get list of the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getAllCompanyEmployeesListingRecord(Request $request, $ref)
    {
        try {
            $officeId = Office::findByReference($ref)->id;
            $usersRecord = Employee::getOfficesEmployees($officeId);
            $response = $this->makeCompanyOfficeEmployeesDatatable($usersRecord);
            return $response;
        } catch (\Exception $e) {
            AppException::log($e);
            dd($e->getTraceAsString());
        }
    }

    private function makeCompanyOfficeEmployeesDatatable($data)
    {
        return DataTables::of($data['records'])
            ->addColumn('check', function ($rowdata) {
                $disabled = '';
//                if (!empty($rowdata->
                return '<input type="checkbox" ' . $disabled . ' name="data_raw_id[]"  class="theClass" value="' . $rowdata->id . '">';
            })
            ->addColumn('name', function ($rowdata) {
                $disabledClass = "";
                $url = url("/employees/" . $rowdata->identifier.'/edit');
                $target = "_blank";
                return '<a target="'.$target.'" href="'.$url.'" class="'.$disabledClass.'" >' . `{$rowdata->first_name} {$rowdata->last_name}` . '</a>';
            })
            ->addColumn('status', function ($rowdata) {
                $isActive = $rowdata->status;
                $userStatus = array_flip(Constant::EMPLOYEE_STATUS);
                return '<label class="badge badge-' . Constant::EMPLOYEE_STATUS_STYLE[$isActive] . '"> ' . $userStatus[$isActive] . '</label>';
            })
            ->addColumn('phone', function ($rowdata) {
                return "+(".$rowdata->country_code.")".$rowdata->phone_number;
            })
            ->addColumn('country', function ($rowdata) {
                return $rowdata->country->name;
            })
            ->addColumn('created_at', function ($rowdata) {
//                optional($rowdata->created_record)->name
                return Helper::dated_by(null, $rowdata->created_at);
            })
            ->addColumn('updated_at', function ($rowdata) {
                return Helper::dated_by(null,$rowdata->updated_at);
            })
            ->rawColumns(['check', 'name', 'status','created_at','updated_at'])
            ->setOffset($data['offset'])
            ->with([
                "recordsTotal" => $data['count'],
                "recordsFiltered" => $data['count'],
            ])
            ->setTotalRecords($data['count'])
            ->make(true);
    }
}
