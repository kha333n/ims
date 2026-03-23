@props([
    'wireModel' => '',
    'options' => [],
    'placeholder' => '— Select —',
    'label' => '',
    'required' => false,
    'disabled' => false,
])

<div x-data="{
    open: false,
    search: '',
    selected: @entangle($wireModel).live,
    options: {{ Js::from($options) }},
    get filtered() {
        if (!this.search) return this.options;
        const s = this.search.toLowerCase();
        return this.options.filter(o => {
            return String(o.id).includes(s) || o.label.toLowerCase().includes(s);
        });
    },
    get selectedLabel() {
        const opt = this.options.find(o => o.id == this.selected);
        return opt ? opt.label : '';
    },
    select(id) {
        this.selected = id;
        this.search = '';
        this.open = false;
    },
    clear() {
        this.selected = null;
        this.search = '';
    }
}" x-on:click.outside="open = false" class="relative">
    @if ($label)
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $label }}@if($required) <span class="text-red-500">*</span>@endif</label>
    @endif

    <div class="relative">
        {{-- Display selected or search input --}}
        <template x-if="!open && selected">
            <button type="button" x-on:click="open = true; $nextTick(() => $refs.searchInput.focus())"
                    class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-lg text-sm text-left bg-white focus:ring-2 focus:ring-navy-400 outline-none {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ $disabled ? 'disabled' : '' }}>
                <span x-text="selectedLabel" class="truncate"></span>
                <span x-on:click.stop="clear()" class="text-gray-400 hover:text-gray-600 ml-2 shrink-0">&times;</span>
            </button>
        </template>
        <template x-if="open || !selected">
            <div>
                <input x-ref="searchInput"
                       x-model="search"
                       x-on:focus="open = true"
                       x-on:keydown.escape="open = false"
                       type="text"
                       placeholder="{{ $placeholder }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                       {{ $disabled ? 'disabled' : '' }}>
                <svg class="absolute right-2.5 top-2.5 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </template>

        {{-- Dropdown --}}
        <div x-show="open" x-transition.opacity.duration.150ms
             class="absolute z-50 mt-1 w-full max-h-48 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg">
            <template x-for="opt in filtered" :key="opt.id">
                <button type="button"
                        x-on:click="select(opt.id)"
                        class="w-full px-3 py-1.5 text-sm text-left hover:bg-navy-50 transition-colors flex items-center gap-2"
                        :class="{ 'bg-navy-50 font-medium': opt.id == selected }">
                    <span class="text-gray-400 text-xs tabular-nums shrink-0" x-text="'#' + opt.id"></span>
                    <span x-text="opt.label" class="truncate"></span>
                </button>
            </template>
            <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-400">No results</div>
        </div>
    </div>
</div>
