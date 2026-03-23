<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CustomerList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $customers = Customer::query()
            ->withSum(['accounts as total_remaining' => fn ($q) => $q->where('status', 'active')], 'remaining_amount')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('livewire.customers.customer-list', [
            'customers' => $customers,
        ]);
    }
}
