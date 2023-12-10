<div class="main-card mb-3 card">
    <div class="card-body">
        <div class="page-title-actions">
            <div class="d-inline-block pr-3">
                <button class="btn btn-primary fright listing-btns-wrap clear-pagination-state" type="button"
                        onclick="location.href='{{ URL::to('/employees-create/'.$data['office']->office_reference) }}'">
                    <i class="icon-add"></i>
                    <span>Create New Employee</span>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table style="width: 100%;" id="office_employees_table" class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th>S#</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Country</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
