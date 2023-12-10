<div class="card-body">
    <section>
        <div id="exampleAccordion" data-children=".item">
            <div class="item">
                <button type="button" aria-expanded="true" aria-controls="exampleAccordion2" data-toggle="collapse"
                        href="#collapseExample2" class="m-0 p-0 btn btn-link">
                    <h5 class="pb-3 card-title">Company Information</h5>
                </button>
                <div data-parent="#exampleAccordion" id="collapseExample2" class="show">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <form id="company_create_form" class="newFormContainer" method="post" autocomplete="off">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 formFieldsWrap">

                                        <div class="form-group">
                                            <label>Company Name</label>
                                            <div>
                                                <div class="chat-box">
                                                    <input
                                                            type="text"
                                                            name="name"
                                                            maxlength="30"
                                                            placeholder="Name"
                                                            class="form-control"
                                                            value="{{ !empty(old('name')) ? old('name') : (isset($data['company']) && !empty($data['company']->name) ? $data['company']->name : '') }}"
                                                            required
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 formFieldsWrap">
                                        <div class="form-group">
                                            <label>Domain *</label>
                                            <div>
                                                <div class="chat-box">
                                                    <input
                                                            type="text"
                                                            name="domain"
                                                            maxlength="30"
                                                            placeholder="Domain"
                                                            class="form-control"
                                                            value="{{ !empty(old('company')) ? old('company') : (isset($data['company']) && !empty($data['company']->domain) ? $data['company']->domain : '') }}"
                                                            required
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 formFieldsWrap">
                                        <div class="form-group">
                                            <div class="form-group switchFromGrp">
                                                <span class="defaultLabel">Status</span>
                                                <div class="custom-control custom-switch product-purchase-checkbox">
                                                    <input value="{{ !empty(old('status')) ? old('status') :  (isset($data['company']) && $data['company']->status ? $data['company']->status : 1) }}"
                                                           type="checkbox"
                                                           checked="checked"
                                                           name="status"
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
    <a href="javascript:void(0);" id="create-company"  class="btn-wide btn btn-success">Save</a>
</div>