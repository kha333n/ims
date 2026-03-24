<?php

namespace Tests\Feature;

use App\Livewire\HR\RecoveryManList;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecoveryManListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsOwner();
    }

    public function test_page_loads(): void
    {
        $this->get(route('hr.recovery-men'))
            ->assertStatus(200)
            ->assertSee('Recovery Men');
    }

    public function test_shows_recovery_men_only(): void
    {
        Employee::create(['name' => 'Ali SM', 'type' => 'sale_man']);
        Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man']);

        Livewire::test(RecoveryManList::class)
            ->assertSee('Hassan RM')
            ->assertDontSee('Ali SM');
    }

    public function test_search_filters_by_name(): void
    {
        Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man']);
        Employee::create(['name' => 'Bilal RM', 'type' => 'recovery_man']);

        Livewire::test(RecoveryManList::class)
            ->set('search', 'Hassan')
            ->assertSee('Hassan RM')
            ->assertDontSee('Bilal RM');
    }

    public function test_can_create_recovery_man(): void
    {
        Livewire::test(RecoveryManList::class)
            ->call('openAddModal')
            ->set('name', 'New RM')
            ->set('area', 'Saddar')
            ->set('rank', 'Senior')
            ->set('salary', 25000)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('employees', [
            'name' => 'New RM',
            'type' => 'recovery_man',
            'area' => 'Saddar',
            'rank' => 'Senior',
            'salary' => 25000,
        ]);
    }

    public function test_can_edit_recovery_man(): void
    {
        $emp = Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man', 'area' => 'Saddar']);

        Livewire::test(RecoveryManList::class)
            ->call('openEditModal', $emp->id)
            ->assertSet('name', 'Hassan RM')
            ->assertSet('area', 'Saddar')
            ->set('area', 'Satellite Town')
            ->call('save');

        $this->assertDatabaseHas('employees', ['id' => $emp->id, 'area' => 'Satellite Town']);
    }

    public function test_can_delete_recovery_man(): void
    {
        $emp = Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man']);

        Livewire::test(RecoveryManList::class)
            ->call('confirmDelete', $emp->id)
            ->call('deleteEmployee');

        $this->assertSoftDeleted('employees', ['id' => $emp->id]);
    }

    public function test_cannot_delete_recovery_man_with_active_accounts(): void
    {
        $emp = Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man']);
        $customer = Customer::create(['name' => 'Test']);
        Account::create([
            'customer_id' => $customer->id,
            'recovery_man_id' => $emp->id,
            'total_amount' => 100000,
            'remaining_amount' => 100000,
            'installment_type' => 'monthly',
            'installment_amount' => 10000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);

        Livewire::test(RecoveryManList::class)
            ->call('confirmDelete', $emp->id)
            ->call('deleteEmployee')
            ->assertSet('deleteError', 'Cannot delete: this recovery man has active accounts.');

        $this->assertDatabaseHas('employees', ['id' => $emp->id, 'deleted_at' => null]);
    }

    public function test_shows_empty_state(): void
    {
        Livewire::test(RecoveryManList::class)
            ->assertSee('No recovery men have been added yet.');
    }
}
