<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransporterRateStoreRequest;
use App\Models\Area;
use App\Models\TransporterRate;
use App\Models\VehicleType;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class TransporterRateController extends Controller
{
  public function index()
  {
    return view(
      'content.transporter-rate.list',
      [
        'transporterRates' => TransporterRate::get()
      ]
    );
  }

  public function view()
  {
    return view('content.transporter-rate.create');
  }

  public function create()
  {
    return view(
      'content.transporter-rate.create',
      [
        'areas' => Area::get(),
        "vehicleTypes" => VehicleType::orderBy('ShortChar01', 'ASC')->get(),
      ]
    );
  }

  public function edit($uuid)
  {
    $transporterRate = TransporterRate::findOrFail($uuid);
    $startDate = str_replace(':AM', ' AM', $transporterRate->start_date);
    $startDate = str_replace(':PM', ' PM', $startDate);

    $startDate = Carbon::parse($startDate);
    $formatedStartDate = $startDate->format('d/m/Y');

    $endDate = str_replace(':AM', ' AM', $transporterRate->end_date);
    $endDate = str_replace(':PM', ' PM', $endDate);

    $endDate = Carbon::parse($endDate);
    $formatedEndDate = $endDate->format('d/m/Y');
    return view(
      'content.transporter-rate.edit',
      [
        "transporterRate" => TransporterRate::findOrFail($uuid),
        "vehicleTypes" => VehicleType::orderBy('ShortChar01', 'ASC')->get(),
        'formatedStartDate' => $formatedStartDate,
        'formatedEndDate' => $formatedEndDate,
        'areas' => Area::get(),
      ]
    );
  }

  public function update(TransporterRateStoreRequest $request, $uuid)
  {
    $transporterRate = TransporterRate::findOrFail($uuid);
    $validated = $request->validated();
    $transporterRate->update($validated);
    return redirect()->route(
      'master-data.transporter-rate.edit',
      ['uuid' => $uuid]
    )->with('success', 'TransporterRate updated successfully');
  }

  public function delete($uuid)
  {
    $transporterRate = TransporterRate::findOrFail($uuid);
    $transporterRate->delete();

    return redirect()->route('master-data.transporter-rate.list')->with('success', 'TransporterRate deleted successfully.');
  }

  public function store(TransporterRateStoreRequest $request)
  {
    $validated = $request->validated();
    TransporterRate::create($validated);
    return redirect()->route('master-data.transporter-rate.list')->with('success', 'TransporterRate created successfully');
  }
}
