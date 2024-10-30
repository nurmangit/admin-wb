<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ExportImportController extends Controller
{
    public function export(Request $request)
    {
        $table = $request->input('table');
        $columns = $request->input('columns');

        $data = DB::table($table)->select($columns)->get();

        $csvHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$table.csv\"",
        ];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, array_values((array) $row));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $csvHeaders);
    }

    public function import(Request $request)
    {
        $table = $request->input('table');
        $columns = $request->input('columns');

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                fgetcsv($handle);

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $rowData = array_combine($columns, $data);

                    DB::table($table)->insert($rowData);
                }
                fclose($handle);
            }

            return back()->with('success', 'Data Imported Successfully');
        }

        return back()->withErrors(['file' => 'Please upload a valid CSV file.']);
    }
}