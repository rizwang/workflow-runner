<?php

namespace App\Services;

use App\Models\Run;
use App\Models\RunLog;
use App\Models\Step;
use App\Models\Workflow;
use Illuminate\Support\Facades\Http;

class WorkflowExecutionService
{
    public function execute(Workflow $workflow): Run
    {
        $run = Run::create([
            'workflow_id' => $workflow->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $steps = $workflow->steps;
            
            foreach ($steps as $step) {
                $this->executeStep($run, $step);
            }

            $run->update([
                'status' => 'succeeded',
                'completed_at' => now(),
            ]);

            $this->log($run, null, 'info', 'Workflow execution completed successfully');
        } catch (\Exception $e) {
            $run->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);

            $this->log($run, null, 'error', 'Workflow execution failed: ' . $e->getMessage());
        }

        return $run->fresh();
    }

    protected function executeStep(Run $run, Step $step): void
    {
        $this->log($run, $step, 'info', "Executing step: {$step->type}");

        try {
            match ($step->type) {
                'delay' => $this->executeDelayStep($run, $step),
                'http_check' => $this->executeHttpCheckStep($run, $step),
                default => throw new \InvalidArgumentException("Unknown step type: {$step->type}"),
            };

            $this->log($run, $step, 'info', "Step completed successfully");
        } catch (\Exception $e) {
            $this->log($run, $step, 'error', "Step failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function executeDelayStep(Run $run, Step $step): void
    {
        $config = $step->config;
        
        if (!isset($config['seconds'])) {
            throw new \InvalidArgumentException('Delay step requires "seconds" in config');
        }

        $seconds = min((int) $config['seconds'], 2); // Cap at 2 seconds
        
        $this->log($run, $step, 'info', "Delaying for {$seconds} second(s)");
        
        sleep($seconds);
        
        $this->log($run, $step, 'info', "Delay completed");
    }

    protected function executeHttpCheckStep(Run $run, Step $step): void
    {
        $config = $step->config;
        
        if (!isset($config['url'])) {
            throw new \InvalidArgumentException('HTTP check step requires "url" in config');
        }

        $url = $config['url'];
        
        $this->log($run, $step, 'info', "Checking URL: {$url}");

        try {
            $response = Http::timeout(2)->get($url);
            $statusCode = $response->status();
            $success = $response->successful();

            $this->log(
                $run,
                $step,
                $success ? 'info' : 'warn',
                "HTTP check completed. Status: {$statusCode}, Success: " . ($success ? 'Yes' : 'No')
            );
        } catch (\Exception $e) {
            $this->log($run, $step, 'error', "HTTP check failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function log(Run $run, ?Step $step, string $level, string $message): void
    {
        RunLog::create([
            'run_id' => $run->id,
            'step_id' => $step?->id,
            'level' => $level,
            'message' => $message,
            'logged_at' => now(),
        ]);
    }
}

