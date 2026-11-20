<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} catalogue</title>
</head>
<body>
    <main>
        <h1>{{ $headline }}</h1>
        <ul>
            @foreach ($products as $product)
                <li data-product="{{ $product->id }}">{{ $product->title }} — {{ $product->price_cents }} cents</li>
            @endforeach
        </ul>
    </main>
</body>
</html>
