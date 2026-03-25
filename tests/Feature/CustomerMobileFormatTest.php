<?php

namespace Tests\Feature;

use App\Livewire\Customers\AddCustomer;
use App\Livewire\Customers\CustomerDetail;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerMobileFormatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->actingAs($user);
    }

    public function test_customer_can_be_created_with_mobile_2(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Test Customer')
            ->set('mobile', '0312-1234567')
            ->set('mobile_2', '0300-7654321')
            ->set('cnic', '35202-1234567-1')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'mobile' => '0312-1234567',
            'mobile_2' => '0300-7654321',
            'cnic' => '35202-1234567-1',
        ]);
    }

    public function test_mobile_format_validation_rejects_invalid(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Test')
            ->set('mobile', '123456')
            ->call('save')
            ->assertHasErrors(['mobile']);
    }

    public function test_mobile_format_accepts_without_dash(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Test')
            ->set('mobile', '03121234567')
            ->call('save')
            ->assertHasNoErrors(['mobile']);
    }

    public function test_cnic_format_validation_rejects_invalid(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Test')
            ->set('cnic', 'ABC123')
            ->call('save')
            ->assertHasErrors(['cnic']);
    }

    public function test_cnic_format_accepts_with_dashes(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Test')
            ->set('cnic', '35202-1234567-1')
            ->call('save')
            ->assertHasNoErrors(['cnic']);
    }

    public function test_cnic_format_accepts_without_dashes(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Test')
            ->set('cnic', '3520212345671')
            ->call('save')
            ->assertHasNoErrors(['cnic']);
    }

    public function test_mobile_2_is_optional(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Test No Mobile2')
            ->call('save')
            ->assertHasNoErrors(['mobile_2']);

        $this->assertDatabaseHas('customers', [
            'name' => 'Test No Mobile2',
            'mobile_2' => null,
        ]);
    }

    public function test_customer_detail_can_update_mobile_2(): void
    {
        $customer = Customer::create(['name' => 'Existing', 'mobile' => '0312-1234567']);

        Livewire::test(CustomerDetail::class, ['id' => $customer->id])
            ->call('startEdit')
            ->set('mobile_2', '0345-9876543')
            ->call('saveCustomer')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'mobile_2' => '0345-9876543',
        ]);
    }
}
