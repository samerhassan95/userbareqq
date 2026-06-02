<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\ProductOrder;
use App\Http\Controllers\Client\ClientMeetingController;
use Illuminate\Http\Request;

class TestMeetingStore extends Command
{
    protected $signature = 'test:meeting-store';

    public function handle()
    {
        $client = Client::first();
        if (!$client) {
            $client = Client::create([
                'name' => 'Test Client',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        }
        
        auth()->guard('client')->setUser($client);
        auth()->shouldUse('client');

        $controller = new ClientMeetingController();
        $request = Request::create('/api/client/meetings', 'POST', [
            'date' => '2026-06-15',
            'start_time' => '10:00',
            'strategy_id' => null,
            'meeting_name' => 'Project Kickoff',
            'description' => 'Kickoff call',
            'end_time' => '11:00'
        ]);
        $request->setUserResolver(function () use ($client) {
            return $client;
        });

        try {
            $response = $controller->store($request);
            $this->info("Status: " . $response->getStatusCode());
            $this->info("Content: " . $response->getContent());
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
        }
    }
}
