<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload | Virus Total Uploader</title>

    <!-- Laravel Vite Directive (compiles Tailwind & your JS bundle) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col justify-center items-center bg-slate-50 px-4 py-12">
    <!-- Card for the upload field -->
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-sm border border-slate-200/80">

        <!-- Form header and description -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800">Upload Files</h2>
            <p class="text-sm text-slate-500 mt-1">Select or drop your documents below</p>
        </div>

        <!-- Styled Dropzone Form -->
        <form action="/upload" method="POST" id="filesDropzone" class="border-2 border-dashed border-slate-300 hover:border-indigo-500 bg-slate-50/50 hover:bg-slate-50 rounded-lg p-6 flex flex-col items-center justify-center cursor-pointer transition-all duration-200 group">
            @csrf
            
            <div class="dz-message flex flex-col items-center justify-center text-center my-4" data-dz-message>
                <div class="w-12 h-12 mb-3 rounded-full bg-slate-100 group-hover:bg-indigo-50 flex items-center justify-center text-slate-500 group-hover:text-indigo-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700 group-hover:text-indigo-600 transition-colors">
                    Click to upload <span class="font-normal text-slate-500">or drag and drop</span>
                </p>
                <p class="text-xs text-slate-400 mt-1">All files up to 32MB</p>
            </div>
        </form>

        <!-- Submit Button -->
        <div class="mt-6">
            <button
                type="button"
                id="submitBtn"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors cursor-pointer"
            >
                Submit Files
            </button>
        </div>
    </div>

    <!-- Renders the error modal using Blade components -->
    <x-modal id="errorModal" title="Error Details">
        <p class="my-2">
            <span id="errorMessage"></span>
        </p>

        <div class="flex justify-between gap-2 pt-3 border-t border-slate-100">
            <button
                id="errorModalBtn"
                type="button"
                onclick="closeModal('errorModal')"
                class="w-full p-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg transition-colors cursor-pointer"
            >
                Close
            </button>
        </div>
    </x-modal>
</body>
</html>