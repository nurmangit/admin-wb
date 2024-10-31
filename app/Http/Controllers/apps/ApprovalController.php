<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\WeightBridge;
use App\Models\WeightBridgeApproval;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
  public function index()
  {
    return view(
      'content.approval.list',
      [
        'approvals' => WeightBridgeApproval::orderBy('Character01', 'DESC')->get()
      ]
    );
  }

  public function view()
  {
    return view('content.weight-bridge.create');
  }

  public function approve($approvalUuid)
  {
    $approval = WeightBridgeApproval::findOrFail($approvalUuid);
    $approval->is_approve = true;
    $approval->action_date = Carbon::now();
    $approval->action_by = 'admin';

    $weightBridge = $approval->weight_bridge;
    $weightBridge->status = 'FG-OUT';
    $approval->update();
    $weightBridge->update();
    return redirect()->route('transaction.weight-bridge.approval.list')->with('success', 'Approved. Detail: successfuly approve Slip No: ' . $approval->weight_bridge->slip_no);
  }

  public function reject($approvalUuid)
  {
    $approval = WeightBridgeApproval::findOrFail($approvalUuid);

    DB::transaction(function () use ($approvalUuid, $approval) {
      $approval->is_reject = true;
      $approval->action_date = Carbon::now();
      $approval->action_by = auth()->user()->name ?? 'admin';
      $weightBridge = $approval->weight_bridge;
      $fillable = $weightBridge->getFillable();
      $weightBridge->status = 'FG-IN';
      $newWeightBridge = [];
      foreach ($fillable as $key) {
        $newWeightBridge[$key] = $weightBridge->$key;
      }
      $weightBridge->status = 'REJECTED';
      $approval->save();
      $weightBridge->save();
      $newWeightBridge = WeightBridge::create($newWeightBridge);
    });

    return redirect()->route('transaction.weight-bridge.approval.list')
      ->with('success', 'Rejected. Detail: successfully rejected Slip No: ' . $approval->weight_bridge->slip_no);
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
