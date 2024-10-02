@extends('layouts/contentNavbarLayout')

@section('title', 'Transporter - List')

@section('content')
<div class="d-flex mt-4 justify-content-between">
  <h4 class="py-2 mb-2">
    <span class="text-muted fw-light">Transporter /</span> List
  </h4>
  <div>
    <a href="{{ route('master-data.vehicle-type.create') }}" class="btn btn-info mb-4 fw-bold">Import Data</a>
    <a href="{{ route('master-data.vehicle-type.create') }}" class="btn btn-success mb-4 fw-bold">Export Data</a>
    <a href="{{ route('master-data.transporter.create') }}" class="btn btn-primary mb-4 fw-bold">Add New Transporter</a>
  </div>
</div>

<!-- Hoverable Table rows -->
<div class="card">
  <div class="table-responsive text-nowrap p-4">
    <table id="transporterTable" class="table table-hover">
      <thead>
        <tr>
          <th>Code</th>
          <th>Name</th>
          <th>Address</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach ($transporters as $transporter)
        <tr>
          <td>{{ $transporter->code }}</td>
          <td>{{ $transporter->name }}</td>
          <td>{{ $transporter->address }}</td>
          <td>
            <div class="d-flex">
              <a href="{{ route('master-data.transporter.edit', $transporter->uuid) }}" class="btn btn-sm btn-info" style="margin-right: 5px;">edit</a>
              <form action="{{ route('master-data.transporter.delete', $transporter->uuid) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this type of vehicle?');">
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
    $('#transporterTable').DataTable({
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