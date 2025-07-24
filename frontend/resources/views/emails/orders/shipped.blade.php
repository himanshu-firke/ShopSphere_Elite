<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Shipped</title>
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
        .tracking-info {
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
        <h1>Your Order Has Shipped!</h1>
    </div>

    <p>Dear {{ $order->shippingAddress->full_name }},</p>

    <p>Great news! Your order is on its way to you.</p>

    <div class="tracking-info">
        <h2>Tracking Information</h2>
        <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p><strong>Carrier:</strong> {{ $order->shipping_details['carrier'] }}</p>
        <p><strong>Tracking Number:</strong> {{ $order->tracking_number }}</p>
        <p><strong>Estimated Delivery:</strong> {{ $order->estimated_delivery_date->format('F j, Y') }}</p>
    </div>

    <h3>Items Shipped</h3>
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

    <h3>Shipping Address</h3>
    <p>
        {{ $order->shippingAddress->full_name }}<br>
        {{ $order->shippingAddress->address_line1 }}<br>
        @if($order->shippingAddress->address_line2)
            {{ $order->shippingAddress->address_line2 }}<br>
        @endif
        {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }}<br>
        {{ $order->shippingAddress->postal_code }}<br>
        {{ $order->shippingAddress->country }}<br>
        Phone: {{ $order->shippingAddress->phone }}
    </p>

    <p>
        <a href="{{ route('orders.tracking', $order->order_number) }}" class="button">Track Your Package</a>
    </p>

    <p>Once your package is delivered, we'll send you a delivery confirmation email. If you have any questions about your shipment, please don't hesitate to contact us.</p>

    <div class="footer">
        <p>Thank you for shopping with Kanha Living!</p>
        <p>For any queries, please contact us at {{ config('company.email') }} or call {{ config('company.phone') }}</p>
        <p>{{ config('company.address') }}</p>
    </div>
</body>
</html> 