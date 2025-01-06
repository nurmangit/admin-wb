@extends('layouts/contentNavbarLayout')

@section('title', 'Transaction - Report')

@section('content')
<div class="d-flex mt-4 justify-content-between">
  <h4 class="py-2 mb-2">
    <span class="text-muted fw-light">Transaction /</span> Report
  </h4>
</div>

<!-- Hoverable Table rows -->
<div class="row">
  <div class="col mt-2">
    <div class="card">
      <h5 class="card-header">Transporter Report</h5>
      <div class="table-responsive text-nowrap p-4">
        <table id="report" class="table table-hover">
          <thead>
            <tr>
              <th>D/O No</th>
              <th>Date</th>
              <th>Quantity</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach ($reports as $report)
            <tr>
              <td>{{$report->LegalNumber}}</td>
              <td>
                {{ \Carbon\Carbon::parse(str_replace(':AM', ' AM', $report->ShipDate))->format('Y-m-d') }}
              </td>
              <td>{{$report->TotalNetWeight}}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!--/ Hoverable Table rows -->
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('#report').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true
    });
  });
</script>
@endsection