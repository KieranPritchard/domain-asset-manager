@props(['data'])

<!-- Card to contain the table -->
<div class="w-full rounded-lg bg-white p-6 border border-slate-200/80 shadow-sm flex flex-col">
    <p class="text-sm text-slate-500 mb-4">All files scanned</p>

    <!-- Table to be displayed -->
    <div class="flex-1 overflow-y-auto overflow-x-auto rounded-md border border-slate-200">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-indigo-500 text-white">
                    <th class="px-4 py-2 text-left font-medium">File Name</th>
                    <th class="px-4 py-2 text-left font-medium">File Hash</th>
                    <th class="px-4 py-2 text-left font-medium">Malicious</th>
                    <th class="px-4 py-2 text-left font-medium">Suspicious</th>
                    <th class="px-4 py-2 text-left font-medium">Harmless</th>
                    <th class="px-4 py-2 text-left font-medium">Undetected</th>
                    <th class="px-4 py-2 text-left font-medium">Date</th>
                </tr>
            </thead>

            <!-- Body which is rendered on the server side -->
            <tbody class="divide-y divide-slate-100">
                @forelse($data as $file)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 text-slate-700">{{ $file['original_filename'] ?? $file->original_filename }}</td>
                        <td class="px-4 py-2 font-mono text-xs text-slate-400">
                            {{ Str::limit($file['file_hash'] ?? $file->file_hash, 16, '…') }}
                        </td>
                        <td class="px-4 py-2 text-red-600 font-medium">{{ (int) ($file['malicious'] ?? $file->malicious) }}</td>
                        <td class="px-4 py-2 text-amber-600">{{ (int) ($file['suspicious'] ?? $file->suspicious) }}</td>
                        <td class="px-4 py-2 text-green-600">{{ (int) ($file['harmless'] ?? $file->harmless) }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ (int) ($file['undetected'] ?? $file->undetected) }}</td>
                        <td class="px-4 py-2 text-slate-400">{{ $file['scan_date'] ?? $file->scan_date }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-400">
                            No files scanned yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>