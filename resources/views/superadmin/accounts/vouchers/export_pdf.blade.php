<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vouchers Export</title>
    <style>
        body{font-family: DejaVu Sans, sans-serif; font-size:12px}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #ccc;padding:6px;text-align:left}
        th{background:#f5f5f5}
    </style>
</head>
<body>
    <h3>Vouchers Export</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Amount</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Approved By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vouchers as $v)
            <tr>
                <td>{{ $v->id }}</td>
                <td>{{ optional($v->user)->name }}</td>
                <td style="text-align:right">{{ number_format((float)$v->amount,2) }}</td>
                <td>{{ $v->purpose }}</td>
                <td>{{ ucfirst($v->status) }}</td>
                <td>{{ optional($v->created_at)->format('Y-m-d H:i') }}</td>
                <td>{{ optional($v->approver)->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
