<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegionStoreRequest;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
  public function index()
  {
    return view(
      'content.region.list',
      [
        'regions' => Region::get()
      ]
    );
  }

  public function view()
  {
    return view('content.region.create');
  }

  public function create()
  {
    return view('content.region.create');
  }

  public function edit($uuid)
  {
    return view(
      'content.region.edit',
      [
        "region" => Region::findOrFail($uuid)
      ]
    );
  }

  public function update(RegionStoreRequest $request, $uuid)
  {
    $region = Region::findOrFail($uuid);
    $validated = $request->validated();
    $region->update($validated);
    return redirect()->route(
      'master-data.region.edit',
      ['uuid' => $uuid]
    )->with('success', 'Region updated successfully');
  }

  public function delete($uuid)
  {
    $region = Region::findOrFail($uuid);
    $region->delete();

    return redirect()->route('master-data.region.list')->with('success', 'Region deleted successfully.');
  }

  public function store(RegionStoreRequest $request)
  {
    $validated = $request->validated();
    Region::create($validated);
    return redirect()->route('master-data.region.list')->with('success', 'Region created successfully');
  }
}
