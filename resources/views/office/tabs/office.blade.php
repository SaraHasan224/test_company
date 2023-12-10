<div class="card-body">
    <section>

        <div id="exampleAccordion" data-children=".item">
            <div class="item">
                <button type="button" aria-expanded="true" aria-controls="exampleAccordion2" data-toggle="collapse"
                        href="#collapseExample2" class="m-0 p-0 btn btn-link">
                    <h5 class="pb-3 card-title">Office Information</h5>
                </button>
                <div data-parent="#exampleAccordion" id="collapseExample2" class="show">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <form id="office_create_form" class="newFormContainer" method="post" autocomplete="off">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 formFieldsWrap">

                                        <div class="form-group">
                                            <label>Office Name</label>
                                            <div>
                                                <div class="chat-box">
                                                    <input
                                                            type="text"
                                                            name="office_name"
                                                            maxlength="30"
                                                            placeholder="Name"
                                                            class="form-control"
                                                            value="{{ !empty(old('office_name')) ? old('office_name') : (isset($data['office']) && !empty($data['office']->office_name) ? $data['office']->office_name : '') }}"
                                                            required
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 formFieldsWrap">

                                        <div class="form-group">
                                            <label>Office Name</label>
                                            <div>
                                                <div class="chat-box">
                                                    <select
                                                            class="form-control {{isset($data['company']) && !empty($data['company']->id) ? "disabled" : ""}}"
                                                            name="company_id"
                                                            id="company_id"
                                                    >
                                                        <option value="">Company</option>
                                                        @foreach($data['companies'] as $key => $value)
                                                            <option
                                                                    value="{{$value->id}}"
                                                                    {{((isset($data['company']) && !empty($data['company']->id) && $value->id == $data['company']->id) || ((isset($data['office']) && $value->id == $data['office']->company_id)) ? "selected" : "") }}
                                                            >{{$value->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 formFieldsWrap">
                                        <div class="form-group">
                                            <label>Description *</label>
                                            <div>
                                                <div class="chat-box">
                                                    <textarea
                                                            class="form-control"
                                                            id="about_office"
                                                            name="about_office"
                                                    >
                                                        {{isset($data['office']) ? $data['office']->about_office : ""}}
                                                    </textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 formFieldsWrap">
                                        <div class="form-group">
                                            <div class="form-group switchFromGrp">
                                                <span class="defaultLabel">Status</span>
                                                <div class="custom-control custom-switch product-purchase-checkbox">
                                                    <input value="{{ !empty(old('is_active')) ? old('is_active') :  (isset($data['office']) && $data['office']->is_active ? $data['office']->is_active : 1) }}"
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
                                </div>
                                <div class="row d-none">
                                    <div class="col-md-12 formFieldsWrap">
                                        <div class="form-group">
                                            <div class="insideButtons">
                                                <button id="edit-customer" type="button" disabled="true"
                                                        class="btn btn-primary"><i
                                                            class="icon-check-thin newMargin"></i>Save
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="d-block text-right card-footer">
    <a href="javascript:void(0);" id="create-office"  class="btn-wide btn btn-success">Save</a>
</div>