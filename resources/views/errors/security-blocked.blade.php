{{-- resources/views/errors/security-blocked.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Security Alert - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                {{-- Security Shield Icon --}}
                <div class="mx-auto h-20 w-20 text-red-500 mb-6">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.4,7 14.8,8.6 14.8,10V11.5C15.4,11.5 16,12.4 16,13V16C16,17.1 15.4,18 14.5,18H9.5C8.6,18 8,17.1 8,16V13C8,12.4 8.4,11.5 9,11.5V10C9,8.6 10.6,7 12,7M12,8.2C11.2,8.2 10.2,9.2 10.2,10V11.5H13.8V10C13.8,9.2 12.8,8.2 12,8.2Z" />
                    </svg>
                </div>

                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">
                    Security Alert
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    Your request has been blocked due to security concerns.
                </p>
            </div>

            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Potentially dangerous input detected
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>Your request contained patterns that may indicate malicious activity. This incident has
                                been logged for security purposes.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">What happened?</h3>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <span class="h-1.5 w-1.5 bg-gray-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Your input was analyzed for security threats
                        </li>
                        <li class="flex items-start">
                            <span class="h-1.5 w-1.5 bg-gray-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Suspicious patterns were detected
                        </li>
                        <li class="flex items-start">
                            <span class="h-1.5 w-1.5 bg-gray-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            The request was blocked to protect the system
                        </li>
                    </ul>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">If this was a mistake:</h3>
                    <p class="text-sm text-blue-700">
                        Please contact our support team if you believe this was blocked in error.
                        Include the timestamp: <strong>{{ now()->format('Y-m-d H:i:s T') }}</strong>
                    </p>
                </div>
            </div>

            <div class="flex space-x-4">
                <button onclick="history.back()"
                    class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150">
                    ← Go Back
                </button>
                <a href="{{ route('home') }}"
                    class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-md text-sm font-medium text-center hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                    Home Page
                </a>
            </div>

            <div class="text-center text-xs text-gray-400 mt-8">
                Security powered by {{ config('app.name') }} • Request ID: {{ uniqid() }}
            </div>
        </div>
    </div>
</body>

</html>
