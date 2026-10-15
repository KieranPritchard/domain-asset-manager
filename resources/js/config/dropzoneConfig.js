// Disables dropzones auto discovery to prevent duplication
Dropzone.autoDiscover = false;

// Defines the file dropzone config
const fileDropzone = new Dropzone("#filesDropzone", {
    paramName: "files",
    uploadMultiple: true,
    parallelUploads: 10,
    maxFilesize: 32,
    maxFiles: 5,
    autoProcessQueue: false,
    // Custom Tailwind template for added file cards
    previewTemplate: `
        <div class="dz-preview dz-file-preview w-full mt-3 p-3 bg-white border border-slate-200 rounded-lg flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center space-x-3 overflow-hidden">
                <div class="w-10 h-10 rounded bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex flex-col truncate">
                    <span class="dz-filename text-xs font-medium text-slate-700 truncate" data-dz-name></span>
                    <span class="dz-size text-[11px] text-slate-400" data-dz-size></span>
                    <span class="dz-error-message text-[11px] text-red-500" data-dz-errormessage></span>
                </div>
            </div>
            <button type="button" data-dz-remove class="p-1 text-slate-400 hover:text-red-500 transition-colors focus:outline-none cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `,
    
    // Dragover state handlers
    init: function() {
        this.on("dragenter", () => {
            this.element.classList.add("border-indigo-500", "bg-indigo-50/50");
        });
        this.on("dragleave", () => {
            this.element.classList.remove("border-indigo-500", "bg-indigo-50/50");
        });
        this.on("drop", () => {
            this.element.classList.remove("border-indigo-500", "bg-indigo-50/50");
        });
        this.on("successmultiple", function(files, response) {
            // Process upload results here after drop/submit completes
            window.location.replace("overview.php");
        });
        this.on("errormultiple", function(files, response) {
            const errorMessage = document.getElementById("errorMessage");
            errorMessage.innerHTML = "Upload failed.";
            openModal("errorModal");
        });

        const myDropzone = this;
        const submitButton = document.getElementById("submitBtn");

        submitButton.addEventListener("click", function () {
            if (myDropzone.getQueuedFiles().length > 0) {
                myDropzone.processQueue(); // Manual upload trigger
            } else {
                alert("Please select or drop at least one file first.");
            }
        });
    }
});