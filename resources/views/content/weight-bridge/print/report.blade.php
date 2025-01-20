<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container-fluid {
            padding: 15px;
        }

        h4 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .border {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f4f4f4;
        }

        .bg-warning-subtle {
            background-color: #fff3cd;
        }

        .text-end {
            text-align: right;
        }

        .text-start {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .small {
            font-size: 12px;
        }

        .table-cell-yellow {
            background-color: #ffffe0;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <h4>PT KERAMINDO MEGAH PERTIWI<br>ESTIMATED PAYMENT TO TRANSPORTER</h4>
        @php
        // Initialize totals for the footer
        $totalQuantity = 0;
        $totalStdWeight = 0;
        $totalWeight = 0;
        $totalVar = 0;
        $totalRate = 0;
        $totalAmount = 0;
        @endphp
        @foreach ($reports as $key => $report)
        <table>
            <tr>
                <th colspan="1" style="background: white; width:120px;">
                    <div class="small text-start">
                        <strong>Kode Suplier:</strong>
                    </div>
                </th>
                <th colspan="5" style="background: white;">
                    <div class="small text-start">
                        <strong>{{ $report[0]->TransporterCode ?? 'N/A' }}</strong>
                    </div>
                </th>
            </tr>
            <tr>
                <th colspan="1" style="background: white;">
                    <div class="small text-start">
                        <strong>Nama Transporter:</strong>
                    </div>
                </th>
                <th colspan="5" style="background: white;">
                    <div class="small text-start">
                        <strong>{{ $key ?? 'N/A' }}</strong>
                    </div>
                </th>
            </tr>
            <tr>
                <th class="small border" style="width: 50px;">D/O NO</th>
                <th class="small border" style="width: 70px;">Date</th>
                <th class="small border" style="width: 50px;">Plate NO</th>
                <th class="small border" style="width: 50px;">Vehicle Group</th>
                <th class="small border" style="width: 70px;">Area</th>
                <th class="small border" style="width: 50px;">Quantity</th>
                <th class="small border" style="width: 50px;">WB.Doc</th>
                <th class="small border" style="width: 50px;">STD Weight (Kg)</th>
                <th class="small border" style="width: 50px;">Weight (Kg)</th>
                <th class="small border" style="width: 50px;">Var (Kg)</th>
                <th class="small border" style="width: 50px;">Rate</th>
                <th class="small border" style="width: 80px;">Amount (Rp)</th>
            </tr>

            <!-- Body Section -->
            <tbody>
                @php
                $subtotalQuantity = 0;
                $subtotalStdWeight = 0;
                $subtotalWeight = 0;
                $subtotalVar = 0;
                $subtotalRate = 0;
                $subtotalAmount = 0;
                @endphp

                @foreach($report as $data)
                @if($is_multi_transporter)
                @php
                $subtotalQuantity += $data->Quantity ?? 0;
                $subtotalStdWeight += $data->StdWeight ?? 0;
                $subtotalWeight += $data->Weight ?? 0;
                $subtotalVar += $data->VarKg ?? 0;
                $subtotalRate += $data->Rate ?? 0;
                $subtotalAmount += $data->Amount ?? 0;

                // Accumulate into the footer totals
                $totalQuantity += $data->Quantity ?? 0;
                $totalStdWeight += $data->StdWeight ?? 0;
                $totalWeight += $data->Weight ?? 0;
                $totalVar += $data->VarKg ?? 0;
                $totalRate += $data->Rate ?? 0;
                $totalAmount += $data->Amount ?? 0;
                @endphp
                @endif
                <tr class="small">
                    <td>{{ $data->DoNo ?? 'N/A' }}</td>
                    <td>
                        {{ $data->date ? \Carbon\Carbon::parse(str_replace(':AM', ' AM', str_replace(':PM', ' PM', $data->date)))->format('d-m-Y') : '' }}
                    </td>
                    <td>{{ $data->PlateNo ?? 'N/A' }}</td>
                    <td>{{ $data->VehicleGroup ?? 'N/A' }}</td>
                    <td>{{ !empty($data->Area) ? $data->Area : 'N/A' }}</td>
                    <td>{{ number_format($data->Quantity ?? 0, 0) }}</td>
                    <td>{{ $data->WbDoc ?? 'N/A' }}</td>
                    <td>{{ number_format($data->StdWeight ?? 0, 0) }}</td>
                    <td>{{ number_format($data->Weight ?? 0, 0) }}</td>
                    <td>{{ number_format($data->VarKg ?? 0, 0) }}</td>
                    <td>{{ number_format($data->Rate ?? 0, 0) }}</td>
                    <td>{{ number_format($data->Amount ?? 0, 0) }}</td>
                </tr>
                @endforeach
                <!-- Subtotal Row -->
                @if($is_multi_transporter)
                <tr class="table-secondary fw-bold small" style="background: #f4f4f4;">
                    <td colspan="5" class="text-end">Sub Total</td>
                    <td class="text-start">{{ number_format($subtotalQuantity, 0) }}</td>
                    <td></td>
                    <td class="text-start">{{ number_format($subtotalStdWeight, 0) }}</td>
                    <td class="text-start">{{ number_format($subtotalWeight, 0) }}</td>
                    <td class="text-start">{{ number_format($subtotalVar, 0) }}</td>
                    <td class="text-start">{{ number_format($subtotalRate, 0) }}</td>
                    <td class="text-start">{{ number_format($subtotalAmount, 0) }}</td>
                </tr>
                @endif
            </tbody>
            <!-- Footer Totals -->
            @if($is_multi_transporter)
            @if($loop->last)
            <tr class="table-dark fw-bold small" style="background: #f4f4f4;">
                <td colspan="5" class="text-end">Total</td>
                <td class="text-start">{{ number_format($totalQuantity, 0) }}</td>
                <td></td>
                <td class="text-start">{{ number_format($totalStdWeight, 0) }}</td>
                <td class="text-start">{{ number_format($totalWeight, 0) }}</td>
                <td class="text-start">{{ number_format($totalVar, 0) }}</td>
                <td class="text-start">{{ number_format($totalRate, 0) }}</td>
                <td class="text-start">{{ number_format($totalAmount, 0) }}</td>
            </tr>
            @endif
            @endif
        </table>
        @endforeach
    </div>
</body>

</html>
