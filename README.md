# Malware Uploader Dashboard

A Laravel-based web application for batch uploading suspicious files, automatically hashing them with SHA-256, submitting them to the VirusTotal API v3, and visualizing analysis results with interactive charts and metrics.

---

## Architecture Overview

- **Backend**: Laravel 12 on PHP 8.4/8.5.
- **Database**: PostgreSQL (production / Docker) & SQLite (testing / local fallback).
- **Frontend**: Blade components, Tailwind CSS v4, Dropzone.js, Chart.js.
- **Containerization**: Multi-stage Docker build with PostgreSQL 16 Alpine, automated test runner stage during image build, persistent uploads volume, and background scheduler service.
- **VirusTotal Integration**: Asynchronous file submission (supporting files up to 32MB standard and larger files via upload URLs), rate-limited to 4 requests/minute, with automatic analysis status polling.

---

## Features

1. **Drag-and-Drop Batch Uploads**: Dropzone.js integration supporting multiple files, live upload progress, and CSRF protection.
2. **Deterministic SHA-256 Hashing**: Automatic client and server-side identification of files.
3. **Session-Scoped Scans**: Users can upload batches of files and view their session's progress in real-time.
4. **Live Analysis Polling**: Automatic polling and status checking until all files in the batch have been scanned.
5. **Interactive Dashboard**:
   - Summary metric cards (Total files, Malicious, Suspicious, Harmless, Undetected).
   - Detailed session scan table showing file hashes and status breakdowns.
   - Points-per-category Doughnut Chart.
   - Stacked Bar Breakdown Chart comparing malicious, suspicious, harmless, and undetected counts per file.
6. **Background Scheduling**: Automated queue submission via `php artisan schedule:work` in Docker.

---

## Issues Identified & Fixed

During the codebase audit and Docker container verification, the following issues were discovered and resolved:

### 1. Session Key Mismatch (Infinite Redirect Bug)
- **Problem**: `UploadController` stored the session identifier under `session(['id' => $session->id])`, whereas `ScanController::dashboard` checked for `$request->session()->get('recorded_session_id')`. Because it was null, visiting `/dashboard` immediately redirected back to `/upload`, preventing access to the dashboard.
- **Fix**: Standardized session key to `'recorded_session_id'` across `UploadController` and `ScanController` methods (`dashboard`, `json`, `status`, and `index`).

### 2. File Storage Path & Volume Mounting Discrepancy
- **Problem**: In Laravel 11/12, the default `local` disk root is `storage/app/private`. `UploadController` computed `$absolutePath` via `storage_path('app/' . $storedPath)`, pointing to `storage/app/uploads/...` rather than `storage/app/private/uploads/...`. In Docker, `VirusTotalService` threw `Failed to open stream: No such file or directory` when attempting `fopen()`.
- **Fix**: Replaced manual path concatenation with `Storage::disk('local')->path($storedPath)`, guaranteeing correct path resolution in both local and containerized environments.

### 3. VirusTotal API Key Configuration
- **Problem**: `config/services.php` only checked `is_file($f = env('API_KEY'))`. When `API_KEY` was supplied as a raw API key string in `.env` rather than a secret file path, `is_file()` returned `false` and evaluated to `null`, breaking API requests.
- **Fix**: Updated configuration logic to support both Docker secret file paths (`/run/secrets/...`) and direct environment variable strings.

### 4. VirusTotalService Robustness & Resource Management
- **Problem**: Unhandled exceptions during file uploads could cause uncaught `ConnectionException` errors; open file handles (`fopen`) were not explicitly closed; and missing file paths were not guarded against.
- **Fix**: Added explicit file existence checks, `try/catch/finally` blocks with `fclose($handle)`, caught `Throwable` to report and set status to `error`, and handled completed, failed, and cancelled scan states gracefully.

### 5. View Resolution Error
- **Problem**: `ScanController::dashboard` called `return view('scans.dashboard', ...)`, but the Blade template was located at `resources/views/dashboard.blade.php`, throwing `View [scans.dashboard] not found`.
- **Fix**: Updated controller to `return view('dashboard', ...)`.

### 6. Blade Template Syntax Errors & 404 Assets
- **Problem**:
  - `resources/views/dashboard.blade.php` contained `:undetected="totalUndetected"` missing the `$` prefix.
  - Script tags `<script src="/js/dashboard/charts/categoryChart.js"></script>` pointed to non-existent public files (404 errors), as these assets are already bundled via Vite.
- **Fix**: Fixed the variable expression to `:undetected="$totalUndetected"` and removed the redundant 404 script tags.

### 7. Frontend JavaScript Runtime Crashes
- **Problem**:
  - `dropzoneConfig.js` immediately instantiated `new Dropzone("#filesDropzone")` and read `document.querySelector('meta[name="csrf-token"]').content`. On the dashboard page, this threw a `TypeError` and stopped all subsequent JavaScript (including Chart.js scripts) from executing.
  - `modal.js` was never imported into `resources/js/app.js`, and its functions were not bound to `window`, breaking modal open/close actions.
  - `uploadErrors.js` made an automatic GET `fetch('/upload')` on load and failed parsing the HTML as JSON, erroneously triggering error modals on every page load.
  - `modal.blade.php` had a click handler on the dialog panel that closed the modal on any click inside the box.
- **Fix**:
  - Guarded Dropzone initialization to only run when `#filesDropzone` exists in the DOM.
  - Guarded Chart.js initialization to only run when `#categoryChart` and `#scanStacked` canvases exist.
  - Exported and registered `openModal` and `closeModal` globally on `window`.
  - Added `event.stopPropagation()` to the modal panel.
  - Removed rogue auto-fetching in `uploadErrors.js`.

### 8. Scheduler Background Task
- **Problem**: `docker-compose.yml` ran `scheduler` via `php artisan schedule:work`, but `routes/console.php` had no scheduled tasks configured.
- **Fix**: Added scheduled task in `routes/console.php` to run `VirusTotalService::submitQueued()` every minute.

### 9. Test Suite & Docker Build Integration
- **Problem**: The Dockerfile includes a test stage (`RUN php artisan test`) that verifies correctness before building the final production image. The repository lacked unit and feature tests covering uploads, scan flows, and status polling.
- **Fix**: Added comprehensive PHPUnit feature tests (`UploadTest.php` and `ScanTest.php`) verifying all endpoints, session tracking, database persistence, and mocked VirusTotal API communication.

---

## Running with Docker

### Prerequisites
- Docker and Docker Compose installed.
- Ensure `./secrets/db/password.txt` and `./secrets/virus-total-key.txt` exist (or use your `.env` values).

### Build and Start Containers
```bash
docker compose build
docker compose up -d
```

### Inspect Container Status
```bash
docker compose ps
docker compose logs -f app
```

The application will be accessible at: **`http://localhost:8000`**.

---

## Running Locally

1. Install PHP dependencies:
   ```bash
   composer install
   ```

2. Install JavaScript dependencies and compile assets:
   ```bash
   npm install
   npm run build
   ```

3. Run migrations:
   ```bash
   php artisan migrate
   ```

4. Run tests:
   ```bash
   php artisan test
   ```

5. Format code:
   ```bash
   vendor/bin/pint --dirty --format agent
   ```
