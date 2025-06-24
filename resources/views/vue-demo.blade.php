<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel with Vue.js</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <div style="text-align: center; padding: 50px;">
            <h1>Laravel with Vue.js Demo</h1>
            <p>Welcome to your Laravel + Vue.js application!</p>
            <example-component></example-component>
        </div>
    </div>
</body>
</html>
