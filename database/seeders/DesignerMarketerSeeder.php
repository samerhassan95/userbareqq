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
                'name' => 'Ahmed Designer',
                'email' => 'designer1@bareqq.com',
                'phone' => '01000000001',
                'password' => Hash::make('123456'),
            ],
            [
                'name' => 'Sara Designer',
                'email' => 'designer2@bareqq.com',
                'phone' => '01000000002',
                'password' => Hash::make('123456'),
            ],
        ];

        foreach ($designers as $designer) {
            Designer::firstOrCreate(
                ['email' => $designer['email']],
                $designer
            );
            $this->command->info("Created designer: {$designer['name']}");
        }

        // Create Marketers
        $marketers = [
            [
                'name' => 'Mohamed Marketer',
                'email' => 'marketer1@bareqq.com',
                'phone' => '01000000003',
                'password' => Hash::make('123456'),
            ],
            [
                'name' => 'Fatima Marketer',
                'email' => 'marketer2@bareqq.com',
                'phone' => '01000000004',
                'password' => Hash::make('123456'),
            ],
        ];

        foreach ($marketers as $marketer) {
            Marketer::firstOrCreate(
                ['email' => $marketer['email']],
                $marketer
            );
            $this->command->info("Created marketer: {$marketer['name']}");
        }

        $this->command->info('Designers and Marketers seeded successfully!');
    }
}
