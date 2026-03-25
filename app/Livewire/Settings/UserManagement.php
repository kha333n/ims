<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

#[Layout('components.layouts.app')]
class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public ?array $actionSummary = null;

    // Edit modal
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $edit_name = '';

    public string $edit_username = '';

    public string $edit_password = '';

    public string $edit_role = 'sale_man';

    public bool $edit_is_active = true;

    /** @var array<string, bool> */
    public array $edit_permissions = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openEditModal(int $id): void
    {
        $user = User::findOrFail($id);
        if ($user->username === 'admin') {
            return;
        }
        $this->editingId = $user->id;
        $this->edit_name = $user->name;
        $this->edit_username = $user->username;
        $this->edit_password = '';
        $this->edit_role = $user->role;
        $this->edit_is_active = $user->is_active;

        $userPerms = $user->getDirectPermissions()->pluck('name')->toArray();
        $this->edit_permissions = [];
        foreach (Permission::all() as $perm) {
            $this->edit_permissions[$perm->name] = in_array($perm->name, $userPerms) || $user->hasPermissionTo($perm->name);
        }

        $this->showModal = true;
    }

    public function saveUser(): void
    {
        $user = User::find($this->editingId);
        if ($user && $user->username === 'admin') {
            return;
        }

        $rules = [
            'edit_name' => 'required|string|max:255',
            'edit_username' => 'required|string|max:50|unique:users,username,'.$this->editingId,
            'edit_role' => 'required|in:owner,sale_man,recovery_man',
            'edit_is_active' => 'required|boolean',
        ];

        if ($this->edit_password) {
            $rules['edit_password'] = 'string|min:6';
        }

        $this->validate($rules);

        $user = User::findOrFail($this->editingId);

        $user->update([
            'name' => $this->edit_name,
            'username' => $this->edit_username,
            'role' => $this->edit_role,
            'is_active' => $this->edit_is_active,
        ]);

        if ($this->edit_password) {
            $user->update(['password' => $this->edit_password]);
        }

        // Sync role
        $user->syncRoles([$this->edit_role]);

        // Sync direct permissions (only extras beyond role defaults)
        $rolePerms = $user->getPermissionsViaRoles()->pluck('name')->toArray();
        $directPerms = [];
        foreach ($this->edit_permissions as $perm => $enabled) {
            if ($enabled && ! in_array($perm, $rolePerms)) {
                $directPerms[] = $perm;
            }
        }
        $user->syncPermissions($directPerms);

        $this->actionSummary = ['action' => 'Updated', 'name' => $this->edit_name];
        $this->showModal = false;
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        if ($user->username === 'admin') {
            return;
        }
        if ($user->isOwner() && User::where('role', 'owner')->where('is_active', true)->count() <= 1) {
            $this->actionSummary = ['action' => 'Error', 'name' => 'Cannot deactivate the last active owner.'];

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $this->actionSummary = ['action' => $user->is_active ? 'Activated' : 'Deactivated', 'name' => $user->name];
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $users = User::query()
            ->where('username', '!=', 'admin')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('username', 'like', "%{$this->search}%"))
            ->with('employee')
            ->orderByRaw("CASE role WHEN 'owner' THEN 0 WHEN 'sale_man' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->paginate(50);

        $allPermissions = Permission::orderBy('name')->get();

        return view('livewire.settings.user-management', [
            'users' => $users,
            'allPermissions' => $allPermissions,
        ]);
    }
}
