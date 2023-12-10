App.Employee = {
    isAdmin: 0,
    countIds: [],

    initializeValidations: function () {
        $("#search-form").validate();
    },
    removeFilters: function () {
        $("#name").val("");
        $("#username").val("");
        $("#email").val("");
        $("#phone").val("");
        $("#country").val('').trigger('change')
        $("#subscription_status").val('').trigger('change')
        $("#status").val('').trigger('change')
        $("#created_at").val("");
        App.Helpers.removeAllfilters();
    },
    removeSelectionFilters: function () {
        $("#name").val("");
        $("#username").val('').trigger('change');
        $("#phone").val('').trigger('change');
        $("#country").val('').trigger('change');
        $("#subscription_status").val("");
        $("#status").val("").trigger('change');
        $("#created_at").val("");
        App.Helpers.oTable.draw();
    },
    initializeDataTable: function () {
        let table_name = "employees_table";
        let url = App.Helpers.generateApiURL(App.Constants.endPoints.getEmployees);
        let sortColumn = [[2, "desc"]];
        let columns = [
            {data: 'show', name: 'show', orderable: false, searchable: false, className: 'show'},
            {data: "id", name: "id", orderable: true, searchable: true},
            {data: "name", name: "name", orderable: true, searchable: true},
            {data: "username", name: "username", orderable: true, searchable: true},
            {data: "email", name: "email", orderable: true, searchable: true},
            {data: "phone", name: "phone", orderable: true, searchable: true},
            {data: "country", name: "country", orderable: true, searchable: true},
            {data: "subscription_status", name: "subscription_status", orderable: true, searchable: true},
            {data: "status", name: "status", orderable: true, searchable: true},
            {data: "created_at", name: "created_at", orderable: true, searchable: true},
            {data: "updated_at", name: "updated_at", orderable: true, searchable: true},
        ];
        let postData = function (d) {
            d.id = $("#id").val();
            d.name = $("#name").val();
            d.username = $("#username").val();
            d.email = $("#email").val();
            d.phone = $("#phone").val();
            d.country = $("#country").val();
            d.subscription_status = $("#subscription_status").val();
            d.status = $("#status").val();
            d.created_at = $("#created_at").val();
        };

        let orderColumn = sortColumn;
        let searchEnabled = true;
        App.Helpers.CreateDataTableIns(table_name, url, columns, postData, searchEnabled, orderColumn, [], true);
    },
    editEmployeeFormBinding: function (userId) {
        $("#customer-user").bind("click", function (e) {
            if ($("#customer_edit_form").valid()) {
                let url = App.Helpers.generateApiURL(
                    App.Constants.endPoints.editEmployee+"/"+userId
                );
                let onSuccess= function () {
                    if(data.type == "success") {
                        window.location.href = '/employees';
                        App.Helpers.showSuccessMessage( data.message );
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
