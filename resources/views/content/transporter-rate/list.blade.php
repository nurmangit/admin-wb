@extends('layouts/contentNavbarLayout')

@section('title', 'Transporter Rate - List')

@section('content')
<div class="d-flex mt-4 justify-content-between">
  <h4 class="py-2 mb-2">
    <span class="text-muted fw-light">Transporter Rate /</span> List
  </h4>
  <div>
    <a href="{{ route('master-data.vehicle-type.create') }}" class="btn btn-info mb-4 fw-bold">Import Data</a>
    <a href="{{ route('master-data.vehicle-type.create') }}" class="btn btn-success mb-4 fw-bold">Export Data</a>
    <a href="{{ route('master-data.transporter-rate.create') }}" class="btn btn-primary mb-4 fw-bold">Add New Transporter Rate</a>
  </div>
</div>

<!-- Hoverable Table rows -->
<div class="card">
  <h5 class="card-header">Transporter Rates</h5>
  <div class="table-responsive text-nowrap p-4">
    <table id="transporterRateTable" class="table table-hover">
      <thead>
        <tr>
          <th>Name</th>
          <th>Area</th>
          <th>Region</th>
          <th>Rate</th>
          <th>charge</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach ($transporterRates as $transporterRate)
        <tr>
          <td>{{ $transporterRate->name }}</td>
          <td>{{ $transporterRate->area->name }}</td>
          <td>{{ $transporterRate->area->region->name }}</td>
          <td>{{ $transporterRate->charge }}</td>
          <td>{{ $transporterRate->rate }}</td>
          <td>{{ $transporterRate->start_date }}</td>
          <td>{{ $transporterRate->end_date }}</td>
          <td>
            <div class="d-flex">
              <a href="{{ route('master-data.transporter-rate.edit', $transporterRate->uuid) }}" class="btn btn-sm btn-info" style="margin-right: 5px;">edit</a>
              <form action="{{ route('master-data.transporter-rate.delete', $transporterRate->uuid) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this type of vehicle?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">delete
                </button>
              </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
<!--/ Hoverable Table rows -->
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('#transporterRateTable').DataTable({
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