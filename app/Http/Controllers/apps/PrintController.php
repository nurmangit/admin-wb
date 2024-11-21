<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\WeightBridge;
use App\Models\WeightBridgeApproval;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use PDF;

class PrintController extends Controller
{
  public function generateSlipPDF($uuid)
  {
    // Retrieve the data for the slip (replace with actual query)
    $slip = WeightBridge::findOrFail($uuid);

    $spbDetails = DB::select("
    SELECT T1.LegalNumber, T2.TotalNetWeight
    FROM ShipHead AS T1
    LEFT JOIN ShipDtl AS T2 ON T1.PackNum = T2.PackNum AND T1.Company = T2.Company
    WHERE T1.NoDokumen_c = :slipNo
", ['slipNo' => $slip->slip_no]);

    $totalWeight = DB::select("
    SELECT SUM(T2.TotalNetWeight) AS TotalWeight 
    FROM ShipHead AS T1
    LEFT JOIN ShipDtl AS T2 ON T1.PackNum = T2.PackNum AND T1.Company = T2.Company
    WHERE T1.NoDokumen_c = :slipNo
    ", ['slipNo' => $slip->slip_no]);

    $totalWeightValue = $totalWeight[0]->TotalWeight ?? 0;
    // Define data to pass to the Blade view
    $data = [
      'slip_no' => $slip->slip_no,
      'weight_in_date' => $slip->weight_in_date,
      'vehicle_no' => $slip->vehicle_no ?? $slip->vehicle->register_number,
      'transporter_name' => $slip->transporter_name,
      'vehicle_type' => $slip->vehicle?->vehicle_type->name,
      'weight_type' => $slip->weight_type,
      'remark' => $slip->remark,
      'weight_in' => (int)$slip->weight_in,
      'weight_in_time' => Carbon::parse($slip->weight_in_date)->format('H.i'),
      'weight_in_date' => Carbon::parse($slip->weight_in_date)->format('Y-m-d'),
      'weight_out' => (int)$slip->weight_out,
      'weight_out_date' => $slip->weight_out_date,
      'weight_netto' => (int)$slip->weight_netto,
      'weight_out_time' => $slip->weight_out_date ? Carbon::parse($slip->weight_out_date)->format('H.i') : '',
      'weight_out_date' => $slip->weight_out_date ? Carbon::parse($slip->weight_out_date)->format('Y-m-d') : '',
      'weight_in_by' => $slip->weight_in_by,
      'driver_name' => $slip->vehicle?->driver_name,
      'po_do' => $slip->po_do,
      'actual_weight' => $slip->actual_weight,
      'status' => $slip->status,
      'spb_details' => $spbDetails,
      'total_weight' => $totalWeightValue
    ];

    // Load the view and pass the data
    $pdf = PDF::loadView('content.weight-bridge.print.slip', $data);

    // Set paper size (60x75 mm) and orientation
    $pdf->setPaper([0, 0, 226.77, 283.46]); // Custom size in points (60mm x 75mm)

    // Output the PDF for download or inline view
    return $pdf->stream("slip_$slip->slip_no.pdf"); // or ->download('weighbridge_slip.pdf') for direct download
  }
}
