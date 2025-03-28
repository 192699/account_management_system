<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Account Statement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .account-info {
            margin-bottom: 20px;
        }
        .statement-period {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .summary {
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .credit {
            color: green;
        }
        .debit {
            color: red;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Account Statement</h1>
    </div>

    <div class="account-info">
        <p><strong>Account Number:</strong> {{ $account->account_number }}</p>
        <p><strong>Account Name:</strong> {{ $account->account_name }}</p>
        <p><strong>Account Type:</strong> {{ $account->account_type }}</p>
        <p><strong>Currency:</strong> {{ $account->currency }}</p>
    </div>

    <div class="statement-period">
        <p><strong>Statement Period:</strong> {{ $start_date }} to {{ $end_date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                <td class="{{ strtolower($transaction->type) }}">{{ $transaction->type }}</td>
                <td>{{ $transaction->description }}</td>
                <td class="{{ strtolower($transaction->type) }}">{{ number_format($transaction->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated statement. No signature is required.</p>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html> 