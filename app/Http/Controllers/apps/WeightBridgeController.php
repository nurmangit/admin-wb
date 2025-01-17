<?php

namespace App\Http\Controllers\apps;

use PDO;
use DateTime;
use Carbon\Carbon;
use App\Models\Area;
use App\Models\Device;
use App\Models\Vehicle;
use App\Utils\Generator;
use App\Models\Transporter;
use App\Models\WeightBridge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\WeightBridgeApproval;
use App\Http\Requests\WeightInRequest;
use App\Http\Requests\WeightOutRequest;
use App\Models\VehicleType;
use PDF;

class WeightBridgeController extends Controller
{
    public function index()
    {
        $weightBridgeRawMaterial = WeightBridge::where('ShortChar01', 'rm')->get();
        $weightBridgeFinishGood = WeightBridge::where('ShortChar01', 'fg')->get();
        return view('content.weight-bridge.list', [
            'raw_materials' => $weightBridgeRawMaterial,
            'finish_goods' => $weightBridgeFinishGood,
        ]);
    }

    public function view($weightBridgeUuid)
    {
        $weightBridge = WeightBridge::findOrFail($weightBridgeUuid);
        $currentDateTime = new DateTime();
        $currentDateTime = $currentDateTime->format('Y-m-d H:i');
        $template =
            $weightBridge->weight_type == 'rm'
            ? 'content.weight-bridge.view-receiving-material'
            : 'content.weight-bridge.view-finish-good';
        return view($template, [
            'weight_bridge' => $weightBridge,
            'currentDateTime' => $currentDateTime
        ]);
    }

    public function receivingMaterial()
    {
        $slipNo = Generator::generateSlipNo('RM');
        $currentDateTime = new DateTime();
        $currentDateTime = $currentDateTime->format('Y-m-d H:i');
        return view('content.weight-bridge.receiving-material', [
            'slip_no' => '',
            'currentDateTime' => $currentDateTime
        ]);
    }
    public function weightIn(WeightInRequest $request)
    {
        $validated = $request->validated();
        $weightInputType = $request->query->get('type');
        $status = strtoupper($validated['weighing_type'] == 'fg' ? 'rm' : 'fg') . '-IN';
        $vehicle = null;
        switch ($validated['weighing_type']) {
            case 'fg':
                $vehicle = Vehicle::where('Character01', $validated['vehicle_no'])
                    ->where('ShortChar02', 'active')
                    ->first();
                if (!$vehicle) {
                    return redirect()
                        ->route('transaction.weight-bridge.finish-good')
                        ->with('error', 'Weight IN failed. Details: Vehicle No:' . $validated['vehicle_no'] . ' not active.');
                }
                $isApprovalExist = WeightBridge::where('Key2', $vehicle->uuid)
                    ->where('ShortChar01', 'WAITING FOR APPROVAL')
                    ->orderBy('Date04', 'DESC')
                    ->first();
                $otherProcess = WeightBridge::where('Key2', $vehicle->uuid)
                    ->where('ShortChar01', $status)
                    ->orderBy('Date04', 'DESC')
                    ->first();

                break;

            default:
                $isApprovalExist = WeightBridge::where('Character08', $validated['vehicle_no'])
                    ->where('ShortChar01', 'WAITING FOR APPROVAL')
                    ->orderBy('Date04', 'DESC')
                    ->first();
                $otherProcess = WeightBridge::where('Character08', $validated['vehicle_no'])
                    ->where('ShortChar01', $status)
                    ->orderBy('Date04', 'DESC')
                    ->first();
                break;
        }

        if ($isApprovalExist) {
            if ($validated['weighing_type'] == 'rm') {
                return redirect()
                    ->route('transaction.weight-bridge.receiving-material')
                    ->with('error', 'Weight IN failed. Detail: Waiting for approval with Slip no: ' . $isApprovalExist->slip_no);
            } else {
                return redirect()
                    ->route('transaction.weight-bridge.finish-good')
                    ->with('error', 'Weight IN failed. Detail: Waiting for approval with Slip No: ' . $isApprovalExist->slip_no);
            }
        }

        if ($otherProcess) {
            if ($validated['weighing_type'] == 'rm') {
                return redirect()
                    ->route('transaction.weight-bridge.receiving-material')
                    ->with('error', 'Weight IN failed. Detail: Other process not finished Vehicle no: ' . $otherProcess->slip_no);
            } else {
                return redirect()
                    ->route('transaction.weight-bridge.finish-good')
                    ->with(
                        'error',
                        'Weight IN failed. Detail: Other process not finished with Vehicle No: ' . $otherProcess->slip_no
                    );
            }
        }
        $slipNo = Generator::generateSlipNo(strtoupper($validated['weighing_type']));
        $currentDateTime = new DateTime();
        $currentDateTime = $currentDateTime->format('Y-m-d H:i:s');

        DB::beginTransaction();
        try {
            // Get the secret from the environment variable
            $secret = env('DEVICE_SECRET');
            // Find the device using the secret, or fail if not found
            $device = Device::where('Character02', $secret)->first();
            if (!$device) {
                if ($validated['weighing_type'] == 'rm') {
                    return redirect()
                        ->route('transaction.weight-bridge.receiving-material')
                        ->with('error', 'Weight IN failed. Detail: Device not found!');
                } else {
                    return redirect()
                        ->route('transaction.weight-bridge.finish-good')
                        ->with('error', 'Weight IN failed. Detail:  Detail:  Device not found!');
                }
            }

            if ($weightInputType == 'auto') {
                if ($device->status != 'stable') {
                    if ($validated['weighing_type'] == 'rm') {
                        return redirect()
                            ->route('transaction.weight-bridge.receiving-material')
                            ->with('error', 'Weight IN failed. Detail: Weight Unstable');
                    } else {
                        return redirect()
                            ->route('transaction.weight-bridge.finish-good')
                            ->with('error', 'Weight IN failed. Detail:  Detail: Weight Unstable');
                    }
                }
            }

            $weightBridge = WeightBridge::create([
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
                'status' => strtoupper($validated['weighing_type']) . '-IN',
                'transporter_name' => $vehicle?->transporter?->name ?? '',
            ]);
            $device->current_weight = 0;
            $device->previous_weight = 0;
            $device->status = 'unstable';
            $device->used_at = $currentDateTime;
            $device->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($validated['weighing_type'] == 'rm') {
                return redirect()
                    ->route('transaction.weight-bridge.receiving-material')
                    ->with('error', 'Weight IN failed. Detail:' . $th->getMessage());
            } else {
                return redirect()
                    ->route('transaction.weight-bridge.finish-good')
                    ->with('error', 'Weight IN failed. Detail:' . $th->getMessage());
            }
        }

        if ($validated['weighing_type'] == 'rm') {
            return redirect()
                ->route('transaction.weight-bridge.receiving-material')
                ->with('vehicle_no', $validated['vehicle_no'])
                ->with('success', 'Weight IN success.');
        } else {
            return redirect()
                ->route('transaction.weight-bridge.finish-good')
                ->with('vehicle_no', $validated['vehicle_no'])
                ->with('success', 'Weight IN success.');
        }
    }

    public function weightOut(WeightOutRequest $request)
    {
        $weightInputType = $request->query->get('type');
        $validated = $request->validated();
        $status = strtoupper($validated['weighing_type'] == 'fg' ? 'rm' : 'fg') . '-IN';
        $vehicle = null;
        switch ($validated['weighing_type']) {
            case 'fg':
                $vehicle = Vehicle::where('Character01', $validated['vehicle_no'])
                    ->where('ShortChar02', 'active')
                    ->first();
                $weightBridge = WeightBridge::where('Key2', $vehicle->uuid)
                    ->where('ShortChar02', strtoupper($validated['weighing_type']) . '-IN')
                    ->first();
                $otherProcess = WeightBridge::where('Key2', $vehicle->uuid)
                    ->where('ShortChar01', $status)
                    ->orderBy('Date04', 'DESC')
                    ->first();
                break;

            default:
                $weightBridge = WeightBridge::where('Character08', $validated['vehicle_no'])
                    ->where('ShortChar02', strtoupper($validated['weighing_type']) . '-IN')
                    ->first();
                $otherProcess = WeightBridge::where('Character08', $validated['vehicle_no'])
                    ->where('ShortChar01', $status)
                    ->orderBy('Date04', 'DESC')
                    ->first();
                break;
        }

        if ($otherProcess) {
            if ($validated['weighing_type'] == 'rm') {
                return redirect()
                    ->route('transaction.weight-bridge.receiving-material')
                    ->with(
                        'error',
                        'Weight OUT failed. Detail: Other process not finished Vehicle no: ' . $otherProcess->slip_no
                    );
            } else {
                return redirect()
                    ->route('transaction.weight-bridge.finish-good')
                    ->with(
                        'error',
                        'Weight OUT failed. Detail: Other process not finished with Vehicle No: ' . $otherProcess->slip_no
                    );
            }
        }
        if (!$weightBridge) {
            if ($validated['weighing_type'] == 'rm') {
                return redirect()
                    ->route('transaction.weight-bridge.receiving-material')
                    ->with('error', 'Weight OUT failed. Detail: Weight IN data not found!.');
            } else {
                return redirect()
                    ->route('transaction.weight-bridge.finish-good')
                    ->with('error', 'Weight OUT failed. Detail: Weight IN data not found!.');
            }
        }

        $currentDateTime = new DateTime();
        $currentDateTime = $currentDateTime->format('Y-m-d H:i:s');

        DB::beginTransaction();
        try {
            $weightBridge->weight_out = $validated['weight_out'];
            $weightBridge->weight_out_date = $currentDateTime;
            $weightBridge->weight_out_by = 'admin';
            if ($validated['weighing_type'] == 'rm') {
                if ($validated['weight_out'] > $weightBridge->weight_in) {
                    return redirect()
                        ->route('transaction.weight-bridge.receiving-material')
                        ->with('error', 'Weight OUT failed. Detail: Weight OUT should less than Weight IN');
                }
                $weightBridge->weight_netto = $weightBridge->weight_in - $validated['weight_out'];
            } else {
                $weightBridge->weight_netto = $validated['weight_out'] - $weightBridge->weight_in;
            }
            $weightBridge->status = strtoupper($validated['weighing_type']) . '-OUT';
            if ($validated['weighing_type'] == 'rm') {
                $weightBridge->po_do = $validated['po_do'] ?? '';
            }

            // Get the secret from the environment variable
            $secret = env('DEVICE_SECRET');
            // Find the device using the secret, or fail if not found
            $device = Device::where('Character02', $secret)->first();
            if (!$device) {
                if ($validated['weighing_type'] == 'rm') {
                    return redirect()
                        ->route('transaction.weight-bridge.receiving-material')
                        ->with('error', 'Weight IN failed. Detail: Device not found!');
                } else {
                    return redirect()
                        ->route('transaction.weight-bridge.finish-good')
                        ->with('error', 'Weight IN failed. Detail:  Detail:  Device not found!');
                }
            }
            if ($weightInputType == 'auto') {
                if ($device->status != 'stable') {
                    if ($validated['weighing_type'] == 'rm') {
                        return redirect()
                            ->route('transaction.weight-bridge.receiving-material')
                            ->with('error', 'Weight Out failed. Detail: Weight Unstable');
                    } else {
                        return redirect()
                            ->route('transaction.weight-bridge.finish-good')
                            ->with('error', 'Weight Out failed. Detail:  Detail: Weight Unstable');
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

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($validated['weighing_type'] == 'rm') {
                return redirect()
                    ->route('transaction.weight-bridge.receiving-material')
                    ->with('error', 'Weight OUT failed. Detail:' . $th->getMessage());
            } else {
                return redirect()
                    ->route('transaction.weight-bridge.finish-good')
                    ->with('error', 'Weight OUT failed. Detail:' . $th->getMessage());
            }
        }

        if ($validated['weighing_type'] == 'fg') {
            $weightStandart = $weightBridge->vehicle->vehicle_type->weight_standart;
            $weightNetto = $validated['weight_out'] - $weightBridge->weight_in;
            $tolerance = $weightBridge->vehicle->vehicle_type->tolerance;
            $totalWeight = DB::select("
            SELECT SUM(T2.TotalNetWeight) AS TotalWeight
            FROM ShipHead AS T1
            LEFT JOIN ShipDtl AS T2 ON T1.PackNum = T2.PackNum AND T1.Company = T2.Company
            WHERE T1.NoDokumen_c = :slipNo
        ", ['slipNo' => $weightBridge->slip_no]);
            $difference = abs($weightNetto - ($totalWeight[0]->TotalWeight ?? 0));
            $weightBridge->difference = $difference;
            $weightBridge->transporter_name = $vehicle->transporter?->name ?? '';
            $weightBridge->update();

            // New logic for approval
            // Standard Weight Per Tile
            // $stdWeightPerTile = 12;

            // $qtyTileDo = $weightStandart / $stdWeightPerTile;
            // $anomaly = $weightNetto / $weightStandart; // C

            // $stdWeightTiles = $stdWeightPerTile * $qtyTileDo;

            // $distWeight = $anomaly * $stdWeightTiles;

            // $avgDistWeight = $distWeight / $qtyTileDo;

            // if ($avgDistWeight != $stdWeightPerTile) {
            //     WeightBridgeApproval::create([
            //         'weight_bridge_uuid' => $weightBridge->uuid,
            //     ]);

            //     $weightBridge->status = 'WAITING FOR APPROVAL';
            //     $weightBridge->update();
            //     return redirect()
            //         ->route('transaction.weight-bridge.finish-good')
            //         ->with('warning', 'Weight OUT need approval.');
            // }
            if ($difference > $tolerance) {
                $weightBridgeApproval = WeightBridgeApproval::create([
                    'weight_bridge_uuid' => $weightBridge->uuid,
                ]);
                $weightBridge->status = 'WAITING FOR APPROVAL';
                $weightBridge->update();
                return redirect()
                    ->route('transaction.weight-bridge.finish-good')
                    ->with('warning', 'Weight OUT need approval.');
            }
        }

        if ($validated['weighing_type'] == 'rm') {
            return redirect()
                ->route('transaction.weight-bridge.printSlip', $weightBridge->uuid);
        } else {
            return redirect()
                ->route('transaction.weight-bridge.printSlip', $weightBridge->uuid);
        }
    }
    public function finishGood()
    {
        $slipNo = Generator::generateSlipNo('FG');
        $currentDateTime = new DateTime();
        $currentDateTime = $currentDateTime->format('Y-m-d H:i');
        return view('content.weight-bridge.finish-good', [
            'slip_no' => '',
            'currentDateTime' => $currentDateTime
        ]);
    }

    public function update()
    {
        return view('content.weight-bridge.create');
    }

    public function transporterReport(Request $request)
    {
        $query = "
          SELECT DISTINCT
              T1.TranDocTypeID AS KodeSPB,
              T1.LegalNumber AS DoNo,
              T1.ShipDate AS 'date',
              T1.VehicleNo_c AS PlateNo,
              T1.VehicleType_c AS VehicleGroup,
              T1.WBArea_c AS Area,
              SUM(T2.OurInventoryShipQty) AS Quantity,
              T1.NoDokumen_c AS WbDoc,
              SUM(T2.TotalNetWeight) AS StdWeight,
              T4.Number03,
              T4.Number05 AS Weight,
              T9.Number02 AS Rate,
              (T4.Number03 * T9.Number02) AS Amount,
              T1.ReffCity_c AS TransporterName,
              T1.KwitnsiNo_c AS Kwitansi_NO,
              CASE
            WHEN T1.TranDocTypeID LIKE 'PK.%'
            OR T1.TranDocTypeID LIKE 'PF.%' THEN 'KANMURI'
            WHEN T1.TranDocTypeID LIKE 'LG.%' THEN 'GRACEWOOD'
            ELSE 'Produk Lain'
            END AS Produk
          FROM
              ShipHead T1
          INNER JOIN
              ShipDtl T2 ON T1.PackNum = T2.PackNum
          LEFT JOIN
              Ice.UD100 T4 ON T1.NoDokumen_c = T4.Character01
          LEFT JOIN
              Ice.UD101A T5 ON T1.VehicleNo_c = T5.Character01
          LEFT JOIN
              Ice.UD101 T6 ON T1.VehicleType_c = T6.Character01
          LEFT JOIN
              Ice.UD102 T7 ON T1.ReffCity_c = T7.Character01
          LEFT JOIN
              Ice.UD103A T8 ON T1.WBArea_c = T8.Character01
          LEFT JOIN
              Ice.UD102A T9 ON T8.Key1 = T9.Key2 AND T9.ChildKey1 = T6.Key1
          WHERE
              T1.ReadyToInvoice = 1
              AND T1.Company = 'KMP'
              AND T1.NoDokumen_c <> ''
        ";

        // filter by date
        if ($request->get('period_from') != null && $request->get('period_to') != null) {
            $query .= "AND T1.ShipDate BETWEEN '" . $request->period_from . "' AND '" . $request->period_to . "'";
        }

        // filter by transporter
        if ($request->get('transporter') != null) {
            $transporterArr = $request->get('transporter');
            $transporterStr = '';
            foreach ($transporterArr as $value) {
                if ($value) {
                    $transporterStr .= "'$value',";
                }
            }
            if ($transporterStr != '') {
                $query .= "AND T7.Key1 IN (" . rtrim($transporterStr, ',') . ")";
            }
        }

        // filter by area
        if ($request->get('area') != null) {
            $areaArr = $request->get('area');
            $areaStr = '';
            foreach ($areaArr as $value) {
                if ($value) {
                  $areaStr .= "'" . strtolower($value) . "',";
                }
            }
            if ($areaStr != '') {
              $query .= "AND LOWER(T1.WBArea_c) in(" . rtrim($areaStr, ',') . ")";
            }
        }

        // filter by vehicle group
        if ($request->get('vehicle_group') != null) {
            $vehicleGroupArr = $request->get('vehicle_group');
            $vehicleGroupStr = '';
            foreach ($vehicleGroupArr as $value) {
                if ($value) {
                    $vehicleGroupStr .= "'$value',";
                }
            }
            if ($vehicleGroupStr != '') {
                $query .= "AND T6.Key1 in(" . rtrim($vehicleGroupStr, ',') . ")";
            }
        }

        // filter by register number
        if ($request->get('do_number') != null) {
            $doNumberArr = $request->get('do_number');
            $doNumberStr = '';
            foreach ($doNumberArr as $value) {
                if ($value) {
                    $doNumberStr .= "'$value',";
                }
            }
            if ($doNumberStr != '') {
                $query .= "AND  T1.LegalNumber in(" . rtrim($doNumberStr, ',') . ")";
            }
        }

        $query .= "GROUP BY
                  T1.TranDocTypeID,
                  T1.LegalNumber,
                  T1.ShipDate,
                  T1.NoDokumen_c,
                  T1.VehicleNo_c,
                  T1.VehicleType_c,
                  T1.ReffCity_c,
                  T1.WBArea_c,
                  T4.Number03,
                  T4.Number05,
                  T9.Number02,
                  T1.KwitnsiNo_c";

        $query .= " ORDER BY T1.ReffCity_c ASC, T1.ShipDate DESC";

        $spbDetails = DB::select($query);
        $data = [];
        if ($spbDetails) {
            foreach ($spbDetails as $spbDetail) {
                $data[$spbDetail->TransporterName][] = $spbDetail;
            }
        }
        $isMultipleTransporter = false;
        if (count($data) > 1) {
            $isMultipleTransporter = true;
        }
        if ($request->get('export') == 'PDF') {
            $pdf = PDF::loadView('content.weight-bridge.print.report', [
                "reports" => $data,
                "transporters" => Transporter::all(),
                "areas" => Area::select('Key1', 'Character01')->get(),
                "vehicles" => Vehicle::select('Key1', 'Character01')->get(),
                "vehicle_types" => VehicleType::select('Key1', 'Character01')->get(),
                "is_multi_transporter" => $isMultipleTransporter
            ])->setPaper('a4', 'landscape'); // Set paper size to A4 and orientation to landscape

            // Output the PDF for download or inline view
            return $pdf->stream("report.pdf");
        }

        return view(
            'content.weight-bridge.report',
            [
                "reports" => $data,
                "transporters" => Transporter::all(),
                "areas" => Area::select('Key1', 'Character01')->get(),
                "vehicles" => Vehicle::select('Key1', 'Character01')->get(),
                "vehicle_types" => VehicleType::select('Key1', 'Character01')->get(),
                "is_multi_transporter" => $isMultipleTransporter
            ]
        );
    }

    public function delete()
    {
        return view('content.weight-bridge.create');
    }
}
