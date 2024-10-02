@extends('layouts/contentNavbarLayout')

@section('title', ' Receiving Material - Forms')

@section('content')
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Forms /</span> Receiving Material</h4>

<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Receiving Material Details</h5>
      </div>
      <div class="card-body">
        <form id="weighbridgeForm">
          @csrf
          <div class="row">
            <div class="col-4">
              <!-- Weighbridge Slip No -->
              <div class="mb-3">
                <label class="form-label" for="weight-bridge-slip-no">Weighbridge Slip No</label>
                <input type="text" class="form-control" value="{{$slip_no}}" id="weight-bridge-slip-no" placeholder="Auto generated" disabled />
              </div>
              <!-- Arrival Date -->
              <div class="mb-3">
                <label class="form-label" for="arrival-date">Arrival Date</label>
                <input type="date" class="form-control" id="arrival-date" readonly />
              </div>
              <!-- Weighing Type -->
              <div class="mb-3">
                <label class="form-label" for="weighing-type">Weighing Type</label>
                <select class="form-select" id="weighing-type" name="weighing_type">
                  <option value="rm">Raw Material</option>
                </select>
              </div>
              <!-- Vehicle No -->
              <div class="mb-3">
                <label class="form-label" for="vehicle-no">Vehicle No</label>
                <input type="text" class="form-control @error('vehicle_no') is-invalid @enderror" id="vehicle-no" name="vehicle_no" placeholder="Enter vehicle number" />
                <div class="invalid-feedback">
                  Vehicle number not found.
                </div>
              </div>
              <!-- Vehicle Type -->
              <div class="mb-3">
                <label class="form-label" for="vehicle-type">Vehicle Type</label>
                <input disabled type="text" class="form-control" id="vehicle-type" placeholder="Enter vehicle type" />
              </div>
              <!-- Tolerance -->
              <div class="mb-3">
                <label class="form-label" for="tolerance">Tolerance</label>
                <input disabled type="number" class="form-control" id="tolerance" placeholder="Enter tolerance" />
              </div>
              <!-- Transporter Name -->
              <div class="mb-3">
                <label class="form-label" for="transporter-name">Transporter Name</label>
                <input disabled type="text" class="form-control" id="transporter-name" placeholder="Enter transporter name" />
              </div>
            </div>
            <div class="col-4">
              <!-- Weight In -->
              <div class="mb-3">
                <label class="form-label" for="weight-in">Weight In</label>
                <input type="number" class="form-control  @error('weight_in') is-invalid @enderror" name="weight_in" id="weight-in" placeholder="Enter weight in" />
                @error('weight_in')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <!-- Weight Out -->
              <div class="mb-3">
                <label class="form-label" for="weight-out">Weight Out</label>
                <input type="number" class="form-control  @error('weight_out') is-invalid @enderror" name="weight_out" id="weight-out" placeholder="Enter weight out" />
                @error('weight_out')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Weight Netto -->
              <div class="mb-3">
                <label class="form-label" for="weight-netto">Weight Netto</label>
                <input type="number" class="form-control" id="weight-netto" placeholder="Enter weight netto" disabled />
              </div>
              <!-- Remark -->
              <div class="mb-3">
                <label class="form-label" for="remark">Remark</label>
                <textarea id="remark" class="form-control @error('remark') is-invalid @enderror" name="remark" rows='12' placeholder="Enter any remarks"></textarea>
                @error('remark')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-4">
              <!-- Date Weight In -->
              <div class="mb-3">
                <label class="form-label" for="date-weight-in">Date Weight In</label>
                <input type="datetime-local" class="form-control" id="date-weight-in" disabled />
              </div>
              <!-- Date Weight Out -->
              <div class="mb-3">
                <label class="form-label" for="date-weight-out">Date Weight Out</label>
                <input type="datetime-local" class="form-control" id="date-weight-out" disabled />
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end">
            <!-- Submit Button -->
            <button type="button" style="margin-right: 10px;" id="weightInBtn" class="btn btn-success fw-bold">WEIGHT IN</button>
            <button type="button" style="margin-right: 10px;" id="weightOutBtn" class="btn btn-info fw-bold">WEIGHT OUT</button>
            <button type="button" class="btn btn-primary fw-bold">PRINT</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
  $(document).ready(function() {

    // Get current time in UTC and apply offset for Indonesia (UTC+7)
    var now = new Date();
    // Adjust to Indonesia time (WIB = UTC+7)
    var indonesiaTime = new Date(now.getTime() + (7 * 60 * 60 * 1000)); // Adding 7 hours in milliseconds
    // Format date as YYYY-MM-DD for the 'arrival-date' input
    var formattedDate = indonesiaTime.toISOString().split('T')[0];
    $('#arrival-date').val(formattedDate);
    // Format date and time as YYYY-MM-DDTHH:mm for 'datetime-local' inputs
    var formattedDateTime = indonesiaTime.toISOString().slice(0, 16); // Get YYYY-MM-DDTHH:mm
    $('#date-weight-in').val(formattedDateTime);
    $('#date-weight-out').val(formattedDateTime);

    $('#weightOutBtn').attr('disabled', true);
    $('#weightInBtn').attr('disabled', true);
    $('#weight-in').attr('disabled', true);
    $('#weight-out').attr('disabled', true);


    $('#weightInBtn').click(function() {
      $('#weighbridgeForm').attr('action', "{{ route('transaction.weight-bridge.weightIn') }}");
      $('#weighbridgeForm').attr('method', 'POST');
      $('#weighbridgeForm').submit();
    });
    $('#weightOutBtn').click(function() {
      $('#weighbridgeForm').attr('action', "{{ route('transaction.weight-bridge.weightOut') }}");
      $('#weighbridgeForm').attr('method', 'POST');
      $('#weighbridgeForm').submit();
    });

    $('#weight-out').on('input', function() {
      var weightIn = $('#weight-in').val();
      var weightOut = $(this).val();
      if (weightOut - weightIn >= 0) {
        $('#weight-netto').val(weightOut - weightIn);
      } else {
        $('#weight-netto').val('');
      }
    });

    $('#vehicle-no').on('input', function() {
      var vehicleNo = $(this).val();
      // When vehicle number has 5 characters, make the request
      if (vehicleNo.length >= 5) {
        $.ajax({
          url: `{{ route('master-data.vehicle.details') }}`, // URL to your backend
          type: 'GET',
          data: {
            vehicle_no: vehicleNo
          },
          success: function(response) {

            if (response.status != 'success') {
              $('#vehicle-no').addClass('is-invalid');
              $('#vehicle-type').val('');
              $('#tolerance').val('');
              $('#transporter-name').val('');
              $('#weight-bridge-slip-no').val('');
              $('#weight-in').val('');
              $('#weight-in').attr('disabled', true);
              $('#weight-out').attr('disabled', true);
              $('#weightInBtn').attr('disabled', true);
            } else {
              $('#weightInBtn').attr('disabled', false);
              $('#vehicle-no').removeClass('is-invalid');
              $('#vehicle-type').val(response.data.vehicle_type);
              $('#tolerance').val(response.data.tolerance);
              $('#transporter-name').val(response.data.transporter_name);
              $('#weight-in').attr('disabled', false);
              if (response.data.status == 'RM-IN') {
                $('#weightInBtn').attr('disabled', true);
                $('#weight-in').val(response.data.weight_in);
                $('#date-weight-in').val(response.data.weight_in_date);
                $('#weight-bridge-slip-no').val(response.data.slip_no);
                $('#remark').val(response.data.remark);
                $('#weight-in').attr('disabled', true);
                $('#weight-out').attr('disabled', false);
                $('#weightOutBtn').attr('disabled', false);
              } else {
                $('#weightOutBtn').attr('disabled', true);
                $('#weight-out').attr('disabled', true)
              }
            }
          },
          error: function() {
            alert('Error fetching vehicle details.');
          }
        });
      }
    });
  });
</script>
@endsection