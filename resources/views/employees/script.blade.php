@section('scripts')
    <script>
        $(document).ready(function () {
            App.Employee.initializeValidations();
            App.Employee.initializeDataTable();
            $(":input").inputmask();
        })
    </script>
@endsection
