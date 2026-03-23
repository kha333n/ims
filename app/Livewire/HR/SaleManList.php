<?php

namespace App\Livewire\HR;

use App\Models\Employee;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SaleManList extends Component
{
    use WithPagination;

    public string $search = '';

    // Modal state
    public bool $showModal = false;

    public ?int $editingId = null;

    // Form fields
    public string $name = '';

    public string $phone = '';

    public string $cnic = '';

    public string $address = '';

    public int $commission_percent = 0;

    // Delete state
    public ?int $confirmingDeleteId = null;

    public string $deleteError = '';

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
        $employee = Employee::saleMen()->findOrFail($id);
        $this->editingId = $employee->id;
        $this->name = $employee->name;
        $this->phone = $employee->phone ?? '';
        $this->cnic = $employee->cnic ?? '';
        $this->address = $employee->address ?? '';
        $this->commission_percent = $employee->commission_percent ?? 0;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'commission_percent' => 'required|integer|min:0|max:100',
        ]);

        $data = [
            'name' => $this->name,
            'type' => 'sale_man',
            'phone' => $this->phone ?: null,
            'cnic' => $this->cnic ?: null,
            'address' => $this->address ?: null,
            'commission_percent' => $this->commission_percent,
        ];

        if ($this->editingId) {
            Employee::findOrFail($this->editingId)->update($data);
        } else {
            Employee::create($data);
        }

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

    public function deleteEmployee(): void
    {
        $employee = Employee::findOrFail($this->confirmingDeleteId);

        if ($employee->accountsAsSaleMan()->where('status', 'active')->exists()) {
            $this->deleteError = 'Cannot delete: this sale man has active accounts.';

            return;
        }

        $employee->delete();
        $this->confirmingDeleteId = null;
        $this->deleteError = '';
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'cnic', 'address', 'commission_percent']);
        $this->resetValidation();
    }

    public function render()
    {
        $employees = Employee::saleMen()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(50);

        return view('livewire.h-r.sale-man-list', [
            'employees' => $employees,
        ]);
    }
}
