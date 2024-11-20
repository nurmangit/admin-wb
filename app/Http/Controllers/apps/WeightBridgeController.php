<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\WeightInRequest;
use App\Http\Requests\WeightOutRequest;
use App\Models\Device;
use App\Models\Vehicle;
use App\Models\WeightBridge;
use App\Models\WeightBridgeApproval;
use App\Utils\Generator;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use PDO;

class WeightBridgeController extends Controller
{
    public function index()
    {
        $weightBridgeRawMaterial = WeightBridge::where('ShortChar01', 'rm')->get();
        $weightBridgeFinishGood = WeightBridge::where('ShortChar01', 'fg')->get();
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
        $weightInputType = $request->query->get('type');
        $status = strtoupper(($validated['weighing_type'] == 'fg' ? 'rm' : 'fg')) . '-IN';
        $vehicle = null;
        switch ($validated['weighing_type']) {
            case 'fg':
                $vehicle = Vehicle::where('Character01', $validated['vehicle_no'])->where('ShortChar02', 'active')->first();
                if (!$vehicle) {
                    return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Details: Vehicle No:' . $validated['vehicle_no'] . ' not active.');
                }
                $isApprovalExist = WeightBridge::where('Key2', $vehicle->uuid)->where('ShortChar01', 'WAITING FOR APPROVAL')->orderBy('Date04', 'DESC')->first();
                $otherProcess = WeightBridge::where('Key2', $vehicle->uuid)->where('ShortChar01', $status)->orderBy('Date04', 'DESC')->first();

                break;

            default:
                $isApprovalExist = WeightBridge::where('Character08', $validated['vehicle_no'])->where('ShortChar01', 'WAITING FOR APPROVAL')->orderBy('Date04', 'DESC')->first();
                $otherProcess = WeightBridge::where('Character08', $validated['vehicle_no'])->where('ShortChar01', $status)->orderBy('Date04', 'DESC')->first();
                break;
        }

        if ($isApprovalExist) {
            if ($validated['weighing_type'] == 'rm') {
                return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight IN failed. Detail: Waiting for approval with Slip no: ' . $isApprovalExist->slip_no);
            } else {
                return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Detail: Waiting for approval with Slip No: ' . $isApprovalExist->slip_no);
            }
        }

        if ($otherProcess) {
            if ($validated['weighing_type'] == 'rm') {
                return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight IN failed. Detail: Other process not finished Vehicle no: ' . $otherProcess->slip_no);
            } else {
                return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Detail: Other process not finished with Vehicle No: ' . $otherProcess->slip_no);
            }
        }
        $slipNo = Generator::generateSlipNo(strtoupper($validated['weighing_type']));
        $currentDateTime = new DateTime();
        $currentDateTime = $currentDateTime->format('Y-m-d H:i:s');
        try {
            // Get the secret from the environment variable
            $secret = env('DEVICE_SECRET');
            // Find the device using the secret, or fail if not found
            $device = Device::where('Character02', $secret)->first();
            if (!$device) {
                if ($validated['weighing_type'] == 'rm') {
                    return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight IN failed. Detail: Device not found!');
                } else {
                    return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Detail:  Detail:  Device not found!');
                }
            }

            if ($weightInputType == "auto") {
                if ($device->status != 'stable') {
                    if ($validated['weighing_type'] == 'rm') {
                        return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight IN failed. Detail: Weight Unstable');
                    } else {
                        return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Detail:  Detail: Weight Unstable');
                    }
                }
            }

            $weightBridge = WeightBridge::create(
                [
                    'slip_no' => $slipNo,
                    'vehicle_no' => $validated['vehicle_no'],
                    'arrival_date' => $currentDateTime,
                    'weight_type' => $validated['weighing_type'],
                    'vehicle_uuid' => $vehicle?->uuid,
                    'weight_in' => $validated['weight_in'],
                    'weight_in_date' => $currentDateTime,
                    'weight_standart' => $vehicle?->vehicle_type->weight_standart ?? '0',
                    'weight_in_by' => 'admin',
                    'remark' => $validated['remark'] ?? '',
                    'status' => strtoupper($validated['weighing_type']) . "-IN",
                    'transporter_name' => $vehicle?->transporter?->name
                ]
            );
            $device->current_weight = 0;
            $device->previous_weight = 0;
            $device->status = 'unstable';
            $device->used_at = $currentDateTime;
            $device->save();
        } catch (\Throwable $th) {
            if ($validated['weighing_type'] == 'rm') {
                return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight IN failed. Detail:' . $th->getMessage());
            } else {
                return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Detail:' . $th->getMessage());
            }
        }

        if ($validated['weighing_type'] == 'rm') {
            return redirect()->route('transaction.weight-bridge.receiving-material')->with('vehicle_no', $validated['vehicle_no'])->with('success', 'Weight IN success.');
        } else {
            return redirect()->route('transaction.weight-bridge.finish-good')->with('vehicle_no', $validated['vehicle_no'])->with('success', 'Weight IN success.');
        }
    }

    public function weightOut(WeightOutRequest $request)
    {
        $weightInputType = $request->query->get('type');
        $validated = $request->validated();
        $status = strtoupper(($validated['weighing_type'] == 'fg' ? 'rm' : 'fg')) . '-IN';
        $vehicle = null;
        switch ($validated['weighing_type']) {
            case 'fg':
                $vehicle = Vehicle::where('Character01', $validated['vehicle_no'])->where('ShortChar02', 'active')->first();
                $weightBridge = WeightBridge::where('Key2', $vehicle->uuid)->where('ShortChar02', strtoupper($validated['weighing_type']) . '-IN')->first();
                $otherProcess = WeightBridge::where('Key2', $vehicle->uuid)->where('ShortChar01', $status)->orderBy('Date04', 'DESC')->first();
                break;

            default:
                $weightBridge = WeightBridge::where('Character08', $validated['vehicle_no'])->where('ShortChar02', strtoupper($validated['weighing_type']) . '-IN')->first();
                $otherProcess = WeightBridge::where('Character08', $validated['vehicle_no'])->where('ShortChar01', $status)->orderBy('Date04', 'DESC')->first();
                break;
        }

        if ($otherProcess) {
            if ($validated['weighing_type'] == 'rm') {
                return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight OUT failed. Detail: Other process not finished Vehicle no: ' . $otherProcess->slip_no);
            } else {
                return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight OUT failed. Detail: Other process not finished with Vehicle No: ' . $otherProcess->slip_no);
            }
        }
        if (!$weightBridge) {
            if ($validated['weighing_type'] == 'rm') {
                return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight OUT failed. Detail: Weight IN data not found!.');
            } else {
                return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight OUT failed. Detail: Weight IN data not found!.');
            }
        }

        $currentDateTime = new DateTime();
        $currentDateTime = $currentDateTime->format('Y-m-d H:i:s');
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
            if ($validated['weighing_type'] == 'rm') {
                $weightBridge->po_do = $validated['po_do'] ?? "";
            }

            // Get the secret from the environment variable
            $secret = env('DEVICE_SECRET');
            // Find the device using the secret, or fail if not found
            $device = Device::where('Character02', $secret)->first();
            if (!$device) {
                if ($validated['weighing_type'] == 'rm') {
                    return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight IN failed. Detail: Device not found!');
                } else {
                    return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight IN failed. Detail:  Detail:  Device not found!');
                }
            }
            if ($weightInputType == 'auto') {
                if ($device->status != 'stable') {
                    if ($validated['weighing_type'] == 'rm') {
                        return redirect()->route('transaction.weight-bridge.receiving-material')->with('error', 'Weight Out failed. Detail: Weight Unstable');
                    } else {
                        return redirect()->route('transaction.weight-bridge.finish-good')->with('error', 'Weight Out failed. Detail:  Detail: Weight Unstable');
                    }
                }
            }
            $device->current_weight = 0;
            $device->previous_weight = 0;
            $device->status = 'unstable';
            $device->used_at = $currentDateTime;
            $device->save();
            $weightBridge->remark = $validated['remark'] ?? '';
            $weightBridge->save();
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
            $difference = abs($weightNetto - $weightStandart);
            $weightBridge->difference = $difference;
            if ($vehicle->transporter?->name) {
                $weightBridge->transporter_name = $vehicle->transporter->name;
            }
            $weightBridge->update();
            if ($difference > $tolerance) {
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
