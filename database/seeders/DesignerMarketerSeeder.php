<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designer;
use App\Models\Marketer;
use Illuminate\Support\Facades\Hash;

class DesignerMarketerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Designers
        $designers = [
            [
                'username' => 'ahmed_designer',
                'email' => 'designer1@bareqq.com',
                'phone' => '01000000001',
                'password' => Hash::make('123456'),
                'role' => 'designer',
                'admin_id' => 1, // Assuming admin with ID 1 exists
            ],
            [
                'username' => 'sara_designer',
                'email' => 'designer2@bareqq.com',
                'phone' => '01000000002',
                'password' => Hash::make('123456'),
                'role' => 'designer',
                'admin_id' => 1,
            ],
        ];

        foreach ($designers as $designer) {
            Designer::firstOrCreate(
                ['email' => $designer['email']],
                $designer
            );
            $this->command->info("Created designer: {$designer['username']}");
        }

        // Create Marketers
        $marketers = [
            [
                'username' => 'mohamed_marketer',
                'email' => 'marketer1@bareqq.com',
                'phone' => '01000000003',
                'password' => Hash::make('123456'),
                'role' => 'marketer',
                'admin_id' => 1,
            ],
            [
                'username' => 'fatima_marketer',
                'email' => 'marketer2@bareqq.com',
                'phone' => '01000000004',
                'password' => Hash::make('123456'),
                'role' => 'marketer',
                'admin_id' => 1,
            ],
        ];

        foreach ($marketers as $marketer) {
            Marketer::firstOrCreate(
                ['email' => $marketer['email']],
                $marketer
            );
            $this->command->info("Created marketer: {$marketer['username']}");
        }

        $this->command->info('Designers and Marketers seeded successfully!');
    }
}
