<div class="main-card mb-3 card">
    <div class="card-body">
        <div class="page-title-actions">
            <div class="d-inline-block pr-3">
                <button class="btn btn-primary fright listing-btns-wrap clear-pagination-state" type="button"
                        onclick="location.href='{{ URL::to('/office-create/'.$data['company']->reference) }}'">
                    <i class="icon-add"></i>
                    <span>Create New Office</span>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table style="width: 100%;" id="company_offices_table" class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th>S#</th>
                <th>Name</th>
                <th>Reference</th>
                <th>About</th>
                <th>Status</th>
                <th>Total No. of employees</th>
                <th>Created at</th>
                <th>Updated at</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
