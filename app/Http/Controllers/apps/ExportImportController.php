<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ExportImportController extends Controller
{
    public function export(Request $request)
    {
        $table = $request->input('table');       // Example: 'users'
        $columns = $request->input('columns');   // Example: ['id', 'name', 'email']

        // Fetch data from the specified table
        $data = DB::table($table)->select($columns)->get();

        // Define the CSV headers
        $csvHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$table.csv\"",
        ];

        // Open output stream to create the CSV file
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns); // Write the column headers

            // Write each row
            foreach ($data as $row) {
                fputcsv($file, array_values((array) $row));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $csvHeaders);
    }

    public function import()
    {
        return;
    }
}