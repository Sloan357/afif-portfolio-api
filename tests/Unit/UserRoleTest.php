<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Panel;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_role_helpers_identify_each_role(): void
    {
        $owner = new User(['role' => UserRole::Owner]);
        $editor = new User(['role' => UserRole::Editor]);
        $translator = new User(['role' => UserRole::Translator]);

        $this->assertTrue($owner->isOwner());
        $this->assertFalse($owner->isEditor());
        $this->assertFalse($owner->isTranslator());

        $this->assertTrue($editor->isEditor());
        $this->assertFalse($editor->isOwner());
        $this->assertFalse($editor->isTranslator());

        $this->assertTrue($translator->isTranslator());
        $this->assertFalse($translator->isOwner());
        $this->assertFalse($translator->isEditor());
    }

    public function test_active_users_with_supported_roles_can_access_filament_panel(): void
    {
        $panel = Panel::make();

        foreach (UserRole::cases() as $role) {
            $user = new User([
                'role' => $role,
                'is_active' => true,
            ]);

            $this->assertTrue($user->canAccessPanel($panel));
        }
    }

    public function test_inactive_users_cannot_access_filament_panel(): void
    {
        $user = new User([
            'role' => UserRole::Owner,
            'is_active' => false,
        ]);

        $this->assertFalse($user->canAccessPanel(Panel::make()));
    }
}
