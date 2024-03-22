<!DOCTYPE html>
<html>
<head>
    <title>Product Details</title>
</head>
<body>
    <h1>Product Details</h1>
    <ul>
        <li>Product name: {{ $name}}</li>
        <li>Sku: {{ $sku}}</li>
        <li>Price: ₹{{ $amount}}</li>
        <li>Description: {!! $description !!}</li>
        <li>Shared by: {{$user_name}}({{ $user_email}})</li>
    </ul>
</body>
</html>
