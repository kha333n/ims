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

    public string $mobile_2 = '';

    public string $cnic = '';

    public string $reference = '';

    public string $home_address = '';

    public string $shop_address = '';

    public ?array $savedSummary = null;

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mobile' => ['nullable', 'string', 'max:15', 'regex:/^0\d{3}-?\d{7,8}$/'],
            'mobile_2' => ['nullable', 'string', 'max:15', 'regex:/^0\d{3}-?\d{7,8}$/'],
            'cnic' => ['nullable', 'string', 'max:15', 'regex:/^\d{5}-?\d{7}-?\d$/'],
            'reference' => 'nullable|string|max:255',
            'home_address' => 'nullable|string|max:500',
            'shop_address' => 'nullable|string|max:500',
        ], [
            'mobile.regex' => 'Mobile must be in format 03XX-XXXXXXX or 03XX-XXXXXXXX.',
            'mobile_2.regex' => 'Mobile 2 must be in format 03XX-XXXXXXX or 03XX-XXXXXXXX.',
            'cnic.regex' => 'CNIC must be in format XXXXX-XXXXXXX-X (13 digits).',
        ]);

        $customer = Customer::create([
            'name' => $this->name,
            'father_name' => $this->father_name ?: null,
            'mobile' => $this->mobile ?: null,
            'mobile_2' => $this->mobile_2 ?: null,
            'cnic' => $this->cnic ?: null,
            'reference' => $this->reference ?: null,
            'home_address' => $this->home_address ?: null,
            'shop_address' => $this->shop_address ?: null,
        ]);

        $this->savedSummary = [
            'id' => $customer->id,
            'name' => $customer->name,
            'mobile' => $customer->mobile ?? '—',
            'cnic' => $customer->cnic ?? '—',
        ];

        $this->reset(['name', 'father_name', 'mobile', 'mobile_2', 'cnic', 'reference', 'home_address', 'shop_address']);
    }

    public function render()
    {
        return view('livewire.customers.add-customer');
    }
}
