<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SupplierList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?array $actionSummary = null;

    // Modal state
    public bool $showModal = false;

    public ?int $editingId = null;

    // Form fields
    public string $name = '';

    public string $phone = '';

    public string $contact_person = '';

    public string $address = '';

    public string $notes = '';

    // Delete state
    public ?int $confirmingDeleteId = null;

    public string $deleteError = '';

    // Pricing state
    public ?int $pricingSupplier = null;

    public bool $showPricingModal = false;

    /** @var array<int, array{product_id: int, name: string, price: string}> */
    public array $pricingRows = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->editingId = $supplier->id;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone ?? '';
        $this->contact_person = $supplier->contact_person ?? '';
        $this->address = $supplier->address ?? '';
        $this->notes = $supplier->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data = [
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'contact_person' => $this->contact_person ?: null,
            'address' => $this->address ?: null,
            'notes' => $this->notes ?: null,
        ];

        $action = $this->editingId ? 'Updated' : 'Added';

        if ($this->editingId) {
            Supplier::findOrFail($this->editingId)->update($data);
        } else {
            Supplier::create($data);
        }

        $this->actionSummary = ['action' => $action, 'name' => $this->name];
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
        $this->deleteError = '';
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->deleteError = '';
    }

    public function deleteSupplier(): void
    {
        $supplier = Supplier::findOrFail($this->confirmingDeleteId);

        if ($supplier->purchases()->exists()) {
            $this->deleteError = 'Cannot delete: this supplier has purchase history.';

            return;
        }

        $this->actionSummary = ['action' => 'Deleted', 'name' => $supplier->name];
        $supplier->delete();
        $this->confirmingDeleteId = null;
        $this->deleteError = '';
    }

    // ── Pricing ─────────────────────────────────────────────

    public function openPricing(int $supplierId): void
    {
        $this->pricingSupplier = $supplierId;
        $supplier = Supplier::findOrFail($supplierId);

        $existingPrices = SupplierProduct::where('supplier_id', $supplierId)
            ->pluck('unit_price', 'product_id')
            ->toArray();

        $this->pricingRows = Product::orderBy('name')->get()->map(fn (Product $p) => [
            'product_id' => $p->id,
            'name' => $p->name,
            'price' => isset($existingPrices[$p->id]) ? (string) ($existingPrices[$p->id] / 100) : '',
        ])->toArray();

        $this->showPricingModal = true;
    }

    public function savePricing(): void
    {
        if (! $this->pricingSupplier) {
            return;
        }

        $this->validate([
            'pricingRows.*.price' => 'nullable|numeric|min:0',
        ], [
            'pricingRows.*.price.numeric' => 'Each price must be a valid number.',
            'pricingRows.*.price.min' => 'Price cannot be negative.',
        ]);

        foreach ($this->pricingRows as $row) {
            $price = trim($row['price']);
            if ($price === '') {
                // Remove if exists
                SupplierProduct::where('supplier_id', $this->pricingSupplier)
                    ->where('product_id', $row['product_id'])
                    ->delete();

                continue;
            }

            SupplierProduct::updateOrCreate(
                ['supplier_id' => $this->pricingSupplier, 'product_id' => $row['product_id']],
                ['unit_price' => parseMoney($price)]
            );
        }

        $supplier = Supplier::find($this->pricingSupplier);
        $count = SupplierProduct::where('supplier_id', $this->pricingSupplier)->count();
        $this->actionSummary = ['action' => 'Pricing Updated', 'name' => $supplier->name." ({$count} products)"];

        $this->showPricingModal = false;
        $this->pricingSupplier = null;
    }

    public function closePricing(): void
    {
        $this->showPricingModal = false;
        $this->pricingSupplier = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'contact_person', 'address', 'notes']);
        $this->resetValidation();
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('id', $this->search))
            ->withCount('supplierProducts')
            ->orderBy('name')
            ->paginate(50);

        return view('livewire.inventory.supplier-list', [
            'suppliers' => $suppliers,
        ]);
    }
}
