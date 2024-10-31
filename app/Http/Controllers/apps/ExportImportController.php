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
        $request->validate([
            'table' => 'required|string',
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $table = $request->input('table');
        $redirectUrl = $request->input('redirect_url', url()->previous());

        if (!class_exists("App\\Models\\$table")) {
            return redirect($redirectUrl)->with('error', 'Model not found.');
        }

        $modelClass = "App\\Models\\$table";
        $model = new $modelClass();

        try {
            $file = $request->file('file');
            $csvData = array_map(function($line) {
                return str_getcsv($line, ';');
            }, file($file->getPathname()));

            $headers = array_map('strtolower', $csvData[0]);
            array_shift($csvData);

            $processedRows = 0;
            $errorRows = [];
            $dataToInsert = [];

            foreach ($csvData as $index => $row) {
                try {
                    $data = array_combine($headers, $row);

                    $data = array_map(function($value) {
                        $value = trim($value);
                        return $value === '' ? null : $value;
                    }, $data);

                    $dataToInsert[] = $data;

                    $processedRows++;
                } catch (\Exception $e) {
                    $errorRows[] = [
                        'row_number' => $index + 2,
                        'data' => $row,
                        'error' => $e->getMessage()
                    ];
                    \Log::error("Error processing row " . ($index + 2) . ": " . $e->getMessage());
                }
            }

            foreach ($dataToInsert as $dataRow) {
                try {
                    $modelClass::updateOrCreate(
                        ['code' => $dataRow['code']],
                        $dataRow
                    );

                    $processedRows++;
                } catch (\Exception $e) {
                    $errorRows[] = [
                        'row_number' => $processedRows + 2,
                        'data' => $dataRow,
                        'error' => $e->getMessage(),
                    ];
                    \Log::error("Error processing row {$processedRows}: {$e->getMessage()}");
                }
            }

            $message = "Successfully processed {$processedRows} rows.";
            if (count($errorRows) > 0) {
                $message .= " Failed to process " . count($errorRows) . " rows.";
                session(['import_errors' => $errorRows]);

                foreach ($errorRows as $error) {
                    \Log::error("Row {$error['row_number']} failed:", [
                        'data' => $error['data'],
                        'error' => $error['error']
                    ]);
                }
            }

            return redirect($redirectUrl)->with('success', $message);

        } catch (\Exception $e) {
            \Log::error("Import failed: " . $e->getMessage());
            return redirect($redirectUrl)->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

}

