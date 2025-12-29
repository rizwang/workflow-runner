<?php

namespace Database\Seeders;

use App\Models\Step;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create a sample workflow
        $workflow = Workflow::create([
            'name' => 'Rizwan Created Sample Workflow',
            'description' => 'A sample workflow with delay and HTTP check steps by RG.',
        ]);

        // Add steps
        Step::create([
            'workflow_id' => $workflow->id,
            'type' => 'delay',
            'config' => ['seconds' => 1],
            'order' => 0,
        ]);

        Step::create([
            'workflow_id' => $workflow->id,
            'type' => 'http_check',
            'config' => ['url' => 'https://example.com'],
            'order' => 1,
        ]);

        Step::create([
            'workflow_id' => $workflow->id,
            'type' => 'delay',
            'config' => ['seconds' => 2],
            'order' => 2,
        ]);
    }
}
