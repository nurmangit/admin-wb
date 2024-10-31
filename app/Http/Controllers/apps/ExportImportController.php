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

        // Store the URL to redirect back to
        $redirectUrl = $request->input('redirect_url', url()->previous());

        // Check if the model exists
        if (!class_exists("App\\Models\\$table")) {
            return redirect($redirectUrl)
                ->with('error', 'Model not found.');
        }

        // Get model instance
        $modelClass = "App\\Models\\$table";
        $model = new $modelClass();

        // Get fillable columns
        $fillableColumns = $model->getFillable();

        // Read CSV file
        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        try {
            // Read headers
            $headers = fgetcsv($handle);
            if (!$headers) {
                throw new \Exception('CSV file is empty or invalid');
            }

            // Map headers to their positions
            $headerMap = array_flip($headers);

            // Start database transaction
            DB::beginTransaction();

            $processedRows = 0;
            $errorRows = [];

            // Read data rows
            while (($row = fgetcsv($handle)) !== false) {
                $data = [];
                $relatedData = [];
                $whereConditions = [];

                try {
                    // Process each column in the current row
                    foreach ($headerMap as $header => $position) {
                        // Skip if the position doesn't exist in the row
                        if (!isset($row[$position])) {
                            continue;
                        }

                        $value = $row[$position];

                        // Check if this is a related model column
                        if (str_contains($header, '_')) {
                            list($relation, $field) = explode('_', $header, 2);

                            // If this is not a UUID field in the main model
                            if (!in_array("{$relation}_uuid", $fillableColumns)) {
                                // Store for later processing
                                $relatedData[$relation][$field] = $value;
                                continue;
                            }
                        }

                        // If it's a direct column of the main model
                        if (in_array($header, $fillableColumns)) {
                            $data[$header] = $value;
                            // Build where conditions for non-null values
                            if (!is_null($value)) {
                                $whereConditions[$header] = $value;
                            }
                        }
                    }

                    // Create or update main model instance using query builder
                    $existingRecord = DB::table($model->getTable())
                        ->where(function($query) use ($whereConditions) {
                            foreach ($whereConditions as $column => $value) {
                                $value = str_replace("'", "''", $value); // Escape single quotes
                                $query->where($column, '=', $value);
                            }
                        })
                        ->where('Date04', null)
                        ->first();

                    if ($existingRecord) {
                        DB::table($model->getTable())
                            ->where($model->getKeyName(), $existingRecord->{$model->getKeyName()})
                            ->update($data);

                        $mainRecord = $modelClass::find($existingRecord->{$model->getKeyName()});
                    } else {
                        $mainRecord = $modelClass::create($data);
                    }

                    // Process related models
                    foreach ($relatedData as $relation => $fields) {
                        $relationName = str_replace(' ', '', ucwords(str_replace('_', ' ', $relation)));
                        $relatedModelClass = "App\\Models\\$relationName";

                        if (!class_exists($relatedModelClass)) {
                            continue;
                        }

                        $relatedModel = new $relatedModelClass();
                        $relatedFillable = $relatedModel->getFillable();

                        $validFields = array_intersect_key($fields, array_flip($relatedFillable));

                        if (!empty($validFields)) {
                            $existingRelated = DB::table($relatedModel->getTable())
                                ->where(function($query) use ($validFields) {
                                    foreach ($validFields as $column => $value) {
                                        if (!is_null($value)) {
                                            $value = str_replace("'", "''", $value);
                                            $query->where($column, '=', $value);
                                        }
                                    }
                                })
                                ->where('Date04', null)
                                ->first();

                            if ($existingRelated) {
                                DB::table($relatedModel->getTable())
                                    ->where($relatedModel->getKeyName(), $existingRelated->{$relatedModel->getKeyName()})
                                    ->update($validFields);

                                $relatedRecord = $relatedModelClass::find($existingRelated->{$relatedModel->getKeyName()});
                            } else {
                                $relatedRecord = $relatedModelClass::create($validFields);
                            }

                            if (isset($relatedRecord)) {
                                $mainRecord->{$relation . '_uuid'} = $relatedRecord->uuid;
                                $mainRecord->save();
                            }
                        }
                    }

                    $processedRows++;

                } catch (\Exception $e) {
                    // Store the error information
                    $errorRows[] = [
                        'row' => $row,
                        'error' => $e->getMessage()
                    ];
                    \Log::error("Error processing row: " . json_encode($row));
                    \Log::error("Error message: " . $e->getMessage());
                }
            }

            // Commit transaction
            DB::commit();

            // Prepare success message with details
            $message = "Successfully processed {$processedRows} rows.";
            if (count($errorRows) > 0) {
                $message .= " Failed to process " . count($errorRows) . " rows.";
                // Store error details in session for detailed view if needed
                session(['import_errors' => $errorRows]);
            }

            return redirect($redirectUrl)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect($redirectUrl)
                ->with('error', 'Import failed: ' . $e->getMessage());
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

}

