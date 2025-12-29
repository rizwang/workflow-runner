# Workflow Runner

A Laravel application that allows administrators to define workflows made of steps, run them, and record execution logs.

## Features

- **Workflow Management**: Create, edit, and delete workflows with name and description
- **Step Management**: Add steps to workflows (delay and HTTP check types)
- **Step Reordering**: Reorder steps using up/down controls
- **Workflow Execution**: Run workflows synchronously with real-time logging
- **Run Logs**: View detailed execution logs with levels (info, warn, error)
- **Status Tracking**: Monitor run status (running, succeeded, failed)

## Requirements

- PHP >= 8.2
- Composer
- SQLite (included, no additional setup needed)
- PHP Extensions:
  - PDO
  - SQLite3
  - cURL (for HTTP check steps)
  - JSON
  - OpenSSL

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/rizwang/workflow-runner.git
cd workflow-runner
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Setup

Copy the `.env.example` file to `.env`:

```bash
copy .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 4. Database Setup

The project uses SQLite by default. The database file should already exist at `database/database.sqlite`. If it doesn't exist, create it:

```bash
type nul > database/database.sqlite
```

Or on Windows:

```bash
New-Item -Path database\database.sqlite -ItemType File
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Seed the Database (Optional)

Seed the database with sample data:

```bash
php artisan db:seed
```

This will create:
- A sample workflow named "Rizwan Created Sample Workflow"
- 3 steps (2 delay steps and 1 HTTP check step)

### 7. Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Usage

### Accessing the Application

1. Open your browser and navigate to `http://localhost:8000`
2. You'll be redirected to the workflows list page

### Creating a Workflow

1. Click "Create Workflow" button
2. Enter a name and optional description
3. Click "Create Workflow"

### Adding Steps to a Workflow

1. Open a workflow from the list
2. Scroll to the "Add Step" section
3. Select a step type:
   - **Delay**: Requires `{"seconds": 1}` in config (capped at 2 seconds)
   - **HTTP Check**: Requires `{"url": "https://example.com"}` in config (timeout ≤ 2 seconds)
4. Enter the JSON configuration
5. Click "Add Step"

### Reordering Steps

1. On the workflow detail page, use the ↑ and ↓ buttons next to each step
2. Steps will be reordered immediately

### Running a Workflow

1. Open a workflow that has at least one step
2. Click the "Run Workflow" button
3. The workflow will execute synchronously
4. You'll be redirected to the run details page showing all logs

### Viewing Logs

- **Per Run**: Click on any run from the workflow detail page or recent runs section
- **All Logs**: Click "All Logs" in the navigation bar to see all logs across all runs

## Testing

Run the test suite:

```bash
php artisan test
```

Or run specific tests:

```bash
php artisan test --filter WorkflowTest
```

### Test Coverage

The test suite includes:
- Workflow creation tests
- Workflow execution tests
- Log creation verification
- Status update verification

## Project Structure

```
workflow-runner/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── RunController.php      # Handles run execution and viewing
│   │       └── WorkflowController.php # Handles workflow CRUD operations
│   ├── Models/
│   │   ├── Run.php                    # Run model
│   │   ├── RunLog.php                 # Run log model
│   │   ├── Step.php                    # Step model
│   │   └── Workflow.php               # Workflow model
│   └── Services/
│       └── WorkflowExecutionService.php # Workflow execution logic
├── database/
│   ├── migrations/                    # Database migrations
│   ├── seeders/
│   │   └── DatabaseSeeder.php        # Sample data seeder
│   └── database.sqlite                # SQLite database file
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php          # Main layout
│       ├── workflows/                 # Workflow views
│       └── runs/                      # Run views
├── routes/
│   └── web.php                        # Application routes
└── tests/
    └── Feature/
        └── WorkflowTest.php           # Feature tests
```

## Step Types

### Delay Step

Pauses execution for a specified number of seconds.

**Configuration:**
```json
{
  "seconds": 1
}
```

**Notes:**
- Maximum delay is capped at 2 seconds for testing purposes
- Actual sleep time will be min(seconds, 2)

### HTTP Check Step

Performs a GET request to a URL and logs the response.

**Configuration:**
```json
{
  "url": "https://example.com"
}
```

**Notes:**
- Timeout is set to 2 seconds
- Logs HTTP status code and success status
- Uses Laravel's HTTP client

## Database Schema

### workflows
- `id` - Primary key
- `name` - Workflow name
- `description` - Optional description
- `timestamps` - created_at, updated_at

### steps
- `id` - Primary key
- `workflow_id` - Foreign key to workflows
- `type` - Step type (delay, http_check)
- `config` - JSON configuration
- `order` - Step order for execution
- `timestamps` - created_at, updated_at

### runs
- `id` - Primary key
- `workflow_id` - Foreign key to workflows
- `status` - running, succeeded, failed
- `started_at` - Execution start time
- `completed_at` - Execution completion time
- `timestamps` - created_at, updated_at

### run_logs
- `id` - Primary key
- `run_id` - Foreign key to runs
- `step_id` - Foreign key to steps (nullable)
- `level` - info, warn, error
- `message` - Log message
- `logged_at` - Log timestamp
- `timestamps` - created_at, updated_at

## API Routes

### Workflows
- `GET /workflows` - List all workflows
- `GET /workflows/create` - Show create form
- `POST /workflows` - Store new workflow
- `GET /workflows/{id}` - Show workflow details
- `GET /workflows/{id}/edit` - Show edit form
- `PUT /workflows/{id}` - Update workflow
- `DELETE /workflows/{id}` - Delete workflow

### Steps
- `POST /workflows/{id}/steps` - Add step to workflow
- `POST /workflows/{id}/steps/reorder` - Reorder steps
- `DELETE /workflows/{id}/steps/{step}` - Delete step

### Runs
- `POST /workflows/{id}/runs` - Execute workflow
- `GET /runs/{id}` - Show run details
- `GET /runs-logs` - Show all logs

## Troubleshooting

### Database Issues

If you encounter database errors:

1. Ensure `database/database.sqlite` exists
2. Run migrations: `php artisan migrate:fresh`
3. Re-seed if needed: `php artisan db:seed`

### Permission Issues

On Linux/Mac, ensure the database file is writable:

```bash
chmod 664 database/database.sqlite
```

### HTTP Check Timeout

If HTTP check steps are timing out:
- Verify the URL is accessible
- Check your internet connection
- The timeout is set to 2 seconds maximum

## Development

### Code Quality

The project follows Laravel best practices:
- Clean separation of concerns (Service class for execution logic)
- Proper use of Eloquent relationships
- Comprehensive feature tests
- Validation on all user inputs

### Running Tests

```bash
# Run all tests
php artisan test

```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues or questions, please open an issue in the repository.


# AI Usage Appendix

## AI Tools Used
- Tool(s): Cursor and Google Gemini
- First i upload all files to get the requirement in a net shell and then create a structure and than give prompt to cursor.
- Writing feature tests

## High-impact Prompts

1. **Prompt goal**: Workflow execution service with step handlers and their logs.
   - **What you asked for**: Service class to execute workflows with delay and http_check step types
   - **What you received**: `WorkflowExecutionService` with clean separation of concerns, step execution logic, error handling, and logging
   - **How you validated / adjusted it**: Ran migrations, executed tests, verified all features work as expected


## One example where AI was wrong (required)

- **What the AI suggested**: Initially suggested using Laravel Queues/Jobs for the workflow steps to ensure background processing.
- **Why it was incorrect**: The PROJECT_SPEC.md explicitly states "Synchronous execution is acceptable" and warns to prioritize "Pragmatism" for the 2-hour window. Setting up a queue worker adds unnecessary deployment friction for the reviewer.
- **How you detected the issue**: Reviewed the "Soft Cap" section of the main README.md.
- **What you changed**: Explicitly implemented a synchronous service class that runs in the standard request lifecycle to keep setup as simple as possible.

## Verification Approach

- **Tests you wrote**:
  - `test_can_create_workflow`: Verifies workflow creation via HTTP request
  - `test_running_workflow_creates_logs`: Verifies logs are created with proper fields, levels, and associations
  - `test_workflow_run_status_is_succeeded_on_success`: Explicitly verifies status transitions and completion timestamps
  - All tests use `RefreshDatabase` trait for clean test environment

- **Manual test script (brief)**:
  1. Create workflow via UI
  2. Add delay and http_check steps
  3. Reorder steps using up/down buttons
  4. Run workflow and verify execution
  5. Check run status (succeeded/failed)
  6. View logs in run details page
  7. View all logs in logs listing page
  8. Verify database entries directly

- **Any linters/formatters used**:
  - Laravel Pint (included in dev dependencies)
  - PHPUnit for testing
  - Built-in Laravel validation
  - No linting errors detected during development

## Time Breakdown (estimate)

- **Setup/scaffolding**:
  - Project structure analysis
  - Migration creation
  - Model setup with relationships

**Total estimated time**: ~1 hours
