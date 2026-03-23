<div>
    <table class="w-full text-xs border-collapse">
        <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Sr#</th><th class="px-2 py-2 text-left">Product</th><th class="px-2 py-2 text-right">Qty</th><th class="px-2 py-2 text-right">Purchase Price</th><th class="px-2 py-2 text-right">Sale Price</th><th class="px-2 py-2 text-right">Value at Cost</th><th class="px-2 py-2 text-right">Value at Sale</th><th class="px-2 py-2 text-right">Potential Profit</th></tr></thead>
        <tbody class="divide-y divide-gray-200">
            @foreach ($products as $i => $p)
                <tr class="{{ $p['quantity'] === 0 ? 'bg-gray-100 text-gray-400' : ($p['profit'] < 0 ? 'bg-red-50' : '') }}"><td class="px-2 py-1.5">{{ $i + 1 }}</td><td class="px-2 py-1.5 font-medium">{{ $p['name'] }}</td><td class="px-2 py-1.5 text-right">{{ $p['quantity'] }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($p['purchase_price'])</td><td class="px-2 py-1.5 text-right tabular-nums">@money($p['sale_price'])</td><td class="px-2 py-1.5 text-right tabular-nums">@money($p['cost_value'])</td><td class="px-2 py-1.5 text-right tabular-nums">@money($p['sale_value'])</td><td class="px-2 py-1.5 text-right tabular-nums font-medium {{ $p['profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">@money(abs($p['profit']))</td></tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-100 font-bold"><tr><td colspan="5" class="px-2 py-2 text-right">Totals:</td><td class="px-2 py-2 text-right tabular-nums">@money($products->sum('cost_value'))</td><td class="px-2 py-2 text-right tabular-nums">@money($products->sum('sale_value'))</td><td class="px-2 py-2 text-right tabular-nums text-green-700">@money($products->sum('profit'))</td></tr></tfoot>
    </table>
</div>
