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
      <form action="{{ route('transaction.weight-bridge.report') }}" method="GET">
        {{-- @csrf --}}
        <div class="card-body -mb-10">
          <div class="accordion" id="filterAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                  Period Filter
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne">
                <div class="accordion-body">
                  <div class="row">
                    <div class="col-md-6">
                      <label for="period_from" class="form-label">From</label>
                      <input type="date" name="period_from" class="form-control" id="period_from">
                    </div>
                    <div class="col-md-6">
                      <label for="period_to" class="form-label">To</label>
                      <input type="date" name="period_to" class="form-control" id="period_to">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                  Transporter
                </button>
              </h2>
              <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo">
                <div class="accordion-body">
                  <select class="form-select" name="transporter[]" id="transporter">
                    <option value="">Select Transporter</option>
                    @foreach ($transporters as $transporter)
                      <option value="{{ $transporter->id }}">{{ $transporter->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            {{-- Area Filter --}}
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                  Area
                </button>
              </h2>
              <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree">
                <div class="accordion-body">
                  <select name="area[]" id="area" class="form-select">
                    <option value="">Select Area</option>
                    @foreach ($areas as $area)
                      <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            {{-- Vehicle Group Filter --}}
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                  Vehicle Group
                </button>
              </h2>
              <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour">
                <div class="accordion-body">
                  <select name="vehicle_group[]" id="vehicle_group" class="form-select">
                    <option value="">Select Vehicle Group</option>
                    @foreach ($vehicles as $group)
                      <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

          </div>

          {{-- Submit Button --}}
          <button type="submit" class="btn btn-primary mt-3">Apply Filter</button>
        </div>
      </form>
      {{-- // form filter --}}
      <div class="container-fluid py-3">
        <h4 class="text-center mb-4">PT KERAMINDO MEGAH PERTIWI<br>ESTIMATED PAYMENT TO TRANSPORTER</h4>
          @foreach ($reports as $key => $report)
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <!-- Header Section -->
                <thead class="sticky-header">
                    <tr>
                        <th colspan="2">
                            <div class="row g-0">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="small">Kode Suplier</label>
                                        <input type="text" readonly
                                         value="{{ $report[0]->TransporterCode ?? '' }}"  class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="small">Nama Transporter</label>
                                        <input readonly type="text" value="{{ $key ?? '' }}" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </th>
                        <th colspan="10"></th>
                    </tr>
                    <tr class="text-center small">
                        <th>D/O NO</th>
                        <th>Date</th>
                        <th>Plate NO</th>
                        <th>Vehicle Group</th>
                        <th>Area</th>
                        <th>Quantity</th>
                        <th>WB.Doc</th>
                        <th>STD Weight (Kg)</th>
                        <th>Weight(Kg)</th>
                        <th>Var(Kg)</th>
                        <th>Rate</th>
                        <th>Amount(Rp)</th>
                    </tr>
                </thead>

                <!-- Body Section -->
                <tbody>
                    @foreach($report as $data)
                    <tr class="small">
                        <td>
                            <input type="text" class="form-control form-control-sm" value="{{ $data->DoNo ?? '' }}">
                        </td>
                        <td>
                            <input type="date" class="form-control form-control-sm" value="{{ $data->date ?? '' }}">
                        </td>
                        <td class="table-cell-yellow">
                            <input type="text" class="form-control form-control-sm bg-warning-subtle" value="{{ $data->PlateNo ?? '' }}">
                        </td>
                        <td class="table-cell-yellow">
                            {{ $data->VehicleGroup ?? '' }}
                        </td>
                        <td class="table-cell-yellow">
                            {{ $data->Area ?? '' }}
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end" value="{{ $data->Quantity ?? '' }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" value="{{ $data->WbDoc ?? '' }}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end" value="{{ $data->StdWeight ?? '' }}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end" value="{{ $data->Weight ?? '' }}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end" readonly value="{{ $data->Difference ?? '' }}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end" value="{{ $data->Rate ?? '' }}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end" readonly value="{{ $data->Amount ?? '' }}">
                        </td>
                    </tr>
                    @endforeach

                    <!-- Subtotal Row -->
                    {{-- <tr class="table-secondary fw-bold small">
                        <td colspan="5" class="text-end">Sub Total</td>
                        <td class="text-end">{{ $subtotalQuantity }}</td>
                        <td></td>
                        <td class="text-end">{{ $subtotalStdWeight }}</td>
                        <td class="text-end">{{ $subtotalWeight }}</td>
                        <td class="text-end">{{ $subtotalVar }}</td>
                        <td class="text-end">{{ $subtotalRate }}</td>
                        <td class="text-end">{{ $subtotalAmount }}</td>
                    </tr> --}}
                </tbody>

                <!-- Footer Totals -->
                <tfoot>
                    {{-- <tr class="table-dark fw-bold small">
                        <td colspan="5" class="text-end">Total</td>
                        <td class="text-end">{{ $totalQuantity }}</td>
                        <td></td>
                        <td class="text-end">{{ $totalStdWeight }}</td>
                        <td class="text-end">{{ $totalWeight }}</td>
                        <td class="text-end">{{ $totalVar }}</td>
                        <td class="text-end">{{ $totalRate }}</td>
                        <td class="text-end">{{ $totalAmount }}</td>
                    </tr> --}}
                </tfoot>
            </table>
          </div>
          @endforeach
      </div>
      {{-- <div class="table-responsive text-nowrap p-4">
        @dd($reports)
      </div> --}}
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
    $('#accordion').on('shown.bs.collapse', function () {
        $('#transporter').select2({
            placeholder: 'Select a transporter',
            allowClear: true,
            multiple: true
        });
    });
  });
</script>
@endsection
