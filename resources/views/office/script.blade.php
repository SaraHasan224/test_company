@section('scripts')
    <script>
        $(document).ready(function () {
            App.Office.initializeValidations();
            App.Office.initializeDataTable();
            $(":input").inputmask();
        })
    </script>
@endsection
