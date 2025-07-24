<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .company-info {
            margin-bottom: 30px;
        }
        .invoice-info {
            margin-bottom: 30px;
        }
        .address-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .address-box {
            width: 45%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .totals {
            float: right;
            width: 300px;
        }
        .totals table {
            margin-bottom: 0;
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
    <div class="container">
        <div class="header">
            <h1>{{ $company['name'] }}</h1>
            <p>{{ $company['address'] }}</p>
            <p>Phone: {{ $company['phone'] }} | Email: {{ $company['email'] }}</p>
            <p>GST: {{ $company['gst'] }}</p>
        </div>

        <div class="invoice-info">
            <h2>Invoice #{{ $order->order_number }}</h2>
            <p>Date: {{ $order->created_at->format('d/m/Y') }}</p>
            <p>Payment Method: {{ ucfirst($order->payment_method) }}</p>
            <p>Payment Status: {{ ucfirst($order->payment_status) }}</p>
        </div>

        <div class="address-info">
            <div class="address-box">
                <h3>Billing Address</h3>
                <p>{{ $order->billingAddress->full_name }}</p>
                <p>{{ $order->billingAddress->address_line1 }}</p>
                @if($order->billingAddress->address_line2)
                    <p>{{ $order->billingAddress->address_line2 }}</p>
                @endif
                <p>{{ $order->billingAddress->city }}, {{ $order->billingAddress->state }}</p>
                <p>{{ $order->billingAddress->postal_code }}</p>
                <p>{{ $order->billingAddress->country }}</p>
                <p>Phone: {{ $order->billingAddress->phone }}</p>
            </div>

            <div class="address-box">
                <h3>Shipping Address</h3>
                <p>{{ $order->shippingAddress->full_name }}</p>
                <p>{{ $order->shippingAddress->address_line1 }}</p>
                @if($order->shippingAddress->address_line2)
                    <p>{{ $order->shippingAddress->address_line2 }}</p>
                @endif
                <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }}</p>
                <p>{{ $order->shippingAddress->postal_code }}</p>
                <p>{{ $order->shippingAddress->country }}</p>
                <p>Phone: {{ $order->shippingAddress->phone }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->product_sku }}</td>
                        <td>₹{{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
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
                    <th>Total:</th>
                    <th>₹{{ number_format($order->total_amount, 2) }}</th>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>For any queries, please contact us at {{ $company['email'] }} or call {{ $company['phone'] }}</p>
        </div>
    </div>
</body>
</html> 