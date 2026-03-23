<?php

namespace App\Livewire\HR;

use App\Models\Employee;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RecoveryManList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?array $actionSummary = null;

    public bool $showModal = false;

    public ?int $editingId = null;

    // Form fields
    public string $name = '';

    public string $phone = '';

    public string $cnic = '';

    public string $address = '';

    public string $area = '';

    public string $rank = '';

    public int $salary = 0;

    // Delete
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
        $employee = Employee::recoveryMen()->findOrFail($id);
        $this->editingId = $employee->id;
        $this->name = $employee->name;
        $this->phone = $employee->phone ?? '';
        $this->cnic = $employee->cnic ?? '';
        $this->address = $employee->address ?? '';
        $this->area = $employee->area ?? '';
        $this->rank = $employee->rank ?? '';
        $this->salary = $employee->salary ?? 0;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'area' => 'nullable|string|max:255',
            'rank' => 'nullable|string|max:255',
            'salary' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'type' => 'recovery_man',
            'phone' => $this->phone ?: null,
            'cnic' => $this->cnic ?: null,
            'address' => $this->address ?: null,
            'area' => $this->area ?: null,
            'rank' => $this->rank ?: null,
            'salary' => $this->salary,
        ];

        $action = $this->editingId ? 'Updated' : 'Added';

        if ($this->editingId) {
            Employee::findOrFail($this->editingId)->update($data);
        } else {
            Employee::create($data);
        }

        $this->actionSummary = ['action' => $action, 'name' => $this->name, 'area' => $this->area ?: '—'];
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

        if ($employee->accountsAsRecoveryMan()->where('status', 'active')->exists()) {
            $this->deleteError = 'Cannot delete: this recovery man has active accounts.';

            return;
        }

        $this->actionSummary = ['action' => 'Deleted', 'name' => $employee->name, 'area' => $employee->area ?? '—'];
        $employee->delete();
        $this->confirmingDeleteId = null;
        $this->deleteError = '';
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'cnic', 'address', 'area', 'rank', 'salary']);
        $this->resetValidation();
    }

    public function render()
    {
        $employees = Employee::recoveryMen()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(50);

        return view('livewire.h-r.recovery-man-list', [
            'employees' => $employees,
        ]);
    }
}
