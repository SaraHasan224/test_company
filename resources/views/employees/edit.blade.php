@extends('layouts.master')
@section('page_title',env('APP_NAME').' - Employee Management')
@section('parent_module_breadcrumb_title','Employee Management')

@section('parent_module_icon','lnr-users')
@section('parent_module_title','Account Management')

@section('has_child_breadcrumb_section', true)
{{--@section('has_child_breadcrumb_actions', true)--}}

@section('child_module_icon','icon-breadcrumb')
@section('child_module_breadcrumb_title','Employee')
@section('sub_child_module_icon','icon-breadcrumb')
@section('sub_child_module_breadcrumb_title','Edit')

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
                <section id="section1">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <form id="customer_edit_form" class="newFormContainer" method="post" autocomplete="off">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 formFieldsWrap">

                                        <div class="form-group">
                                            <label>First Name *</label>
                                            <input
                                                    type="text"
                                                    name="first_name"
                                                    maxlength="30"
                                                    placeholder="First Name"
                                                    class="form-control"
                                                    value="{{ !empty(old('first_name')) ? old('first_name') : (!empty($customer) ? $customer->first_name : '') }}"
                                                    required
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-6 formFieldsWrap">

                                        <div class="form-group">
                                            <label>Last Name *</label>
                                            <input
                                                    type="text"
                                                    name="last_name"
                                                    maxlength="30"
                                                    placeholder="last_name"
                                                    class="form-control"
                                                    value="{{ !empty(old('last_name')) ? old('last_name') : (!empty($customer) ? $customer->last_name : '') }}"
                                                    required
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 formFieldsWrap">
                                        <div class="form-group">
                                            <label>Email *</label>
                                            <input
                                                    type="email"
                                                    name="email"
                                                    maxlength="100"
                                                    placeholder="Email"
                                                    class="form-control"
                                                    value="{{ !empty(old('email')) ? old('email') : (!empty($customer) ? $customer->email : '') }}"
                                                    required
                                            >
                                        </div>

                                    </div>
                                    <div class="col-md-6 formFieldsWrap">
                                        <div class="form-group profileMobileNo">
                                            <label class="col-12">Mobile no *</label>
                                            <input
                                                    type="hidden"
                                                    name="country_code"
                                                    id="create_country_code"
                                            >
                                            <input
                                                    type="tel"
                                                    name="phone"
                                                    oninput="App.Helpers.validatePhoneNumber(this)"
                                                    class="form-control col-12"
                                                    value="{{ !empty(old('phone')) ? old('phone') :  (!empty($customer) ? $customer->phone : '') }}"
                                                    required
                                                    id="create_phone"
                                            >
                                            <label id="mcc_code_error" class="help-block error"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 formFieldsWrap">
                                        <div class="form-group">
                                            <label>Username *</label>
                                            <input
                                                    type="text"
                                                    name="username"
                                                    maxlength="100"
                                                    placeholder="username"
                                                    class="form-control"
                                                    value="{{ !empty(old('username')) ? old('username') : (!empty($customer) ? $customer->username : '') }}"
                                                    required
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-3 formFieldsWrap">
                                        <div class="form-group">
                                            <div class="form-group switchFromGrp">
                                                <span class="defaultLabel">Status</span>
                                                <div class="custom-control custom-switch product-purchase-checkbox">
                                                    <input value="{{ !empty(old('is_active')) ? old('is_active') :  (!empty($customer) ? $customer->status : '') }}"
                                                           type="checkbox"
                                                           checked="checked"
                                                           name="is_active"
                                                           class="custom-control-input"
                                                           id="chbox_is_active"
                                                    />

                                                    <label class="custom-control-label"
                                                           for="chbox_is_active"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 formFieldsWrap">
                                        <div class="form-group">
                                            <div class="form-group switchFromGrp">
                                                <span class="defaultLabel">Subscription Status</span>
                                                <div class="custom-control custom-switch product-purchase-checkbox">
                                                    <input value="{{ !empty(old('subscription_status')) ? old('subscription_status') :  (!empty($customer) ? $customer->subscription_status : '') }}"
                                                           type="checkbox"
                                                           checked="checked"
                                                           name="subscription_status"
                                                           class="custom-control-input"
                                                           id="chbox_subscription_status"
                                                    />

                                                    <label class="custom-control-label"
                                                           for="chbox_subscription_status"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    <div class="row">
                                        <div class="col-md-12 formFieldsWrap">
                                            <div class="form-group">
                                                <div class="insideButtons">
                                                    <button id="edit-customer" type="button" class="btn btn-primary"><i
                                                                class="icon-check-thin newMargin"></i>Save
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- /.box-body -->
    </section>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            App.Users.initializeValidations();
            var countryCode = "{{ $customer->country_code }}";
            var phoneNumber = "{{ $customer->phone_number }}";
            var userId = "{{ $customer->id }}";
            $('#create_phone').val(phoneNumber);
            App.Users.editUserFormBinding(userId);
            App.Helpers.getPhoneInput('create_phone', 'create_country_code', true, countryCode, phoneNumber)
        })
    </script>
@endsection
