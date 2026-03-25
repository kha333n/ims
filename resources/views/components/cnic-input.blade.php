@props([
    'wireModel' => '',
    'label' => 'CNIC',
    'required' => false,
    'placeholder' => 'XXXXX-XXXXXXX-X',
])

<div x-data="{
    raw: @entangle($wireModel),
    display: '',
    init() {
        this.display = this.format(this.raw);
        this.$watch('raw', v => this.display = this.format(v));
    },
    format(v) {
        if (!v) return '';
        let d = String(v).replace(/\D/g, '').slice(0, 13);
        if (d.length > 12) return d.slice(0, 5) + '-' + d.slice(5, 12) + '-' + d.slice(12);
        if (d.length > 5) return d.slice(0, 5) + '-' + d.slice(5);
        return d;
    },
    onInput() {
        let d = String(this.display).replace(/\D/g, '').slice(0, 13);
        if (d.length > 12) this.display = d.slice(0, 5) + '-' + d.slice(5, 12) + '-' + d.slice(12);
        else if (d.length > 5) this.display = d.slice(0, 5) + '-' + d.slice(5);
        else this.display = d;
        this.raw = this.display;
    }
}">
    @if ($label)
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $label }}@if($required) <span class="text-red-500">*</span>@endif</label>
    @endif
    <input type="text"
           inputmode="numeric"
           x-model="display"
           x-on:input="onInput()"
           placeholder="{{ $placeholder }}"
           maxlength="15"
           {{ $attributes->merge(['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none']) }}>
</div>
