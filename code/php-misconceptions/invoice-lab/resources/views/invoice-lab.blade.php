<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->id }}</title>
</head>
<body>
    <main>
        <h1>Invoice {{ $invoice->id }}</h1>
        <p>Customer: {{ $invoice->customer_name }}</p>
    </main>
</body>
</html>
