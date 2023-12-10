<!-- Static Filter Wrap Start -->
<div class="main-card mb-3 card">
    <div class="card-body">
        <form method="POST" id="search-form" class="filterForm form-inline" role="form">
            @csrf

            <div class="form-group">
                <input
                        type="text"
                        name="office_name"
                        id="office_name"
                        placeholder="Office Name"
                        class="form-control mr-3"
                />
            </div>

            <div class="form-group">
                <input
                        type="text"
                        name="office_reference"
                        id="office_reference"
                        placeholder="Office Reference"
                        class="form-control mr-3"
                />
            </div>


            <div class="form-group filterButtons">
                <button type="submit" class="btn btn-primary filter-col mr-2">Search</button>
                <input type="button" onclick="App.Office.removeFilters('office_table');"
                       class="btn btn-primary filter-col mr-2" value="Remove Filters"/>
            </div>

            <div class="form-group" style="padding-left: 5px;">
                <button onclick="App.Helpers.refreshDataTable();" class="btn btn-info" type="button">Refresh</button>
            </div>
        </form>
    </div>
</div>
<!-- Static Filter Wrap End -->