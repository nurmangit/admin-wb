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

        $headerColumns = $columns;

        foreach ($columns as $column) {
            if (str_contains($column, '_uuid')) {
                // Modify column name
                $relatedModelName = str_replace('_uuid', '', $column);
                $tempRelatedModelName = $relatedModelName;
                $relatedModelName = str_replace('_', ' ', $relatedModelName);
                $relatedModelName = ucwords($relatedModelName);
                $relatedModelName = str_replace(' ', '', $relatedModelName);

                // Check if related model exists
                $relatedModel = "App\\Models\\$relatedModelName";
                if (class_exists($relatedModel)) {
                    $relatedColumns = (new $relatedModel)->getFillable();

                    // Merge unique columns only
                    foreach ($relatedColumns as $relatedColumn) {
                        $qualifiedColumn = $tempRelatedModelName . '_' . $relatedColumn; // Prefix with table name
                        if (!in_array($qualifiedColumn, $headerColumns)) {
                            $headerColumns[] = strtolower($qualifiedColumn);
                        }
                    }
                }
            }
        }

        $csvHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$table.csv\"",
        ];

        $csvData = [];
        foreach ($data as $row) {
            $tempData = $row->only($columns);
            foreach ($columns as $column) {
                if (str_contains($column, '_uuid')) {
                    // Modify column name
                    $relatedModelName = str_replace('_uuid', '', $column);
                    if (method_exists($row, $relatedModelName)) {
                        $relatedColumns = $row->$relatedModelName?->getFillable() ?? [];
                        foreach ($relatedColumns as $relatedColumn) {
                            $qualifiedColumn = $relatedModelName . '_' . $relatedColumn; // Prefix with table name
                            if (in_array($qualifiedColumn, $headerColumns)) {
                                $tempData[strtolower($qualifiedColumn)] = $row->$relatedModelName->$relatedColumn;
                            }
                        }
                    }
                }
            }
            $csvData[] = $tempData;
        }

        $callback = function () use ($csvData, $headerColumns) {
            $file = fopen('php://output', 'w');

            // Write the column headers to the CSV
            fputcsv($file, $headerColumns);

            // Write each row of data to the CSV
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $csvHeaders);
    }

    public function import(Request $request)
    {
        $table = $request->input('table');

        // Check if the model exists
        if (!class_exists("App\\Models\\$table")) {
            return back()->withErrors(['table' => 'Model not found.']);
        }

        $model = app("App\\Models\\$table");

        // Get the fillable columns from the model
        $columns = $model->getFillable();

        // Check if a file was uploaded
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Open the uploaded CSV file
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $header = fgetcsv($handle);

                // Validate that CSV header matches model columns (including related models if applicable)
                $validatedColumns = [];
                foreach ($header as $column) {
                    if (in_array($column, $columns)) {
                        $validatedColumns[] = $column;
                    } elseif (str_contains($column, '_uuid')) {
                        // Handle related UUID columns by identifying and including related model's fillable attributes
                        $relatedModelName = str_replace('_uuid', '', $column);
                        $relatedModelClass = "App\\Models\\" . ucwords(str_replace('_', '', $relatedModelName));
                        if (class_exists($relatedModelClass)) {
                            $relatedColumns = (new $relatedModelClass)->getFillable();
                            foreach ($relatedColumns as $relatedColumn) {
                                if (strtolower($relatedModelName . '_' . $relatedColumn) === strtolower($column)) {
                                    $validatedColumns[] = $column;
                                }
                            }
                        }
                    }
                }

                if (empty($validatedColumns)) {
                    return back()->withErrors(['file' => 'CSV file columns do not match the model attributes.']);
                }

                // Process each row in the CSV
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $rowData = array_combine($validatedColumns, $data);

                    // Separate related model data for UUID columns
                    foreach ($rowData as $column => $value) {
                        if (str_contains($column, '_uuid')) {
                            $relatedModelName = str_replace('_uuid', '', $column);
                            $relatedModelClass = "App\\Models\\" . ucwords(str_replace('_', '', $relatedModelName));
                            if (class_exists($relatedModelClass)) {
                                // Find or create related model instance
                                $relatedInstance = $relatedModelClass::firstOrCreate([$column => $value]);
                                $rowData[$column] = $relatedInstance->id; // Assign the related model ID to main table
                            }
                        }
                    }

                    // Insert or update data in the main table
                    DB::table($table)->insert($rowData);
                }
                fclose($handle);

                return back()->with('success', 'Data Imported Successfully');
            }

            return back()->withErrors(['file' => 'Could not open the CSV file.']);
        }

        return back()->withErrors(['file' => 'Please upload a valid CSV file.']);
    }
}
