<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\WeightBridge;
use App\Models\WeightBridgeApproval;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PDF;

class DeviceController extends Controller
{
  public function create(Request $request)
  {
    // Get the weight from the request body
    $weight = $request->get('weight');

    // Get the secret from the request headers
    $secret = $request->header('secret');

    // Find the device using the secret, or fail if not found
    $device = Device::where('secret', $secret)->first();

    if (!$device) {
      // Return error response if device not found
      return response()->json(
        
        [
          "status" => "failed",
          "message" => "Device not found!"
        ],
        404
      );
    }

    // Store the previous weight and update the current weight
    $device->previous_weight = $device->current_weight;
    $device->current_weight = $weight;

    // Calculate weight difference and update status
    if (abs($device->previous_weight - $device->current_weight) <= $device->tolerance) {
      $device->status = 'stable';
    } else {
      $device->status = 'unstable';
    }

    // Save the updated device information
    $device->save();

    // Return success response
    return response()->json(
      [
        "status" => "success",
        "message" => "Device weight updated!",
      ],
      200
    );
  }

  public function detail()
  {
    // Get the secret from the environment variable
    $secret = env('DEVICE_SECRET');

    // Find the device using the secret, or fail if not found
    $device = Device::where('secret', $secret)->first();

    if (!$device) {
      // Return error response if device not found
      return response()->json(
        [
          "status" => "failed",
          "message" => "Device not found!",
          "data" => []
        ],
        404
      );
    }

    // Return success response with the device's current weight and status
    return response()->json(
      [
        "status" => "success",
        "message" => "Device details retrieved!",
        "data" => [
          "current_weight" => $device->current_weight,
          "status" => $device->status
        ]
      ],
      200
    );
  }
}
