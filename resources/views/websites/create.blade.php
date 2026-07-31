@extends('layouts.app')

@section('title', 'Upload Website')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('websites.index') }}" class="p-2 rounded-lg bg-hostinger-card border border-hostinger-border text-gray-400 hover:text-white transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Upload New Website</h2>
            <p class="text-sm text-gray-400 mt-0.5">Deploy a static HTML/CSS/JS project using a ZIP archive</p>
        </div>
    </div>

    <!-- Validation Error Alert -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400">
            <div class="flex items-start space-x-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 mt-0.5 shrink-0"></i>
                <div>
                    <p class="font-bold text-sm mb-1">Please fix the following errors:</p>
                    <ul class="text-xs list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400">
            <div class="flex items-start justify-between space-x-3">
                <div class="flex items-start space-x-3">
                    <i data-lucide="x-circle" class="w-5 h-5 mt-0.5 shrink-0"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="shrink-0 text-rose-400 hover:text-rose-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Upload Card -->
    <div x-data="{
            isDragging: false,
            fileName: null,
            fileSize: null,
            isUploading: false,
            handleFile(file) {
                if (file && file.name.endsWith('.zip')) {
                    this.fileName = file.name;
                    this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
                    document.getElementById('zip_file').files = this.createFileList(file);
                }
            },
            createFileList(file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                return dt.files;
            }
        }"
        class="bg-hostinger-card border border-hostinger-border rounded-2xl p-8 shadow-2xl glow-effect">

        <form method="POST" action="{{ route('websites.store') }}" enctype="multipart/form-data"
              @submit="isUploading = true" class="space-y-7">
            @csrf

            <!-- Website Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">
                    Website Project Name
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="tag" class="w-5 h-5"></i>
                    </div>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           placeholder="e.g., My Portfolio, Landing Page v2"
                           class="w-full pl-11 pr-4 py-3 bg-hostinger-dark border border-hostinger-border rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm transition">
                </div>
            </div>

            <!-- ZIP File Dropzone -->
            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">
                    Website ZIP Archive
                </label>

                <div
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="isDragging = false; handleFile($event.dataTransfer.files[0])"
                    @click="$refs.fileInput.click()"
                    :class="isDragging ? 'border-indigo-500 bg-indigo-500/5' : 'border-hostinger-border hover:border-indigo-500/50'"
                    class="relative border-2 border-dashed rounded-2xl p-10 text-center cursor-pointer transition-all duration-200"
                >
                    <input
                        type="file"
                        name="zip_file"
                        id="zip_file"
                        accept=".zip"
                        class="hidden"
                        x-ref="fileInput"
                        @change="handleFile($event.target.files[0])"
                    >

                    <!-- Default State -->
                    <div x-show="!fileName" class="space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center mx-auto">
                            <i data-lucide="upload-cloud" class="w-8 h-8 text-indigo-400"></i>
                        </div>
                        <div>
                            <p class="text-white font-bold text-sm">Drop your website.zip here</p>
                            <p class="text-gray-400 text-xs mt-1">or <span class="text-indigo-400 font-semibold">click to browse</span></p>
                        </div>
                        <p class="text-[11px] text-gray-500 pt-2">Only .zip files accepted • Max 100 MB per archive</p>
                    </div>

                    <!-- File Selected State -->
                    <div x-show="fileName" class="space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto">
                            <i data-lucide="file-archive" class="w-8 h-8 text-emerald-400"></i>
                        </div>
                        <div>
                            <p class="text-emerald-400 font-bold text-sm" x-text="fileName"></p>
                            <p class="text-gray-400 text-xs mt-0.5" x-text="fileSize"></p>
                        </div>
                        <p class="text-[11px] text-indigo-400">✓ ZIP selected — click to change file</p>
                    </div>
                </div>
            </div>

            <!-- Requirements Checklist -->
            <div class="p-4 rounded-xl bg-blue-500/5 border border-blue-500/20 text-xs text-gray-400 space-y-2">
                <p class="font-bold text-blue-400 text-sm flex items-center space-x-2">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>ZIP Requirements & Security Checks</span>
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 mt-2">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                        <span>Must contain <code class="text-indigo-300">index.html</code> at root</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                        <span>HTML, CSS, JS, images allowed</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="x" class="w-3.5 h-3.5 text-rose-400 shrink-0"></i>
                        <span>No PHP, .exe, .sh, .py files</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="x" class="w-3.5 h-3.5 text-rose-400 shrink-0"></i>
                        <span>No server-side scripts</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                        <span>Maximum 2,000 files per archive</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                        <span>Max extracted size: 150 MB</span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="upload-btn"
                    :disabled="isUploading"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2">
                <template x-if="!isUploading">
                    <span class="flex items-center space-x-2">
                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                        <span>Validate &amp; Upload Website</span>
                    </span>
                </template>
                <template x-if="isUploading">
                    <span class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span>Uploading & Validating...</span>
                    </span>
                </template>
            </button>
        </form>
    </div>
</div>
@endsection
