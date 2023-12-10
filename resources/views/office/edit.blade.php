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
@section('sub_child_module_breadcrumb_title',$data['office']->office_name)

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
                        {{$data['office']->office_name}}
                        <div class="btn-actions-pane-right">
                        <div class="nav">
                            <a data-toggle="tab" href="#tab-eg2-0" id="office"
                               class="btn-pill btn-wide active btn btn-outline-alternate btn-sm">Office</a>
                            <a data-toggle="tab" href="#tab-eg2-2" id="employeesListing"
                               class="btn-pill btn-wide mr-1 ml-1  btn btn-outline-alternate btn-sm">Employees</a>
                        </div>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-eg2-0" role="tabpanel">
                        @include('office.tabs.office')
                    </div>
                    <div class="tab-pane" id="tab-eg2-2" role="tabpanel">
                        @include('office.tabs.employees')
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
        const office_ref = "<?php echo $data['office']->office_reference ?>";

        document.getElementById("employeesListing").onclick = function () {
            App.Office.initializeOfficeEmployeeDataTable(office_ref);
        };
        $("#create-office").bind("click", function (e) {
            if ($("#office_create_form").valid()) {
                let url = App.Helpers.generateApiURL(
                    App.Constants.endPoints.editOffice+"/"+office_ref
                );

                let onSuccess= function (data) {
                    console.log("success: ", data)
                    if(data.type == "success") {
                        window.location.href = '/office';
                        App.Helpers.showSuccessMessage( data.message );
                    }
                }
                let requestData = $("#office_create_form").serialize();
                App.Ajax.post(url, requestData, onSuccess, false, {});
            }
        });
    </script>
@endsection
