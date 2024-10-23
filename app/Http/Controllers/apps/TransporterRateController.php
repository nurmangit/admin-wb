<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransporterRateStoreRequest;
use App\Models\Area;
use App\Models\TransporterRate;
use App\Models\VehicleType;
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
    return view(
      'content.transporter-rate.edit',
      [
        "transporterRate" => TransporterRate::findOrFail($uuid),
        "vehicleTypes" => VehicleType::orderBy('ShortChar01', 'ASC')->get(),
        'areas' => Area::get(),
      ]
    );
  }

  public function update(TransporterRateStoreRequest $request, $uuid)
  {
    $transporterRate = TransporterRate::findOrFail($uuid);
    $validated = $request->validated();
    dd($validated);
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
