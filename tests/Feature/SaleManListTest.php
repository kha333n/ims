<?php

namespace Tests\Feature;

use App\Livewire\HR\SaleManList;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SaleManListTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_loads(): void
    {
        $this->get(route('hr.sale-men'))
            ->assertStatus(200)
            ->assertSee('Sale Men');
    }

    public function test_shows_sale_men_only(): void
    {
        Employee::create(['name' => 'Ali SM', 'type' => 'sale_man']);
        Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man']);

        Livewire::test(SaleManList::class)
            ->assertSee('Ali SM')
            ->assertDontSee('Hassan RM');
    }

    public function test_search_filters_by_name(): void
    {
        Employee::create(['name' => 'Ali SM', 'type' => 'sale_man']);
        Employee::create(['name' => 'Bilal SM', 'type' => 'sale_man']);

        Livewire::test(SaleManList::class)
            ->set('search', 'Ali')
            ->assertSee('Ali SM')
            ->assertDontSee('Bilal SM');
    }

    public function test_can_create_sale_man(): void
    {
        Livewire::test(SaleManList::class)
            ->call('openAddModal')
            ->set('name', 'New Sale Man')
            ->set('phone', '0300-1234567')
            ->set('cnic', '12345-1234567-1')
            ->set('commission_percent', 5)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('employees', [
            'name' => 'New Sale Man',
            'type' => 'sale_man',
            'phone' => '0300-1234567',
            'commission_percent' => 5,
        ]);
    }

    public function test_create_validates_name_required(): void
    {
        Livewire::test(SaleManList::class)
            ->call('openAddModal')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_can_edit_sale_man(): void
    {
        $emp = Employee::create(['name' => 'Ali SM', 'type' => 'sale_man', 'commission_percent' => 3]);

        Livewire::test(SaleManList::class)
            ->call('openEditModal', $emp->id)
            ->assertSet('name', 'Ali SM')
            ->set('name', 'Updated Ali')
            ->set('commission_percent', 7)
            ->call('save');

        $this->assertDatabaseHas('employees', [
            'id' => $emp->id,
            'name' => 'Updated Ali',
            'commission_percent' => 7,
        ]);
    }

    public function test_can_delete_sale_man(): void
    {
        $emp = Employee::create(['name' => 'Ali SM', 'type' => 'sale_man']);

        Livewire::test(SaleManList::class)
            ->call('confirmDelete', $emp->id)
            ->call('deleteEmployee');

        $this->assertSoftDeleted('employees', ['id' => $emp->id]);
    }

    public function test_cannot_delete_sale_man_with_active_accounts(): void
    {
        $emp = Employee::create(['name' => 'Ali SM', 'type' => 'sale_man']);
        $customer = Customer::create(['name' => 'Test']);
        Account::create([
            'customer_id' => $customer->id,
            'sale_man_id' => $emp->id,
            'total_amount' => 100000,
            'remaining_amount' => 100000,
            'installment_type' => 'monthly',
            'installment_amount' => 10000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);

        Livewire::test(SaleManList::class)
            ->call('confirmDelete', $emp->id)
            ->call('deleteEmployee')
            ->assertSet('deleteError', 'Cannot delete: this sale man has active accounts.');

        $this->assertDatabaseHas('employees', ['id' => $emp->id, 'deleted_at' => null]);
    }

    public function test_shows_empty_state(): void
    {
        Livewire::test(SaleManList::class)
            ->assertSee('No sale men have been added yet.');
    }
}
