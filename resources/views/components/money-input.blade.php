@props([
    'wireModel' => '',
    'label' => '',
    'required' => false,
    'min' => null,
    'step' => '0.01',
    'placeholder' => '0',
    'live' => false,
    'debounce' => '300ms',
    'disabled' => false,
])

@php
    $entangleModifier = $live ? '.live.debounce.' . $debounce : '';
@endphp

<div x-data="{
    raw: @entangle($wireModel){{ $entangleModifier }},
    display: '',
    focused: false,
    init() {
        this.display = this.formatDisplay(this.raw);
        this.$watch('raw', (v) => {
            if (!this.focused) this.display = this.formatDisplay(v);
        });
    },
    formatDisplay(val) {
        if (val === '' || val === null || val === undefined) return '';
        const num = parseFloat(String(val).replace(/,/g, ''));
        if (isNaN(num)) return String(val);
        if (num === 0) return '0';
        return num.toLocaleString('en-US', { maximumFractionDigits: 2 });
    },
    onFocus() {
        this.focused = true;
        this.display = this.raw === '0' ? '' : String(this.raw).replace(/,/g, '');
    },
    onBlur() {
        this.focused = false;
        const clean = String(this.display).replace(/,/g, '');
        if (clean === '' || isNaN(parseFloat(clean))) {
            this.raw = '0';
            this.display = '0';
            return;
        }
        this.raw = clean;
        this.display = this.formatDisplay(clean);
    },
    onInput() {
        const clean = String(this.display).replace(/,/g, '');
        this.raw = clean;
    }
}">
    @if ($label)
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $label }}@if($required) <span class="text-red-500">*</span>@endif</label>
    @endif
    <input type="text"
           inputmode="decimal"
           x-model="display"
           x-on:focus="onFocus()"
           x-on:blur="onBlur()"
           x-on:input="onInput()"
           placeholder="{{ $placeholder }}"
           {{ $disabled ? 'disabled' : '' }}
           {{ $attributes->merge(['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none']) }}>
</div>
