<!DOCTYPE html>
<html>
<head>
    <title>Payroll Payment Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Payroll Payment Notification</h2>
        </div>
        <p>Dear {{ $name }},</p>
        <p>We are pleased to inform you that your salary payment for the month of <strong>{{ $month }}</strong> has been successfully processed.</p>
        <p><strong>Payment Details:</strong></p>
        <ul>
            <li><strong>Amount:</strong> {{ $amount }}</li>
            <li><strong>Total Amount:</strong> {{ $total_amount }}</li>
            <li><strong>Payment Date:</strong> {{ $payment_date }}</li>
            <li><strong>Payment Method:</strong> {{ $payment_method }}</li>
            <li><strong>Number of Payroll Items:</strong> {{ $payroll_count }}</li>
        </ul>

        <p><strong>Payroll Details:</strong></p>
        @foreach($payroll_details as $detail)
        <div style="margin-bottom: 15px; padding: 10px; border: 1px solid #eee;">
            <ul>
                <li><strong>Type:</strong> {{ $detail['type'] }}</li>
                <li><strong>Reference ID:</strong> {{ $detail['reference_id'] }}</li>
                <li><strong>Purpose:</strong> {{ $detail['purpose'] }}</li>
                {{-- <li><strong>Pay To:</strong> {{ $detail['pay_to'] }}</li> --}}
                <li><strong>Due Date:</strong> {{ $detail['due_date'] }}</li>
                <li><strong>Status:</strong> {{ $detail['status'] }}</li>
                <li><strong>Amount:</strong> {{ number_format($detail['amount'], 2) }}</li>
            </ul>
        </div>
        @endforeach

        <p>If you have any questions or concerns regarding this payment, please feel free to contact the HR department.</p>
        <p>Thank you for your hard work and dedication.</p>
        <p>Best regards,</p>
        <p><strong>Bahari Office Management</strong></p>
        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>