@extends('layouts.master')
@section('page_title',env('APP_NAME').' - Offices')
@section('parent_module_breadcrumb_title','Office Management')

@section('parent_module_icon','pe-7s-diamond')
@section('parent_module_title','Application')

@section('has_child_breadcrumb_section', true)
{{--@section('has_child_breadcrumb_actions', true)--}}

@section('child_module_icon','icon-breadcrumb')
@section('child_module_breadcrumb_title','Offices')
@section('sub_child_module_icon','icon-breadcrumb')
@section('sub_child_module_breadcrumb_title',$data['company']->name)

@section('has_child_breadcrumb_actions')
@endsection

@section('content')
    <section class="content">
        <div class="box box-default">
            <!-- ALERTS STARTS HERE -->
            <section>
                <div class="row">
                    {{--@include('common.alerts')--}}
                </div>
            </section>
            <!-- ALERTS ENDS HERE -->
            <div class="box-body">
                <!-- /.row -->
                {{--<div class="main-card mb-3 card">--}}
                <div class="card-header">
                        <i class="header-icon lnr-license icon-gradient bg-plum-plate"> </i>
                        {{$data['company']->name}}
                        <div class="btn-actions-pane-right">
                            <div class="nav">
                                <a data-toggle="tab" href="#tab-eg2-0" id="company"
                                   class="btn-pill btn-wide active btn btn-outline-alternate btn-sm">Company</a>
                                <a data-toggle="tab" href="#tab-eg2-2" id="officeListing"
                                   class="btn-pill btn-wide mr-1 ml-1 btn btn-outline-alternate btn-sm">Office</a>
                                <a data-toggle="tab" href="#tab-eg2-3" id="employeeListing"
                                   class="btn-pill btn-wide mr-1 ml-1  btn btn-outline-alternate btn-sm">Employees</a>
                            </div>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-eg2-0" role="tabpanel">
                        @include('company.tabs.company')
                    </div>
                    <div class="tab-pane" id="tab-eg2-2" role="tabpanel">
                        @include('company.tabs.office')
                    </div>
                    <div class="tab-pane" id="tab-eg2-3" role="tabpanel">
                        @include('company.tabs.employees')
                    </div>
                </div>
                {{--</div>--}}
            </div>
        </div>
        <!-- /.box-body -->
    </section>
@endsection
@section('scripts')
    <script>
        const reference = "<?php echo $data['company']->reference ?>";
        console.log("reference: ", reference)
        document.getElementById("officeListing").onclick = function () {
            App.Company.initializeCompanyOfficeDataTable(reference);
        };
        document.getElementById("employeeListing").onclick = function () {
            App.Company.initializeCompanyEmployeeDataTable(reference);
        };
        $("#edit-office").bind("click", function (e) {
            if ($("#company_create_form").valid()) {
                let url = App.Helpers.generateApiURL(
                    App.Constants.endPoints.editCompany+"/"+reference
                );

                let onSuccess= function (data) {
                    console.log("success: ", data)
                    if(data.type == "success") {
                        window.location.href = '/company';
                        App.Helpers.showSuccessMessage( data.message );
                    }
                }
                let requestData = $("#company_create_form").serialize();
                App.Ajax.post(url, requestData, onSuccess, false, {});
            }
        });
    </script>
@endsection
