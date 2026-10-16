@props(['id', 'title'])

<!-- Modal container -->
<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-backdrop"
    onclick="closeModal('{{ $id }}')"
>
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <!-- Modal panel -->
    <div 
        class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl border border-slate-200/80"
        onclick="closeModal('{{ $id }}')"
    >
        <!-- Modal header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">
                {{ $title }}
            </h2>
            <button 
                id="closeModalBtn"
                type="button"
                onclick="closeModal('{{ $id }}')" 
                class="text-gray-400 hover:text-gray-600 cursor-pointer"
            >
                <!-- Lucide X Icon SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <div class="text-gray-600">
            {{ $slot }} <!-- Renders whatever HTML you pass inside the component -->
        </div>
    </div>
</div>