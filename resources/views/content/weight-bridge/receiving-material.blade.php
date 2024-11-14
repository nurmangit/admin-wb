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
            </div>
            <div class="col-4">
              <!-- Weight In -->
              <div class="mb-3">
                <label class="form-label" for="weight-in">Weight In</label>
                <input type="number" class="form-control  @error('weight_in') is-invalid @enderror" name="weight_in" id="weight-in" placeholder="Enter weight in" />
                <div class="invalid-feedback" id="weight-in-feedback-invalid"></div>
                <div class="valid-feedback" id="weight-in-feedback-valid"></div>
                @error('weight_in')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <!-- Weight Out -->
              <div class="mb-3">
                <label class="form-label" for="weight-out">Weight Out</label>
                <input type="number" class="form-control  @error('weight_out') is-invalid @enderror" name="weight_out" id="weight-out" placeholder="Enter weight out" />
                <div class="invalid-feedback" id="weight-out-feedback-invalid"></div>
                <div class="valid-feedback" id="weight-out-feedback-valid"></div>
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
              @can('manual input')
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="toggle_input">
                <label class="form-check-label" for="toggle_input">
                  <span id="inputLabel">Manual Input Weight</span>
                  <i class="menu-icon tf-icons bx bx-help-circle ms-2" data-bs-toggle="tooltip" title="Toggle between automatic and manual input weight." style="cursor: pointer;"></i>
                </label>
              </div>
              @endcan
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
              <!-- Date PO / DO -->
              <div class="mb-3">
                <label class="form-label" for="po_do">PO / DO</label>
                <input type="text" class="form-control @error('po_do') is-invalid @enderror" id="po_do" disabled name="po_do" placeholder="Enter PO / DO" />
                @error('po_do')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end">
            <!-- Submit Button -->
            @can('weight_in')
            <button type="button" style="margin-right: 10px;" id="weightInBtn" class="btn btn-success fw-bold">WEIGHT IN</button>
            @endcan
            @can('weight_out')
            <button type="button" style="margin-right: 10px;" id="weightOutBtn" class="btn btn-info fw-bold">WEIGHT OUT</button>
            @endcan
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
    // Variable to store interval ID
    let fetchIntervals = [];
    let fetchType = '';
    let queryKey = 'type';
    let queryValue = '';

    function fetchDeviceDetails(type) {
      $.ajax({
        url: "{{ route('device.detail') }}", // The URL to the controller method
        method: "GET", // HTTP method
        success: function(response) {
          // Assuming your response has a 'data' field with 'current_weight' and 'status'
          if (response.status === "success") {
            // Update the value of the input field with the current weight
            $(`#weight-${type}`).val(response.data.current_weight);
            $(`#weight-${type}`).trigger('input');
            if (response.data.status == 'stable') {
              $(`#weight-${type}-feedback-valid`).text('Weight Stable');
              $(`#weight-${type}`).addClass('is-valid');
              $(`#weight-${type}`).removeClass('is-invalid');
            } else {
              $(`#weight-${type}-feedback-invalid`).text('Weight Unstable');
              $(`#weight-${type}`).removeClass('is-valid');
              $(`#weight-${type}`).addClass('is-invalid');
            }
          } else {
            // Handle failure (optional)
            $(`#weight-${type}`).val('');
            console.error(response.message || "Failed to fetch data");
          }
        },
        error: function(error) {
          $(`#weight-${type}`).val('');
          // // stopAllFetchIntervals();
          // $(`#weight-${type}-feedback-invalid`).html('<span class="text-warning">Device not connected. You can manually enter the weight.</span>');
          // $(`#weight-${type}`).removeClass('is-valid');
          // $(`#weight-${type}`).addClass('is-invalid');
          console.error("Error fetching device details:", error);
        }
      });
    }

    function stopAllFetchIntervals() {
      fetchIntervals.forEach(clearInterval);
      fetchIntervals = [];
    }

    // Toggle input between Auto and Manual
    $("#toggle_input").on('change', function() {
      if (!fetchType) {
        return;
      }

      if ($(this).is(':checked')) {
        $("#inputLabel").html('Auto Input Weight');
        queryValue = 'auto';

        // Enable Auto Input: disable manual entry and start fetching
        if (fetchType) {
          stopAllFetchIntervals();
          fetchIntervals.push(setInterval(function() {
            fetchDeviceDetails(fetchType);
          }, 2000));
        }
        if (fetchType === 'in') {
          $('#weight-in').attr('disabled', false);
          $('#weight-out').attr('disabled', true);
        } else {
          $('#weight-out').attr('disabled', false);
          $('#weight-in').attr('disabled', true);
        }
      } else {
        queryValue = 'manual';
        $("#inputLabel").html('Manual Input Weight');

        // Stop fetching and allow manual input
        stopAllFetchIntervals();
        $(`#weight-${fetchType}`).attr('disabled', false).removeClass('is-invalid');
      }
    });

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
      $('#weighbridgeForm').attr('action', "{{ route('transaction.weight-bridge.weightIn') }}" + "?" + queryKey + "=" + encodeURIComponent(queryValue));
      $('#weighbridgeForm').attr('method', 'POST');
      $('#weighbridgeForm').submit();
    });
    $('#weightOutBtn').click(function() {
      $('#weighbridgeForm').attr('action', "{{ route('transaction.weight-bridge.weightOut') }}" + "?" + queryKey + "=" + encodeURIComponent(queryValue));
      $('#weighbridgeForm').attr('method', 'POST');
      $('#weighbridgeForm').submit();
    });

    $('#weight-out').on('input', function() {
      var weightIn = $('#weight-in').val();
      var weightOut = $(this).val();
      if (weightIn - weightOut >= 0) {
        $('#weight-out').removeClass('is-invalid')
        $('#weight-netto').val(weightIn - weightOut);
      } else {
        $('#weight-netto').val('');
        $('#weight-out').addClass('is-invalid')
        $('#weight-out-feedback-invalid').text('Weight OUT should less than Weight IN')
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
            vehicle_no: vehicleNo,
            weight_type: 'RM'
          },
          success: function(response) {
            if (response.status != 'success') {
              $('#vehicle-no').addClass('is-invalid');
              $('#vehicle-type').val('');
              $('#tolerance').val('');
              $('#transporter-name').val('');
              $('#weight-bridge-slip-no').val('');
              $('#weight-in').val('').removeClass('is-invalid').attr('disabled', true);
              $('#weight-out').val('').removeClass('is-invalid').attr('disabled', true);
              $('#weight-in-feedback-invalid').text('');
              $('#weight-out-feedback-invalid').text('');
              $('#weightInBtn').attr('disabled', true);
              $('#po_do').attr('disabled', true);
              // Stop the interval if it's running
              if (fetchIntervals) {
                stopAllFetchIntervals()
              }
            } else {
              $("#toggle_input").prop("checked", true).trigger('change');
              $("#inputLabel").html('Auto Input Weight');
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
                $('#po_do').attr('disabled', false);
                // Call the fetch function every 1 second
                fetchType = 'out';
                fetchIntervals.push(setInterval(function() {
                  fetchDeviceDetails(fetchType);
                }, 2000));
              } else {
                // Call the fetch function every 1 second
                fetchType = 'in';
                fetchIntervals.push(setInterval(function() {
                  fetchDeviceDetails(fetchType);
                }, 2000));
                $('#po_do').attr('disabled', true);
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