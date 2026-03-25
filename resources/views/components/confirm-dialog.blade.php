@props([
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
    'variant' => 'danger',
    'wireConfirm' => '',
    'wireCancel' => '',
])

@php
    $colors = match($variant) {
        'danger' => ['bg' => 'bg-red-600 hover:bg-red-500', 'icon' => 'text-red-600', 'iconBg' => 'bg-red-100'],
        'warning' => ['bg' => 'bg-orange-600 hover:bg-orange-500', 'icon' => 'text-orange-600', 'iconBg' => 'bg-orange-100'],
        default => ['bg' => 'bg-navy-600 hover:bg-navy-500', 'icon' => 'text-navy-600', 'iconBg' => 'bg-navy-100'],
    };
@endphp

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="{{ $wireCancel }}">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-start gap-4 mb-4">
            <div class="shrink-0 w-10 h-10 rounded-full {{ $colors['iconBg'] }} flex items-center justify-center">
                @if ($variant === 'danger')
                    <svg class="w-5 h-5 {{ $colors['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 {{ $colors['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ $title }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $message }}</p>
            </div>
        </div>

        {{ $slot }}

        <div class="flex justify-end gap-3 mt-5">
            <button wire:click="{{ $wireCancel }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                {{ $cancelLabel }}
            </button>
            <button wire:click="{{ $wireConfirm }}" wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white {{ $colors['bg'] }} rounded-lg transition-colors disabled:opacity-50">
                <svg wire:loading wire:target="{{ $wireConfirm }}" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>{{ $confirmLabel }}
            </button>
        </div>
    </div>
</div>
