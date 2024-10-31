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

        // Check if the model exists
        if (!class_exists("App\\Models\\$table")) {
            return response()->json(['error' => 'Model not found.'], 404);
        }

        // Retrieve data from the specified model
        $data = app("App\\Models\\$table")::get();

        // Get the columns dynamically from the model's attributes
        $columns = (new ("App\\Models\\$table"))->getFillable(); // Assuming you're using fillable attributes

        $csvHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$table.csv\"",
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');

            // Write the column headers to the CSV
            fputcsv($file, $columns);

            // Write each row of data to the CSV
            foreach ($data as $row) {
                fputcsv($file, $row->only($columns)); // Use only the specified columns
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
