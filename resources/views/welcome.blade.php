<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel Task Management</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="antialiased font-sans bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col items-center justify-center p-6">
            <div class="max-w-2xl w-full bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="flex justify-center mb-6">
                    <div class="bg-indigo-100 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
                
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-2">
                    Task Management System
                </h1>
                
                <p class="text-lg text-gray-600 mb-8">
                    Welcome to your API-driven project management portal.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                    <div class="p-4 rounded-lg border border-gray-100 bg-gray-50">
                        <h3 class="font-semibold text-gray-900">API Access</h3>
                        <p class="text-sm text-gray-500">Version 1 is active. Check <code>routes/api.php</code> for task and user endpoints.</p>
                    </div>
                    <div class="p-4 rounded-lg border border-gray-100 bg-gray-50">
                        <h3 class="font-semibold text-gray-900">Authentication</h3>
                        <p class="text-sm text-gray-500">Sanctum is configured for stateful and token-based authentication.</p>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-gray-100 flex items-center justify-center space-x-4 text-sm text-gray-400">
                    <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</span>
                    <span>•</span>
                    <span>PHP v{{ PHP_VERSION }}</span>
                </div>
            </div>
        </div>
    </body>
</html>
