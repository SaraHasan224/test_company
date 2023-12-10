<?php

namespace App\Http\Controllers\Web;

use App\Helpers\ApiResponseHandler;
use App\Helpers\AppException;
use App\Helpers\Constant;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function Ramsey\Uuid\v4;
use Yajra\DataTables\DataTables;

class CompanyController extends Controller
{
    /**
     * Show the application office orders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try{
            return view('company.index');
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
            $usersRecord = Company::getByFilters($filter);
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
//                if (!empty($rowdata->
                return '<input type="checkbox" ' . $disabled . ' name="data_raw_id[]"  class="theClass" value="' . $rowdata->id . '">';
            })
            ->addColumn('name', function ($rowdata) {
                $disabledClass = "";
                $url = url("/company/" . $rowdata->reference.'/edit');
                $target = "_blank";
                return '<a target="'.$target.'" href="'.$url.'" class="'.$disabledClass.'" >' . $rowdata->name . '</a>';
            })
            ->addColumn('status', function ($rowdata) {
                $isActive = $rowdata->status;
                $userStatus = array_flip(Constant::COMPANY_STATUS);
                return '<label class="badge badge-' . Constant::COMPANY_STATUS_STYLE[$isActive] . '"> ' . $userStatus[$isActive] . '</label>';
            })
            ->addColumn('created_at', function ($rowdata) {
//                optional($rowdata->created_record)->name
                return Helper::dated_by(null,$rowdata->created_at);
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

    /**
     * Show the form for adding the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add()
    {
        return view('company.create', ['module' => "office"]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check if the incoming request is valid...
        $requestData = $request->all();
        $validationRule = Company::getValidationRules('create', $requestData);
        $validator = Validator::make($requestData, $validationRule);
        if ($validator->fails())
        {
            return ApiResponseHandler::validationError($validator->errors());
        }
        // Retrieve the validated input data...
        $data = $this->storeOrUpdate($requestData, Constant::CRUD_STATES['created']);
        return ApiResponseHandler::success($data);
    }

    private function storeOrUpdate($validated, $state, $id = false)
    {
        $validated  = (array) $validated;
        DB::beginTransaction();
        if ($state == Constant::CRUD_STATES['created']) {
            $office = new Company();
        } else {
            $office = Company::findByReference($id);
        }
        try {
            $office->name = array_key_exists("name", $validated) ? $validated['name'] : "";
            $office->domain = $validated['domain'];
            $office->reference = v4();
            $office->status = array_key_exists('status', $validated) && $validated['status'] == 1 ? Constant::COMPANY_STATUS['enabled'] : Constant::COMPANY_STATUS['disabled'];
            $office->save();
            if ((!$office->save())) //|| (!$mapped)
            {
                throw new \Exception("Oopss we are facing some hurdle right now to process this action, please try again");
            }
            DB::commit();
            $return['type'] = 'success';
            $action = array_flip(Constant::CRUD_STATES);
            $return['message'] = 'Company has been ' . $action[$state] . ' successfully.';
            return $return;
        } catch (\Exception $e) {
            AppException::log($e);
            DB::rollback();
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
     * Show the form for editing the specified resource.
     *
     * @param  int $ref
     * @return \Illuminate\Http\Response
     */
    public function edit($ref)
    {
        $company = Company::findByReference($ref);
        if(empty($company))
        {
            return redirect('/company')->with('warning_msg', "Record not found.");
        }else{
            $data['company'] = $company;
            $data['dashboard'] = (object) [
                'order_count' => 0,
            ];
            return view('company.edit', ['module' => "company", 'data' => $data]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {

        // Check if the incoming request is valid...
        $requestData = $request->all();
        $validationRule = Company::getValidationRules('update', $requestData);
        $validator = Validator::make($requestData, $validationRule);
        if ($validator->fails())
        {
            return ApiResponseHandler::validationError($validator->errors());
        }
//
        $data = $this->storeOrUpdate($requestData, Constant::CRUD_STATES['updated'], $id);
        return ApiResponseHandler::success($data);
    }



    /**
     * Get list of the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getAllCompanyOfficesListingRecord(Request $request,$ref)
    {
        try {
            $companyRef = Company::findByReference($ref);

            $usersRecord = Office::getOfficesByReference($companyRef);
            $response = $this->makeCompanyOfficesDatatable($usersRecord);
            return $response;
        } catch (\Exception $e) {
            AppException::log($e);
            dd($e->getTraceAsString());
        }
    }

    private function makeCompanyOfficesDatatable($data)
    {
        return DataTables::of($data['records'])
            ->addColumn('check', function ($rowdata) {
                $disabled = '';
//                if (!empty($rowdata->
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
            ->addColumn('no_of_emp', function ($rowdata) {
                return optional($rowdata->employees)->count() ?? 0;
            })
            ->addColumn('created_at', function ($rowdata) {
//                optional($rowdata->created_record)->name
                return Helper::dated_by(null, $rowdata->created_at);
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
     * Get list of the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getAllCompanyEmployeesListingRecord(Request $request, $ref)
    {
        try {
            $companyRef = Company::findByReference($ref);

            $usersRecord = Office::getOfficesByReference($companyRef);
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
