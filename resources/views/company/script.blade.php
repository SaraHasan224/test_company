@section('scripts')
    <script>
        $(document).ready(function () {
            App.Company.initializeValidations();
            App.Company.initializeDataTable();
            $(":input").inputmask();
        })
    </script>
@endsection
