@extends('layouts/contentNavbarLayout')

@section('title', 'Vehicle - List')

@section('content')
<div class="d-flex mt-4 justify-content-between">
  <h4 class="py-2 mb-2">
    <span class="text-muted fw-light">Vehicle /</span> List
  </h4>
  <div>
    @can('import vehicle')
    <a href="{{ route('master-data.vehicle-type.create') }}" class="btn btn-info mb-4 fw-bold">Import Data</a>
    @endcan

    @can('export vehicle')
    <a href="{{ route('master-data.vehicle-type.create') }}" class="btn btn-success mb-4 fw-bold">Export Data</a>
    @endcan

    @can('create vehicle')
    <a href="{{ route('master-data.vehicle.create') }}" class="btn btn-primary mb-4 fw-bold">Add New Vehicle</a>
    @endcan
  </div>

</div>

<!-- Hoverable Table rows -->
<div class="card">
  <div class="table-responsive text-nowrap p-4">
    <table id="vehicleTable" lass="table table-hover">
      <thead>
        <tr>
          <th>Register Number</th>
          <th>Vehicle Type</th>
          <th>Transporter</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <!-- Example of looping through a list of vehicles -->
        @foreach($vehicles as $vehicle)
        <tr>
          <td>{{ $vehicle->register_number }}</td>
          <td>{{ $vehicle->vehicle_type->name }}</td>
          <td>{{ $vehicle->transporter->name }}</td>
          <td>
            <span class="badge bg-label-{{ $vehicle->status == 'active' ? 'primary' : 'secondary' }}">
              {{ ucfirst($vehicle->status) }}
            </span>
          </td>
          <td>
            <div class="d-flex">
              @can('edit vehicle')
              <a href="{{ route('master-data.vehicle.edit', $vehicle->uuid) }}" class="btn btn-sm btn-info" style="margin-right: 5px;">edit</a>
              @endcan
              @can('delete vehicle')
              <form action="{{ route('master-data.vehicle.delete', $vehicle->uuid) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this type of vehicle?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">
                  delete
                </button>
              </form>
              @endcan
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
    $('#vehicleTable').DataTable({
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