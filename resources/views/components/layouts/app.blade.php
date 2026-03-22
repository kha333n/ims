<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900">

<div class="flex h-screen">
    {{-- Sidebar --}}
    <aside class="w-56 bg-gray-900 text-white flex flex-col shrink-0">
        <div class="px-4 py-4 border-b border-gray-700">
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400">IMS</p>
            <p class="text-sm font-semibold mt-1 leading-tight">Installment<br>Management System</p>
        </div>
        <nav class="flex-1 py-4 overflow-y-auto text-sm">
            <a href="/" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-700 rounded mx-2">
                Dashboard
            </a>
        </nav>
        <div class="px-4 py-3 border-t border-gray-700 text-xs text-gray-500">
            Techmiddle Technologies
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shrink-0">
            <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>
            <span class="text-xs text-gray-400">{{ now()->format('d/M/Y') }}</span>
        </header>
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>
