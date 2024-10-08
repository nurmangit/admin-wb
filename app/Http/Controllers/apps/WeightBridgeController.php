<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\WeightInRequest;
use App\Http\Requests\WeightOutRequest;
use App\Models\Vehicle;
use App\Models\WeightBridge;
use App\Models\WeightBridgeApproval;
use App\Utils\Generator;
use DateTime;
use Illuminate\Http\Request;

class WeightBridgeController extends Controller
{
  public function index()
  {
    $weightBridgeRawMaterial = WeightBridge::where('weight_type', 'rm')->get();
    $weightBridgeFinishGood = WeightBridge::where('weight_type', 'fg')->get();
    return view(
      'content.weight-bridge.list',
      [
        'raw_materials' => $weightBridgeRawMaterial,
        'finish_goods' => $weightBridgeFinishGood,
      ]
    );
  }

  public function view($weightBridgeUuid)
  {
    $weightBridge = WeightBridge::findOrFail($weightBridgeUuid);
    $template = $weightBridge->weight_type == 'rm' ? 'content.weight-bridge.view-receiving-material' : 'content.weight-bridge.view-finish-good';
    return view(
      $template,
      [
        'weight_bridge' => $weightBridge
      ]
    );
  }

  public function receivingMaterial()
  {
    $slipNo = Generator::generateSlipNo('RM');
    return view(
      'content.weight-bridge.receiving-material',
      [
        'slip_no' => '',
      ]
    );
  }
  public function weightIn(WeightInRequest $request)
  {
    $validated = $request->validated();

    $vehicle = Vehicle::where('register_number', $validated['vehicle_no'])->where('status', 'active')->first();

    if (!$vehicle) {
      return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Details: Vehicle No:' . $validated['vehicle_no'] . ' not active.');
    }
    $isApprovalExist = WeightBridge::where('vehicle_uuid', $vehicle->uuid)->where('status', 'WAITING FOR APPROVAL')->orderBy('created_at', 'DESC')->first();

    $status = strtoupper(($validated['weighing_type'] == 'fg' ? 'rm' : 'fg')) . '-IN';
    $otherProcess = WeightBridge::where('vehicle_uuid', $vehicle->uuid)->where('status', $status)->orderBy('created_at', 'DESC')->first();

    if ($isApprovalExist) {
      if ($validated['weighing_type'] == 'fg') {
        return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight IN failed. Detail: Waiting for approval with Slip no: ' . $isApprovalExist->slip_no);
      } else {
        return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Detail: Waiting for approval with Slip No: ' . $isApprovalExist->slip_no);
      }
    }

    if ($otherProcess) {
      if ($validated['weighing_type'] == 'rm') {
        return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight IN failed. Detail: Other process not finished Vehicle no: ' . $otherProcess->vehicle->register_number);
      } else {
        return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Detail: Other process not finished with Vehicle No: ' . $otherProcess->vehicle->register_number);
      }
    }
    $slipNo = Generator::generateSlipNo(strtoupper($validated['weighing_type']));
    $currentDateTime = new DateTime();
    try {
      $weightBridge = WeightBridge::create(
        [
          'slip_no' => $slipNo,
          'arrival_date' => $currentDateTime,
          'weight_type' => $validated['weighing_type'],
          'vehicle_uuid' => $vehicle->uuid,
          'weight_in' => $validated['weight_in'],
          'weight_in_date' => $currentDateTime,
          'weight_standart' => $vehicle->weight_standart,
          'weight_in_by' => 'admin',
          'remark' => $validated['remark'],
          'status' => strtoupper($validated['weighing_type']) . "-IN"
        ]
      );
    } catch (\Throwable $th) {
      if ($validated['weighing_type'] == 'rm') {
        return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight IN failed. Detail:' . $th->getMessage());
      } else {
        return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Detail:' . $th->getMessage());
      }
    }

    if ($validated['weighing_type'] == 'rm') {
      return redirect()->route('transaction.weight-bridge.receiving-material')->with('success', 'Weight IN success.');
    } else {
      return redirect()->route('transaction.weight-bridge.finish-good')->with('success', 'Weight IN success.');
    }
  }
  public function weightOut(WeightOutRequest $request)
  {
    $validated = $request->validated();

    $vehicle = Vehicle::where('register_number', $validated['vehicle_no'])->where('status', 'active')->first();
    $weightBridge = WeightBridge::where('vehicle_uuid', $vehicle->uuid)->where('status', strtoupper($validated['weighing_type']) . '-IN')->first();
    if (!$weightBridge) {
      if ($validated['weighing_type'] == 'rm') {
        return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight OUT failed. Detail: Weight IN data not found!.');
      } else {
        return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight OUT failed. Detail: Weight IN data not found!.');
      }
    }

    $currentDateTime = new DateTime();
    try {
      $weightBridge->weight_out = $validated['weight_out'];
      $weightBridge->weight_out_date = $currentDateTime;
      $weightBridge->weight_out_by = 'admin';
      if ($validated['weighing_type'] == 'rm') {
        if ($validated['weight_out'] > $weightBridge->weight_in) {
          return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight OUT failed. Detail: Weight OUT should less than Weight IN');
        }
        $weightBridge->weight_netto = $weightBridge->weight_in - $validated['weight_out'];
      } else {
        $weightBridge->weight_netto = $validated['weight_out'] - $weightBridge->weight_in;
      }
      $weightBridge->status = strtoupper($validated['weighing_type']) . '-OUT';
      if ($validated['weighing_type'] == 'fg') {
        $weightBridge->po_do = $validated['po_do'];
      }
      $weightBridge->update();
    } catch (\Throwable $th) {
      if ($validated['weighing_type'] == 'rm') {
        return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight OUT failed. Detail:' . $th->getMessage());
      } else {
        return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight OUT failed. Detail:' . $th->getMessage());
      }
    }

    if ($validated['weighing_type'] == 'fg') {
      $weightStandart = $weightBridge->vehicle->vehicle_type->weight_standart;
      $weightNetto = $validated['weight_out'] - $weightBridge->weight_in;
      $tolerance = $weightBridge->vehicle->vehicle_type->tolerance;
      $difference = ($weightNetto - $tolerance) - $weightStandart;
      $weightBridge->difference = $difference;
      $weightBridge->update();
      if (($weightNetto < $weightStandart) || ($weightNetto > $weightStandart + $tolerance)) {
        $weightBridgeApproval = WeightBridgeApproval::create(
          [
            'weight_bridge_uuid' => $weightBridge->uuid,
          ]
        );
        $weightBridge->status = 'WAITING FOR APPROVAL';
        $weightBridge->update();
        return redirect()->route('transaction.weight-bridge.finish-good')->with('warning', 'Weight OUT need approval.');
      }
    }

    if ($validated['weighing_type'] == 'rm') {
      return redirect()->route('transaction.weight-bridge.receiving-material')->with('success', 'Weight OUT success.');
    } else {
      return redirect()->route('transaction.weight-bridge.finish-good')->with('success', 'Weight OUT success.');
    }
  }
  public function finishGood()
  {

    $slipNo = Generator::generateSlipNo('FG');

    return view(
      'content.weight-bridge.finish-good',
      [
        'slip_no' => ''
      ]
    );
  }

  public function update()
  {
    return view('content.weight-bridge.create');
  }

  public function delete()
  {
    return view('content.weight-bridge.create');
  }
}
