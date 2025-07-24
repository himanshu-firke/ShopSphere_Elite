<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
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
        .order-info {
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
        .total-section {
            float: right;
            width: 250px;
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
            clear: both;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Kanha Living" class="logo">
        <h1>Order Confirmation</h1>
    </div>

    <p>Dear {{ $order->shippingAddress->full_name }},</p>

    <p>Thank you for your order! We're excited to confirm that your order has been received and is being processed.</p>

    <div class="order-info">
        <h2>Order Details</h2>
        <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
        <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
    </div>

    <h3>Items Ordered</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ number_format($item->price, 2) }}</td>
                    <td>₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td>₹{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Tax (18% GST):</td>
                <td>₹{{ number_format($order->tax, 2) }}</td>
            </tr>
            <tr>
                <td>Shipping:</td>
                <td>₹{{ number_format($order->shipping_fee, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total:</strong></td>
                <td><strong>₹{{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

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
        <a href="{{ route('orders.show', $order->order_number) }}" class="button">Track Your Order</a>
    </p>

    <p>We'll send you another email when your order ships. If you have any questions about your order, please don't hesitate to contact us.</p>

    <div class="footer">
        <p>Thank you for shopping with Kanha Living!</p>
        <p>For any queries, please contact us at {{ config('company.email') }} or call {{ config('company.phone') }}</p>
        <p>{{ config('company.address') }}</p>
    </div>
</body>
</html> 