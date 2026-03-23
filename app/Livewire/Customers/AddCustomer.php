<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AddCustomer extends Component
{
    public string $name = '';

    public string $father_name = '';

    public string $mobile = '';

    public string $cnic = '';

    public string $reference = '';

    public string $home_address = '';

    public string $shop_address = '';

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'reference' => 'nullable|string|max:255',
            'home_address' => 'nullable|string|max:500',
            'shop_address' => 'nullable|string|max:500',
        ]);

        $customer = Customer::create([
            'name' => $this->name,
            'father_name' => $this->father_name ?: null,
            'mobile' => $this->mobile ?: null,
            'cnic' => $this->cnic ?: null,
            'reference' => $this->reference ?: null,
            'home_address' => $this->home_address ?: null,
            'shop_address' => $this->shop_address ?: null,
        ]);

        $this->redirect(route('customers.show', $customer->id));
    }

    public function render()
    {
        return view('livewire.customers.add-customer');
    }
}
