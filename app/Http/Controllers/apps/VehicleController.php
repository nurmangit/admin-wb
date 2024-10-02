<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleStoreRequest;
use App\Models\Transporter;
use App\Models\TransporterRate;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\WeightBridge;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
  public function index()
  {
    return view(
      'content.vehicle.list',
      [
        'vehicles' => Vehicle::get()
      ]
    );
  }

  public function view()
  {
    return view('content.vehicle.create');
  }

  public function create()
  {
    return view(
      'content.vehicle.create',
      [
        'vehicleTypes' => VehicleType::get(),
        'transporterRates' => TransporterRate::get(),
        'transporters' => Transporter::get(),
      ]
    );
  }

  public function update(VehicleStoreRequest $request, $uuid)
  {
    $vehicle = Vehicle::findOrFail($uuid);
    $validated = $request->validated();
    $vehicle->update($validated);
    return redirect()->route(
      'master-data.vehicle.edit',
      ['uuid' => $uuid]
    )->with('success', 'Vehicle updated successfully');
  }

  public function edit($uuid)
  {
    return view(
      'content.vehicle.edit',
      [
        "vehicle" => Vehicle::findOrFail($uuid),
        'vehicleTypes' => VehicleType::get(),
        'transporterRates' => TransporterRate::get(),
        'transporters' => Transporter::get(),
      ]
    );
  }

  public function delete($uuid)
  {
    $vehicle = Vehicle::findOrFail($uuid);
    $vehicle->delete();

    return redirect()->route('master-data.vehicle.list')->with('success', 'Vehicle deleted successfully.');
  }

  public function store(VehicleStoreRequest $request)
  {
    $validated = $request->validated();
    Vehicle::create($validated);
    return redirect()->route('master-data.vehicle.list')->with('success', 'Vehicle created successfully');
  }

  public function getVehicleDetails(Request $request)
  {
    $vehicleNo = $request->input('vehicle_no');

    $vehicle = Vehicle::where('register_number', $vehicleNo)->first();
    if (!$vehicle) {
      return response()->json([
        "status" => "failed",
        "message" => "Data Not Found!",
        "data" => []
      ]);
    }

    $weightBridge = WeightBridge::where('vehicle_uuid', $vehicle->uuid)->where('status', 'RM-IN')->first();
    if (!$weightBridge) {
      $weightBridge = WeightBridge::where('vehicle_uuid', $vehicle->uuid)->where('status', 'FG-IN')->first();
    }

    // Fetch the vehicle details from the database or any other source
    // Example data returned:
    $vehicleDetails = [
      "status" => "success",
      "message" => "Data Found!",
      "data" => [
        'vehicle_type' => $vehicle->vehicle_type->name,
        'tolerance' => $vehicle->vehicle_type->tolerance,
        'weight_standart' => $vehicle->vehicle_type->weight_standart,
        'transporter_name' => $vehicle->transporter->name,
        'slip_no' => $weightBridge?->slip_no,
        'weight_in' => $weightBridge?->weight_in,
        'weight_in_date' => $weightBridge?->weight_in_date,
        'remark' => $weightBridge?->remark,
        'status' => $weightBridge?->status
      ]
    ];

    return response()->json($vehicleDetails);
  }
}
