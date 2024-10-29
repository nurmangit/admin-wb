<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransporterStoreRequest;
use App\Http\Requests\TransporterUpdateRequest;
use App\Models\Transporter;
use Illuminate\Http\Request;

class TransporterController extends Controller
{
  public function index()
  {
    return view(
      'content.transporter.list',
      [
        'transporters' => Transporter::get()
      ]
    );
  }

  public function view()
  {
    return view('content.transporter.create');
  }

  public function create()
  {
    return view('content.transporter.create');
  }

  public function edit($uuid)
  {
    return view(
      'content.transporter.edit',
      [
        "transporter" => Transporter::findOrFail($uuid)
      ]
    );
  }

  public function update(TransporterUpdateRequest $request, $uuid)
  {
    $transporter = Transporter::findOrFail($uuid);
    $validated = $request->validated();
    $transporter->update($validated);
    return redirect()->route(
      'master-data.transporter.edit',
      ['uuid' => $uuid]
    )->with('success', 'Transporter updated successfully');
  }

  public function delete($uuid)
  {
    $transporter = Transporter::findOrFail($uuid);
    $transporter->delete();

    return redirect()->route('master-data.transporter.list')->with('success', 'Transporter deleted successfully.');
  }

  public function store(TransporterStoreRequest $request)
  {
    $validated = $request->validated();
    Transporter::create($validated);
    return redirect()->route('master-data.transporter.list')->with('success', 'Transporter created successfully');
  }
}
