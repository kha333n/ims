<div>
    <table class="w-full text-xs border-collapse">
        <thead><tr class="bg-navy-800 text-white"><th class="px-3 py-2 text-left">Sr#</th><th class="px-3 py-2 text-left">Item</th><th class="px-3 py-2 text-right">Sale Price</th><th class="px-3 py-2 text-right">Purchase Price</th><th class="px-3 py-2 text-right">Quantity</th><th class="px-3 py-2 text-left">Supplier</th></tr></thead>
        <tbody class="divide-y divide-gray-200">
            @foreach ($products as $i => $product)
                <tr class="{{ $product->quantity <= 5 ? 'bg-red-50' : '' }}"><td class="px-3 py-1.5">{{ $i + 1 }}</td><td class="px-3 py-1.5 font-medium">{{ $product->name }}</td><td class="px-3 py-1.5 text-right tabular-nums">@money($product->sale_price)</td><td class="px-3 py-1.5 text-right tabular-nums">@money($product->purchase_price)</td><td class="px-3 py-1.5 text-right tabular-nums {{ $product->quantity <= 5 ? 'text-red-600 font-bold' : '' }}">{{ $product->quantity }}</td><td class="px-3 py-1.5">{{ $product->supplier?->name ?? '—' }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
