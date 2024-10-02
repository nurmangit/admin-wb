@extends('layouts/contentNavbarLayout')

@section('title', 'Transaction - List')

@section('content')
<div class="d-flex mt-4 justify-content-between">
  <h4 class="py-2 mb-2">
    <span class="text-muted fw-light">Transaction /</span> Data
  </h4>
  <div>
    <a href="{{ route('master-data.vehicle-type.create') }}" class="btn btn-success mb-4 fw-bold">Export Data</a>
  </div>
</div>

<!-- Hoverable Table rows -->
<div class="card">
  <h5 class="card-header">Transaction Finish Good's</h5>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>No</th>
          <th>WB Slip No</th>
          <th>Vehicle No</th>
          <th>Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <tr>
          <td>1</td>
          <td>12312312312</td>
          <td>WB 1213 UB</td>
          <td>2024-10-10</td>
          <td><span class="badge bg-label-primary me-1">FG-in</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-1"></i> Delete</a>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<!--/ Hoverable Table rows -->
@endsection