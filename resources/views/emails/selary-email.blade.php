<!DOCTYPE html>
<html>
<head>
    <title>Salary Payment Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #007bff;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            color: #007bff;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.85em;
            text-align: center;
            color: #666;
        }
        .details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .payroll-box {
            padding: 10px;
            margin: 10px 0;
            background: #eef5ff;
            border-left: 4px solid #007bff;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Salary Payment Confirmation</h2>
        </div>
        <p>Dear {{ $name }},</p>
        <p>We are delighted to inform you that your salary for <strong>{{ $month }}</strong> has been successfully credited to your account.</p>
        
        <div class="details">
            <p><strong>Payment Summary:</strong></p>
            <ul>
                <li><strong>Amount Credited:</strong> {{ $amount }}</li>
                {{-- <li><strong>Total Earnings:</strong> {{ $total_amount }}</li> --}}
                <li><strong>Payment Date:</strong> {{ $payment_date }}</li>
                <li><strong>Payment Method:</strong> {{ $payment_method }}</li>
                {{-- <li><strong>Payroll Items:</strong> {{ $payroll_count }}</li> --}}
            </ul>
        </div>

        <p><strong>Payroll Breakdown:</strong></p>
        @foreach($payroll_details as $detail)
        <div class="payroll-box">
            <ul>
                {{-- <li><strong>Type:</strong> {{ $detail['type'] }}</li> --}}
                {{-- <li><strong>Reference ID:</strong> {{ $detail['reference_id'] }}</li> --}}
                <li><strong>Purpose:</strong> {{ $detail['purpose'] }}</li>
                <li><strong>Due Date:</strong> {{ $detail['due_date'] }}</li>
                <li><strong>Status:</strong> {{ $detail['status'] }}</li>
                <li><strong>Amount:</strong> {{ number_format($detail['amount'], 2) }}</li>
            </ul>
        </div>
        @endforeach
        
        <p>If you have any queries regarding your salary, please reach out to the us for assistance.</p>
        <p>Thank you for your dedication and contributions to the team.</p>
        <p>Best regards,</p>
        <p><strong>BahariHost</strong></p>
        
        <div class="footer">
            <p>This is an automated notification. Kindly do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
