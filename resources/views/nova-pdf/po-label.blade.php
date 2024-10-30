<!doctype html>
<html>
<head>
    <title>PO label</title>
    <meta charset="UTF-8" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html, body{
            height: 100%;
        }
    </style>
</head>
<body class="tailwind-container">
    @foreach($orderQueues as $orderQueue)
        <div style="width: {{ $pdfWidth ?? 283.465 }}px; height: auto; border: 1px solid black; padding: 20px; font-size: 16px; line-height: 1.6; margin: 30px;">
            <p style="margin: 0; font-weight: bold;">{{ env('app_name') }}</p>
            <p style="margin: 0;"><strong>Order ID:</strong> {{ $orderQueue->order->order_number }}</p>
            <p style="margin: 0;"><strong>PO:</strong> {{ $orderQueue->id }}</p>
            <p style="margin: 0;"><strong>Material:</strong> {{ $orderQueue->upload->material_name }}</p>
            <p style="margin: 0;"><strong>Quantity:</strong> {{ $orderQueue->upload->quantity }}</p>
        </div>
        @if($count > 1)
        @pageBreak
        @endif
    @endforeach
</body>
</html>
