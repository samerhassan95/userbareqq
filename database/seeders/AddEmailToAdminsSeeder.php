<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AddEmailToAdminsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing admins with email based on their username
        $admins = Admin::whereNull('email')->get();
        
        foreach ($admins as $admin) {
            // Generate email from username if not exists
            $email = $admin->username . '@bareqq.com';
            
            $admin->update([
                'email' => $email
            ]);
            
            $this->command->info("Updated admin: {$admin->username} with email: {$email}");
        }
        
        $this->command->info('All admins updated with emails successfully!');
    }
}
