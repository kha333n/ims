<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProductList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    // Action summary
    public ?array $actionSummary = null;

    // Modal state
    public bool $showModal = false;

    public ?int $editingProductId = null;

    // Form fields
    public string $name = '';

    public string $sale_price = '';

    public string $purchase_price = '';

    public int $quantity = 0;

    public ?int $supplier_id = null;

    public string $brand = '';

    public string $model_number = '';

    public string $color = '';

    public string $category = '';

    public string $notes = '';

    public $image = null;

    public ?string $existing_image = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->editingProductId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->sale_price = (string) ($product->sale_price / 100);
        $this->purchase_price = (string) ($product->purchase_price / 100);
        $this->quantity = $product->quantity;
        $this->supplier_id = $product->supplier_id;
        $this->brand = $product->brand ?? '';
        $this->model_number = $product->model_number ?? '';
        $this->color = $product->color ?? '';
        $this->category = $product->category ?? '';
        $this->notes = $product->notes ?? '';
        $this->existing_image = $product->image_path;
        $this->image = null;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'brand' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
        ];

        $this->validate($rules);

        $imagePath = $this->existing_image;
        if ($this->image) {
            $imagePath = $this->image->store('product-images', 'local');
        }

        $data = [
            'name' => $this->name,
            'sale_price' => parseMoney($this->sale_price),
            'purchase_price' => parseMoney($this->purchase_price),
            'quantity' => $this->quantity,
            'supplier_id' => $this->supplier_id ?: null,
            'brand' => $this->brand ?: null,
            'model_number' => $this->model_number ?: null,
            'color' => $this->color ?: null,
            'category' => $this->category ?: null,
            'notes' => $this->notes ?: null,
            'image_path' => $imagePath,
        ];

        $action = $this->editingProductId ? 'Updated' : 'Added';

        if ($this->editingProductId) {
            Product::findOrFail($this->editingProductId)->update($data);
        } else {
            Product::create($data);
        }

        $this->actionSummary = [
            'action' => $action,
            'name' => $this->name,
            'sale_price' => parseMoney($this->sale_price),
            'purchase_price' => parseMoney($this->purchase_price),
            'quantity' => $this->quantity,
        ];

        $this->showModal = false;
        $this->resetForm();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public ?int $confirmingDeleteId = null;

    public string $deleteError = '';

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

    public function deleteProduct(): void
    {
        $product = Product::findOrFail($this->confirmingDeleteId);

        $hasActiveItems = $product->accountItems()
            ->whereHas('account', fn ($q) => $q->where('status', 'active'))
            ->exists();

        if ($hasActiveItems) {
            $this->deleteError = 'Cannot delete: this product has active account items.';

            return;
        }

        $this->actionSummary = [
            'action' => 'Deleted',
            'name' => $product->name,
            'sale_price' => $product->sale_price,
            'purchase_price' => $product->purchase_price,
            'quantity' => $product->quantity,
        ];

        $product->delete();
        $this->confirmingDeleteId = null;
        $this->deleteError = '';
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'sale_price', 'purchase_price', 'quantity', 'supplier_id', 'brand', 'model_number', 'color', 'category', 'notes', 'image', 'existing_image', 'editingProductId']);
        $this->resetValidation();
    }

    public function render()
    {
        $products = Product::query()
            ->with('supplier')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(50);

        $suppliers = Supplier::orderBy('name')->get();

        return view('livewire.inventory.product-list', [
            'products' => $products,
            'suppliers' => $suppliers,
        ]);
    }
}
