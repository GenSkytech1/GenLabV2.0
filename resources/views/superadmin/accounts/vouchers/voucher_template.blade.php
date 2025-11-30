<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Voucher #{{ $voucher->id }}</title>
    <style>
        body{font-family: DejaVu Sans, Arial, sans-serif; color:#222}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
        .company{font-size:18px;font-weight:700}
        .meta{font-size:12px}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{border:1px solid #ccc;padding:8px}
        .text-right{text-align:right}
        .total{font-weight:700}
        .signature{margin-top:40px;display:flex;justify-content:space-between}
        .sig-box{width:30%;text-align:center}
    </style>
</head>
<body>
    <div class="header">
        <div style="display:flex;align-items:center">
            @php $logoPath = public_path('uploads/company_logo.png'); @endphp
            @if(file_exists($logoPath))
                <div style="margin-right:12px"><img src="{{ $logoPath }}" style="height:60px;" alt="Company Logo"></div>
            @endif
            <div>
                    <div class="company">Indian Testing Laboratory Private Limited</div>
                    <div class="meta">Plot No-248, Udyog Kendra 2, Ecotech III, Greater Noida, Tusyana, Uttar Pradesh 201306</div>
            </div>
        </div>
        <div style="text-align:right">
            <h3>Voucher</h3>
            <div>Voucher No: <strong>#{{ $voucher->id }}</strong></div>
            <div>Date: {{ optional($voucher->created_at)->format('d M Y') }}</div>
        </div>
    </div>

    <div>
        <table>
            <tr>
                <th>Payee</th>
                <td>{{ optional($voucher->user)->name ?? '—' }}</td>
                <th>Amount</th>
                <td class="text-right">{{ number_format((float)$voucher->amount, 2) }}</td>
            </tr>
            <tr>
                <th>Purpose</th>
                <td colspan="3">{{ $voucher->purpose }}</td>
            </tr>
        </table>

        <div style="margin-top:8px;font-style:italic">Amount (in words): <strong>{{ $voucher->amountInWords() }}</strong></div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $voucher->purpose }}</td>
                    <td class="text-right">{{ number_format((float)$voucher->amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="total">Total</td>
                    <td class="text-right total">{{ number_format((float)$voucher->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature">
            <div class="sig-box">Prepared by<br/><br/>__________________</div>
            <div class="sig-box">Approved by<br/><br/>{{ optional($voucher->approver)->name ?? '-' }}</div>
            <div class="sig-box">Received by<br/><br/>__________________</div>
        </div>
    </div>
</body>
</html>
