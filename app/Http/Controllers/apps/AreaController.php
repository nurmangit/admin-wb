<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\AreaStoreRequest;
use App\Models\Area;
use App\Models\Region;
use Illuminate\Http\Request;

class AreaController extends Controller
{
  public function index()
  {
    return view(
      'content.area.list',
      [
        'areas' => Area::get()
      ]
    );
  }

  public function view()
  {
    return view('content.area.create');
  }

  public function create()
  {
    return view(
      'content.area.create',
      [
        'regions' => Region::get(),
      ]
    );
  }

  public function edit($uuid)
  {
    return view(
      'content.area.edit',
      [
        "area" => Area::findOrFail($uuid),
        "regions" => Region::get(),
      ]
    );
  }

  public function update(AreaStoreRequest $request, $uuid)
  {
    $area = Area::findOrFail($uuid);
    $validated = $request->validated();
    $area->update($validated);
    return redirect()->route(
      'master-data.area.edit',
      ['uuid' => $uuid]
    )->with('success', 'Area updated successfully');
  }

  public function delete($uuid)
  {
    $area = Area::findOrFail($uuid);
    $area->delete();

    return redirect()->route('master-data.area.list')->with('success', 'Area deleted successfully.');
  }

  public function store(AreaStoreRequest $request)
  {
    $validated = $request->validated();
    Area::create($validated);
    return redirect()->route('master-data.area.list')->with('success', 'Area created successfully');
  }
}
