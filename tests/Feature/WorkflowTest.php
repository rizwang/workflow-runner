<?php

namespace Tests\Feature;

use App\Models\Run;
use App\Models\RunLog;
use App\Models\Step;
use App\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a workflow.
     */
    public function test_can_create_workflow(): void
    {
        $response = $this->post(route('workflows.store'), [
            'name' => 'Test Workflow',
            'description' => 'This is a test workflow',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workflows', [
            'name' => 'Test Workflow',
            'description' => 'This is a test workflow',
        ]);
    }

    /**
     * Test running a workflow creates logs and updates status.
     */
    public function test_running_workflow_creates_logs(): void
    {
        // Create a workflow with steps
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test workflow',
        ]);

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

        // Run the workflow
        $response = $this->post(route('runs.store', $workflow));

        $response->assertRedirect();

        // Assert that a run was created
        $this->assertDatabaseHas('runs', [
            'workflow_id' => $workflow->id,
        ]);

        $run = Run::where('workflow_id', $workflow->id)->first();

        // Assert run status is updated (should be succeeded or failed, not running)
        $this->assertNotNull($run->status);
        $this->assertContains($run->status, ['succeeded', 'failed'], 'Run status should be succeeded or failed');
        $this->assertNotNull($run->started_at, 'Run should have started_at timestamp');
        $this->assertNotNull($run->completed_at, 'Run should have completed_at timestamp');

        // Assert that logs were created
        $this->assertTrue($run->logs()->count() > 0, 'Run should have logs');

        // Assert that logs have the required fields
        $log = $run->logs()->first();
        $this->assertNotNull($log->level);
        $this->assertContains($log->level, ['info', 'warn', 'error'], 'Log level should be info, warn, or error');
        $this->assertNotNull($log->message);
        $this->assertNotNull($log->logged_at);

        // Assert that logs are associated with the run
        $this->assertEquals($run->id, $log->run_id, 'Log should be associated with the run');

        // Assert that at least one log has a step reference (step-specific logs)
        $stepLogs = $run->logs()->whereNotNull('step_id')->count();
        $this->assertGreaterThan(0, $stepLogs, 'At least one log should reference a step');

        // Assert final status log exists
        $finalLog = $run->logs()->whereNull('step_id')->first();
        $this->assertNotNull($finalLog, 'Should have a final workflow completion log');
        $this->assertStringContainsString('Workflow execution', $finalLog->message);
    }

    /**
     * Test that workflow run status is properly set to succeeded on success.
     */
    public function test_workflow_run_status_is_succeeded_on_success(): void
    {
        $workflow = Workflow::create([
            'name' => 'Success Test Workflow',
            'description' => 'Test workflow',
        ]);

        Step::create([
            'workflow_id' => $workflow->id,
            'type' => 'delay',
            'config' => ['seconds' => 1],
            'order' => 0,
        ]);

        $this->post(route('runs.store', $workflow));

        $run = Run::where('workflow_id', $workflow->id)->first();

        // Verify status is succeeded
        $this->assertEquals('succeeded', $run->status);
        $this->assertNotNull($run->completed_at);
        $this->assertNotNull($run->started_at);

        // Verify logs contain success message
        $successLog = $run->logs()->where('message', 'like', '%completed successfully%')->first();
        $this->assertNotNull($successLog);
        $this->assertEquals('info', $successLog->level);
    }
}
