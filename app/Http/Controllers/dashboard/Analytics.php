<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\apps\ApprovalController;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Region;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\WeightBridge;
use App\Models\WeightBridgeApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Analytics extends Controller
{
  public function index()
  {

    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    $weightOutByDates = WeightBridge::select(
      DB::raw('DATE(arrival_date) as date'),
      DB::raw('count(*) as total_weight_out')
    )
      ->whereMonth('arrival_date', $currentMonth)
      ->whereYear('arrival_date', $currentYear)
      ->whereNotNull('weight_out')
      ->groupBy('date')
      ->get()
      ->toArray();

    $weightOut = [];
    $weightOutDate = [];

    foreach ($weightOutByDates as $weightOutByDate) {
      $weightOut[] = $weightOutByDate['total_weight_out'];
      $weightOutDate[] = $weightOutByDate['date'];
    }
    return view(
      'content.dashboard.dashboards-analytics',
      [
        'user' => auth()->user(),
        'total_vehicle' => Vehicle::get()->count(),
        'total_transporter' => Transporter::get()->count(),
        'total_area' => Area::get()->count(),
        'total_region' => Region::get()->count(),
        'waiting_approval' => WeightBridgeApproval::whereNull('action_date')->get()->count(),
        'total_approved' => WeightBridgeApproval::where('is_approve', true)->get()->count(),
        'total_rejected' => WeightBridgeApproval::where('is_reject', true)->get()->count(),
        'weight_out' => $weightOut,
        'weight_out_date' => $weightOutDate
      ]
    );
  }
}
