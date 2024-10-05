<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\apps\ApprovalController;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Region;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\WeightBridgeApproval;
use Illuminate\Http\Request;

class Analytics extends Controller
{
  public function index()
  {
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
      ]
    );
  }
}
