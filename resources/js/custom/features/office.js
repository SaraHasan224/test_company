App.Office = {
    countIds: [],
    initializeValidations: function () {
        $("#search-form").validate();
    },
    removeFilters: function (id) {
        $("#office_reference").val("");
        $("#office_name").val("");
        App.Helpers.removeAllfilters(id);
    },
    removeSelectionFilters: function () {
        $("#customer").val("");
        $("#office_name").val('').trigger('change');
        App.Helpers.oTable.draw();
    },
    initializeDataTable: function () {
        let table_name = "company_table";
        let url = App.Helpers.generateApiURL(App.Constants.endPoints.getOffices);
        let sortColumn = [[2, "desc"]];
        let columns = [
            {data: 'show', name: 'show', orderable: false, searchable: false, className: 'show'},
            // {data: "id", name: "id", orderable: true, searchable: true},
            {data: "office_name", name: "office_name", orderable: true, searchable: true},
            {data: "office_reference", name: "office_reference", orderable: true, searchable: true},
            {data: "status", name: "status", orderable: true, searchable: true},
            {data: "created_at", name: "created_at", orderable: true, searchable: true},
            {data: "updated_at", name: "updated_at", orderable: true, searchable: true}
        ];
        let postData = function (d) {
            d.office_name = $("#office_name").val();
            d.office_reference = $("#office_reference").val();
        };

        let orderColumn = sortColumn;
        let searchEnabled = true;
        App.Helpers.CreateDataTableIns(table_name, url, columns, postData, searchEnabled, orderColumn, [], true);
    },
    initializeOfficeEmployeeDataTable: function (ref) {
        let table_name = "office_employees_table";
        let url = App.Helpers.generateApiURL(App.Constants.endPoints.getOfficesEmployees) + "/" + ref;
        let sortColumn = [[2, "desc"]];
        let columns = [
            {data: 'check', name: 'check', orderable: false, searchable: false, className: 'show'},
            {data: "name", name: "name", orderable: true, searchable: true},
            {data: "username", name: "username", orderable: true, searchable: false},
            {data: "email", name: "email", orderable: true, searchable: false},
            {data: "phone", name: "phone", orderable: true, searchable: true},
            {data: "country", name: "country", orderable: true, searchable: true},
            {data: "status", name: "status", orderable: true, searchable: true},
        ];
        let postData = function (d) {
            // d.customer = $("#customer").val();
            // d.office_name = $("#office_name").val();
        };

        let orderColumn = sortColumn;
        let searchEnabled = true;
        App.Helpers.CreateDataTableIns(table_name, url, columns, postData, searchEnabled, orderColumn, [], true);
    },
    createOfficeFormBinding: function () {
        $("#create-user").bind("click", function (e) {
            if ($("#office_create_form").valid()) {
                let url = App.Helpers.generateApiURL(
                    App.Constants.endPoints.createOffice
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
    },
    editEmployeeFormBinding: function (userId) {
        $("#customer-user").bind("click", function (e) {
            if ($("#office_edit_form").valid()) {
                let url = App.Helpers.generateApiURL(
                    App.Constants.endPoints.editEmployee + "/" + userId
                );
                let onSuccess = function () {
                    if (data.type == "success") {
                        window.location.href = '/offices';
                        App.Helpers.showSuccessMessage(data.message);
                    }
                };
                let requestData = $("#customer_edit_form").serialize();
                App.Ajax.post(url, requestData, onSuccess, false, {});
            }
        });
    },
    updateEmployeeStatus: function (thisKey, customerId) {
        var customerStatusValue = $(thisKey).find(':selected').text();
        let action = function (isConfirm) {
            if (isConfirm) {
                var customerStatus = $(thisKey).val();
                let onSuccess = function (response) {

                };
                let requestData = {'customer_status': customerStatus, 'customer_id': customerId};
                let url = App.Helpers.generateApiURL(App.Constants.endPoints.updateEmployeeStatus);
                App.Ajax.post(url, requestData, onSuccess, false, '', 0);
            }
        };

        App.Helpers.confirm('You want to mark selected customer as ' + customerStatusValue.toLowerCase() + '.', action);

    },
};
