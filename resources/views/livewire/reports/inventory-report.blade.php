<div>
    @php $serial = 1; @endphp
    <table class="w-full text-xs border-collapse">
        <thead>
            <tr class="bg-navy-800 text-white">
                <th class="px-3 py-2 text-left w-8">Serial</th>
                <th class="px-3 py-2 text-left">Product Name</th>
                <th class="px-3 py-2 text-left">Batch Number</th>
                <th class="px-3 py-2 text-left">Purchase Date</th>
                <th class="px-3 py-2 text-right">Purchase Price</th>
                <th class="px-3 py-2 text-right">Sale Price</th>
                <th class="px-3 py-2 text-right">Quantity In Stock</th>
                <th class="px-3 py-2 text-left">Supplier</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($products as $product)
                @if ($product->purchases->isEmpty())
                    <tr class="bg-gray-50">
                        <td class="px-3 py-1.5 text-gray-400">{{ $serial++ }}</td>
                        <td class="px-3 py-1.5 font-medium">{{ $product->name }}</td>
                        <td class="px-3 py-1.5 text-gray-400" colspan="4">— No stock batches —</td>
                        <td class="px-3 py-1.5 text-right text-gray-400">0</td>
                        <td class="px-3 py-1.5 text-gray-400">{{ $product->supplier?->name ?? '—' }}</td>
                    </tr>
                @else
                    @foreach ($product->purchases as $batchIndex => $batch)
                        <tr class="{{ $batchIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                            @if ($batchIndex === 0)
                                <td class="px-3 py-1.5" rowspan="{{ $product->purchases->count() }}">{{ $serial++ }}</td>
                                <td class="px-3 py-1.5 font-medium" rowspan="{{ $product->purchases->count() }}">{{ $product->name }}</td>
                            @endif
                            <td class="px-3 py-1.5">{{ $batch->batch_number ?? '—' }}</td>
                            <td class="px-3 py-1.5">{{ formatDate($batch->purchase_date) }}</td>
                            <td class="px-3 py-1.5 text-right tabular-nums">@money($batch->unit_cost)</td>
                            <td class="px-3 py-1.5 text-right tabular-nums">@money($product->sale_price)</td>
                            <td class="px-3 py-1.5 text-right tabular-nums {{ $batch->remaining_qty <= 5 ? 'text-red-600 font-bold' : '' }}">{{ $batch->remaining_qty }}</td>
                            <td class="px-3 py-1.5">{{ $batch->supplier?->name ?? $product->supplier?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                    {{-- Product subtotal row --}}
                    @if ($product->purchases->count() > 1)
                        <tr class="bg-blue-50 font-semibold text-xs border-t border-blue-200">
                            <td></td>
                            <td></td>
                            <td class="px-3 py-1 italic text-gray-500" colspan="2">{{ $product->name }} — {{ $product->purchases->count() }} batches total</td>
                            <td class="px-3 py-1 text-right tabular-nums">@money($product->batch_cost_value)</td>
                            <td class="px-3 py-1 text-right tabular-nums">@money($product->batch_sale_value)</td>
                            <td class="px-3 py-1 text-right tabular-nums">{{ $product->total_stock }}</td>
                            <td></td>
                        </tr>
                    @endif
                @endif
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">No products found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-navy-800 text-white font-bold">
            <tr>
                <td colspan="4" class="px-3 py-2 text-right">Grand Totals:</td>
                <td class="px-3 py-2 text-right tabular-nums">@money($grandCostValue)</td>
                <td class="px-3 py-2 text-right tabular-nums">@money($grandSaleValue)</td>
                <td class="px-3 py-2 text-right tabular-nums">
                    {{ $products->sum('total_stock') }}
                </td>
                <td></td>
            </tr>
            <tr class="bg-navy-700 text-xs">
                <td colspan="4" class="px-3 py-1.5 text-right text-navy-200">Gross Profit Potential:</td>
                <td class="px-3 py-1.5 text-right tabular-nums text-green-300" colspan="2">@money($grandSaleValue - $grandCostValue)</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>
