<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }

        th, td {
            border: 1px solid #000000;
            padding: 8px;
            vertical-align: middle;
        }

        thead th {
            background: #dbead5;
            font-weight: 700;
            text-align: center;
        }

        .title {
            font-size: 14pt;
            font-weight: 700;
            text-align: center;
            background: #0c4d05;
            color: #ffffff;
        }

        .total-row td {
            font-weight: 700;
            background: #f1f5f9;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="11" class="title">Program of Works Status Monitoring</th>
            </tr>
            <tr>
                <th rowspan="2">District</th>
                <th rowspan="2">No. of Projects</th>
                <th rowspan="2">Total Allocation</th>
                <th rowspan="2">No. of Plans Received</th>
                <th rowspan="2">No. of Project Estimate Received</th>
                <th colspan="3">Status of Program of Works</th>
                <th rowspan="2">On Going POW Preparation</th>
                <th rowspan="2">POW for Submission</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>No. of POW Prepared</th>
                <th>No. of POW Approved</th>
                <th>No. of POW Submitted</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->district }}</td>
                    <td>{{ $row->no_of_projects }}</td>
                    <td>&#8369;{{ number_format($row->total_allocation, 2) }}</td>
                    <td>{{ $row->no_of_plans_received }}</td>
                    <td>{{ $row->no_of_project_estimate_received }}</td>
                    <td>{{ $row->pow_received }}</td>
                    <td>{{ $row->pow_approved }}</td>
                    <td>{{ $row->pow_submitted }}</td>
                    <td>{{ $row->ongoing_pow_preparation }}</td>
                    <td>{{ $row->pow_for_submission }}</td>
                    <td>{{ $row->remarks }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center;">No data found in the database.</td>
                </tr>
            @endforelse

            @if ($rows->isNotEmpty())
                <tr class="total-row">
                    <td>Total</td>
                    <td>{{ $rows->sum('no_of_projects') }}</td>
                    <td>&#8369;{{ number_format($rows->sum('total_allocation'), 2) }}</td>
                    <td>{{ $rows->sum('no_of_plans_received') }}</td>
                    <td>{{ $rows->sum('no_of_project_estimate_received') }}</td>
                    <td>{{ $rows->sum('pow_received') }}</td>
                    <td>{{ $rows->sum('pow_approved') }}</td>
                    <td>{{ $rows->sum('pow_submitted') }}</td>
                    <td>{{ $rows->sum('ongoing_pow_preparation') }}</td>
                    <td>{{ $rows->sum('pow_for_submission') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
