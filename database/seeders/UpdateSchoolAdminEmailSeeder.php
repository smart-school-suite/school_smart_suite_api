<?php

namespace Database\Seeders;

use App\Models\Schooladmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateSchoolAdminEmailSeeder extends Seeder
{
    private const CURRENT_EMAIL = 'fonnathalie892@gmail.com';

    private const NEW_EMAIL = 'fonnathalie@gmail.com';

    public function run(): void
    {
        $admins = $this->adminsWithEmail(self::CURRENT_EMAIL);

        if ($admins->isEmpty()) {
            $this->command?->info('No school admin uses ' . self::CURRENT_EMAIL . '. Nothing to update.');
            return;
        }

        if ($admins->count() > 1) {
            $this->command?->warn($admins->count() . ' school admins use ' . self::CURRENT_EMAIL . '. Update them individually; nothing was changed.');
            return;
        }

        $admin = $admins->first();

        $emailTaken = $this->adminsWithEmail(self::NEW_EMAIL)
            ->contains(fn (Schooladmin $other) => $other->getKey() !== $admin->getKey());

        if ($emailTaken) {
            $this->command?->warn('Another school admin already uses ' . self::NEW_EMAIL . '; nothing was changed.');
            return;
        }

        DB::transaction(fn () => $admin->forceFill(['email' => self::NEW_EMAIL])->save());

        $this->command?->info("School admin {$admin->getKey()} email updated from " . self::CURRENT_EMAIL . ' to ' . self::NEW_EMAIL . '.');
    }

    private function adminsWithEmail(string $email)
    {
        return Schooladmin::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->get();
    }
}
