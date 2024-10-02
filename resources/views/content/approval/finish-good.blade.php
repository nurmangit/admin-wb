@extends('layouts/contentNavbarLayout')

@section('title', ' Finish Good - Forms')

@section('content')
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Forms /</span> Finish Good</h4>

<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Finish Good Details</h5>
      </div>
      <div class="card-body">
        <form>
          <div class="row">
            <div class="col-4">
              <!-- Weighbridge Slip No -->
              <div class="mb-3">
                <label class="form-label" for="weight-bridge-slip-no">Weighbridge Slip No</label>
                <input type="text" class="form-control" id="weight-bridge-slip-no" placeholder="Enter slip number" />
              </div>
              <!-- Arrival Date -->
              <div class="mb-3">
                <label class="form-label" for="arrival-date">Arrival Date</label>
                <input type="date" class="form-control" id="arrival-date" />
              </div>
              <!-- Weighing Type -->
              <div class="mb-3">
                <label class="form-label" for="weighing-type">Weighing Type</label>
                <select class="form-select" id="weighing-type">
                  <option value="gross">Raw Material</option>
                </select>
              </div>
              <!-- Vehicle No -->
              <div class="mb-3">
                <label class="form-label" for="vehicle-no">Vehicle No</label>
                <input type="text" class="form-control" id="vehicle-no" placeholder="Enter vehicle number" />
              </div>
              <!-- Vehicle Type -->
              <div class="mb-3">
                <label class="form-label" for="vehicle-type">Vehicle Type</label>
                <input type="text" class="form-control" id="vehicle-type" placeholder="Enter vehicle type" />
              </div>
              <!-- Tolerance -->
              <div class="mb-3">
                <label class="form-label" for="tolerance">Tolerance</label>
                <input type="number" class="form-control" id="tolerance" placeholder="Enter tolerance" />
              </div>
              <!-- Transporter Name -->
              <div class="mb-3">
                <label class="form-label" for="transporter-name">Transporter Name</label>
                <input type="text" class="form-control" id="transporter-name" placeholder="Enter transporter name" />
              </div>
            </div>
            <div class="col-4">
              <!-- Weight In -->
              <div class="mb-3">
                <label class="form-label" for="weight-in">Weight In</label>
                <input type="number" class="form-control" id="weight-in" placeholder="Enter weight in" />
              </div>
              <!-- Weight Out -->
              <div class="mb-3">
                <label class="form-label" for="weight-out">Weight Out</label>
                <input type="number" class="form-control" id="weight-out" placeholder="Enter weight out" />
              </div>

              <!-- Weight Netto -->
              <div class="mb-3">
                <label class="form-label" for="weight-netto">Weight Netto</label>
                <input type="number" class="form-control" id="weight-netto" placeholder="Enter weight netto" readonly />
              </div>
              <!-- Remark -->
              <div class="mb-3">
                <label class="form-label" for="remark">Remark</label>
                <textarea id="remark" class="form-control" rows='12' placeholder="Enter any remarks"></textarea>
              </div>
            </div>
            <div class="col-4">
              <!-- Date Weight In -->
              <div class="mb-3">
                <label class="form-label" for="date-weight-in">Date Weight In</label>
                <input type="datetime-local" class="form-control" id="date-weight-in" />
              </div>
              <!-- Date Weight Out -->
              <div class="mb-3">
                <label class="form-label" for="date-weight-out">Date Weight Out</label>
                <input type="datetime-local" class="form-control" id="date-weight-out" />
              </div>
              <!-- PO/DO -->
              <div class="mb-3">
                <label class="form-label" for="po-do">PO / DO</label>
                <input type="text" class="form-control" id="po-do" placeholder="Enter PO / DO" />
              </div>
              <div class="mb-3">
                <label class="form-label" for="difference">Difference</label>
                <input type="text" class="form-control" id="difference" placeholder="Enter Difference" />
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end">
            <!-- Submit Button -->
            <button type="submit" style="margin-right: 10px;" class="btn btn-success">Weight IN</button>
            <button type="submit" style="margin-right: 10px;" class="btn btn-info">Weight OUT</button>
            <button type="submit" class="btn btn-primary">Print</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection