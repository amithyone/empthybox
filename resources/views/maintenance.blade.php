<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - BiggestLogs</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/proxima-nova" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark-100 text-gray-200 min-h-screen flex items-center justify-center px-4" style="font-family: 'Proxima Nova', 'Montserrat', sans-serif;">
    <div class="max-w-2xl mx-auto text-center">
        <div class="mb-8">
            <h1 class="text-5xl md:text-7xl font-bold mb-4 gradient-text">🔧</h1>
            <h2 class="text-3xl md:text-5xl font-bold mb-4 gradient-text">Under Maintenance</h2>
        </div>
        
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-2xl shadow-red-accent/10 p-8 md:p-12">
            <p class="text-lg md:text-xl text-gray-300 mb-6">{{ $message ?? 'We are currently performing scheduled maintenance. Please check back soon.' }}</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('login') }}" class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-8 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Admin Login</span>
                </a>
            </div>
        </div>
        
        <p class="text-gray-500 mt-8 text-sm">🔥 BiggestLogs</p>
    </div>
</body>
</html>

