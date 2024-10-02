@extends('layouts/contentNavbarLayout')

@section('title', 'Region - List')

@section('content')
<div class="d-flex mt-4 justify-content-between">
  <h4 class="py-2 mb-2">
    <span class="text-muted fw-light">Region /</span> List
  </h4>
  <div>
    <a href="{{ route('master-data.vehicle-type.create') }}" class="btn btn-info mb-4 fw-bold">Import Data</a>
    <a href="{{ route('master-data.vehicle-type.create') }}" class="btn btn-success mb-4 fw-bold">Export Data</a>
    <a href="{{ route('master-data.region.create') }}" class="btn btn-primary mb-4 fw-bold">Add New Region</a>
  </div>
</div>

<!-- Hoverable Table rows -->
<div class="card">
  <div class="table-responsive text-nowrap p-4">
    <table id="regionTable" class="table table-hover">
      <thead>
        <tr>
          <th>Code</th>
          <th>Name</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach ($regions as $region)
        <tr>
          <td>{{ $region->code }}</td>
          <td>{{ $region->name }}</td>
          <td>
            <div class="d-flex">
              <a href="{{ route('master-data.region.edit', $region->uuid) }}" class="btn btn-sm btn-info" style="margin-right: 5px;">edit</a>
              <form action="{{ route('master-data.region.delete', $region->uuid) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this type of vehicle?');">
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
    $('#regionTable').DataTable({
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