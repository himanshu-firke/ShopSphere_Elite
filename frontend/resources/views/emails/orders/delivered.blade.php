<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Delivered</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .delivery-info {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th,
        .items-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .items-table th {
            background: #f5f5f5;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Kanha Living" class="logo">
        <h1>Your Order Has Been Delivered!</h1>
    </div>

    <p>Dear {{ $order->shippingAddress->full_name }},</p>

    <p>Your order has been successfully delivered! We hope you love your new furniture.</p>

    <div class="delivery-info">
        <h2>Delivery Information</h2>
        <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p><strong>Delivery Date:</strong> {{ $order->delivery_details['delivered_at']->format('F j, Y') }}</p>
        @if(isset($order->delivery_details['signed_by']))
            <p><strong>Signed By:</strong> {{ $order->delivery_details['signed_by'] }}</p>
        @endif
    </div>

    <h3>Items Delivered</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>We'd love to hear your thoughts on your new furniture! Please take a moment to review your purchase.</p>

    <p>
        <a href="{{ route('orders.review', $order->order_number) }}" class="button">Write a Review</a>
    </p>

    <p>If you have any questions or concerns about your delivery, please don't hesitate to contact us.</p>

    <div class="footer">
        <p>Thank you for shopping with Kanha Living!</p>
        <p>For any queries, please contact us at {{ config('company.email') }} or call {{ config('company.phone') }}</p>
        <p>{{ config('company.address') }}</p>
    </div>
</body>
</html> 