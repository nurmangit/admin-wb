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
            $tempDataUuidToRemove = [];
            foreach ($columns as $column) {
                if (str_contains($column, '_uuid')) {
                    $tempDataUuidToRemove[] = $column;
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
            foreach ($tempDataUuidToRemove as $uuidColumn) {
                unset($tempData[$uuidColumn]);
            }

            $csvData[] = $tempData;
        }

        $headerColumns = array_diff($headerColumns, $tempDataUuidToRemove);
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
        $fillableColumn = $model->getFillable();

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        try {
            $headers = fgetcsv($handle);
            if (!$headers) {
                throw new \Exception('CSV file is empty or invalid');
            }

            $headerMap = array_flip($headers);

            DB::beginTransaction();

            $processedRows = 0;
            $errorRow = 0;
            $errorRows = [];

            while (($row = fgetcsv($handle)) !== false) {
                try {
                    if (count($fillableColumn) === count($row)) {
                        $tempData = array_combine($fillableColumn, $row);

                        if (array_key_exists('code', $tempData)) {
                            if ($model::where('ShortChar01', $tempData['code'])->exists()) {
                                throw new \Exception("Record with code {$tempData['code']} already exists.");
                            }
                        }

                        foreach ($tempData as $keyData => $valData) {
                            if (str_contains($keyData, '_uuid')) {
                                // Resolve the related model class name and check if it exists
                                $relatedModel = "App\\Models\\" . ucwords(str_replace('_uuid', '', $keyData));
                                $relatedModel = str_replace('_', ' ', $relatedModel);
                                $relatedModel = ucwords($relatedModel);
                                $relatedModel = str_replace(' ', '', $relatedModel);
                                if (class_exists($relatedModel)) {
                                    // Attempt to find the related model by `ShortChar01`
                                    $tempModelValue = $relatedModel::where('ShortChar01', "$valData")->first();
                                    if ($tempModelValue) {
                                        $tempData[$keyData] = $tempModelValue->uuid;
                                    } else {
                                        throw new \Exception("Relation $relatedModel not found!. Details: $valData");
                                    }
                                }
                            }
                            if (str_contains($keyData, 'password')) {
                                $tempData[$keyData]  = bcrypt($valData);
                            }
                        }

                        $modelClass::create($tempData);
                        $processedRows++;
                    } else {
                        throw new \Exception("Mismatch between columns and values.");
                    }
                } catch (\Exception $e) {
                    $errorRow++;
                    $errorRows = [
                        'row' => $row,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            $messageSuccess = "Successfully processed {$processedRows} rows.";
            if (count($errorRows) > 0) {
                $messageError = "Failed to process " . $errorRow . " rows." . " Message: " . $errorRows['error'];
                session(['import_errors' => $errorRows]);
                return redirect($redirectUrl)->with([
                    'error' => $messageError,
                    'success' => $messageSuccess
                ]);
            }

            return redirect($redirectUrl)->with('success', $messageSuccess);
        } catch (\Exception $e) {
            return redirect($redirectUrl)->with('error', $e->getMessage());
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public function download(Request $request)
    {
        $table = $request->input('table');

        // Check if the model exists
        if (!class_exists("App\\Models\\$table")) {
            return response()->json(['error' => 'Model not found.'], 404);
        }

        // Retrieve data from the specified model
        $data = app("App\\Models\\$table")::get();

        // Get the columns dynamically from the model's attributes
        $columns = (new ("App\\Models\\$table"))->getFillable();

        $csvHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$table-import-template.csv\"",
        ];


        $headerColumns = [];
        foreach ($columns as $column) {
            if (str_contains($column, '_uuid')) {
                $headerColumns[] = str_replace('_uuid', '_code', $column);
                continue;
            }
            $headerColumns[] = $column;
        }
        $callback = function () use ($headerColumns) {
            $file = fopen('php://output', 'w');

            // Write the column headers to the CSV
            fputcsv($file, $headerColumns);

            fclose($file);
        };

        return response()->stream($callback, 200, $csvHeaders);
    }
}
