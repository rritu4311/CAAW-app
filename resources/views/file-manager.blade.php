<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('File Manager') }}
    </h2>
</x-slot>

<!-- Asset Preview Modal -->
<x-asset-preview />

<!-- Cropper.js Library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">File Manager</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Upload and organize your files</p>
                    </div>
                    
                    <!-- Create Folder Button -->
                    <div class="flex items-center space-x-4">
                        <form action="{{ route('folder-manager') }}" method="GET" class="inline">
                            <input type="hidden" name="toggle_folder" value="1">
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                New Folder
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Breadcrumb Navigation -->
                @if(count($breadcrumb) > 0)
                    <nav class="flex items-center space-x-2 text-sm mb-6">
                        <a href="{{ route('folder-manager') }}" class="text-blue-600 hover:text-blue-800">
                            Home
                        </a>
                        @foreach($breadcrumb as $index => $crumb)
                            <span class="text-gray-400">/</span>
                            @if($index < count($breadcrumb) - 1)
                                <a href="{{ route('folder-manager', ['folder' => $crumb['path']]) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $crumb['name'] }}
                                </a>
                            @else
                                <span class="text-gray-700 font-medium">{{ $crumb['name'] }}</span>
                            @endif
                        @endforeach
                    </nav>
                @endif

                <!-- Create Folder Form -->
                @if(request('toggle_folder'))
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <form action="{{ route('folders.store') }}" method="POST">
                            @csrf
                            <div class="flex items-center space-x-4">
                                <input type="text" name="name" placeholder="Folder name" required
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                                <input type="hidden" name="parent_folder" value="{{ $currentFolder }}">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Create
                                </button>
                                <a href="{{ route('folder-manager', ['folder' => $currentFolder]) }}" 
                                   class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Success Message -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Upload Area -->
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 sm:p-6 lg:p-8 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-all duration-300 mb-6 relative overflow-hidden" id="dropZone">
                    <!-- Upload Progress -->
                    <div id="uploadProgress" class="hidden absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-95 flex items-center justify-center z-10">
                        <div class="text-center">
                            <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Uploading files...</p>
                            <div class="w-full max-w-xs bg-gray-200 rounded-full h-2">
                                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="uploadContent">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4 transition-transform duration-300 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Upload files to this folder</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Select files and upload them instantly</p>
                    </div>
                    
                    <form action="{{ route('folders.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <input type="hidden" name="folder" value="{{ $currentFolder }}">
                        
                        <div class="flex items-center justify-center space-x-4">
                            <input type="file" name="files[]" multiple id="fileInput"
                                   class="hidden"
                                   accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.webm,.pdf,.docx,.xlsx,.txt,.md">
                            
                            <button type="button" onclick="document.getElementById('fileInput').click()"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 cursor-pointer hover:shadow-lg transform hover:-translate-y-0.5 text-sm sm:text-base flex items-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Choose Files
                            </button>
                        </div>
                        
                        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Supported file types:</p>
                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Images (PNG, JPG, GIF, WebP)
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                    </svg>
                                    Videos (MP4, MOV, WebM)
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Documents (PDF, DOCX, XLSX)
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Text (TXT, MARKDOWN)
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Max file size: 50MB (images/docs), 500MB (videos)</p>
                        </div>
                    </form>
                </div>

                <!-- File List -->
                <div class="space-y-2">
                    <!-- Folders -->
                    @foreach($folders as $folder)
                        <a href="{{ route('folder-manager', ['folder' => $folder['path']]) }}" 
                           class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors border border-blue-200 dark:border-blue-700">
                            <div class="flex-shrink-0 w-10 h-10 mr-4">
                                <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $folder['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Folder</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @endforeach

                    <!-- Files -->
                    @foreach($files as $file)
                        <div class="flex items-center p-3 bg-white dark:bg-gray-800 rounded-lg hover:shadow-md transition-shadow border border-gray-200 dark:border-gray-700">
                            <!-- Icon/Thumbnail -->
                            <div class="flex-shrink-0 w-10 h-10 mr-4">
                                @if($file['is_image'])
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100">
                                        <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" class="w-full h-full object-cover">
                                    </div>
                                @elseif($file['is_video'])
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @elseif(str_contains($file['name'], '.pdf'))
                                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @elseif(str_contains($file['name'], '.docx'))
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- File Info -->
                            <div class="flex-1 min-w-0 mr-4">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $file['formatted_size'] }}</p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button onclick="openAssetPreview({{ $file['id'] }})" 
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors">View</button>
                                <a href="{{ $file['url'] }}" download 
                                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium rounded transition-colors">Download</a>
                                
                                <form action="{{ route('folders.file.delete') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this file?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="path" value="{{ $file['file_path'] }}">
                                    <button type="submit" 
                                            class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Empty State -->
                @if(count($files) === 0 && count($folders) === 0)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">No files in this folder</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Upload some files to get started</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
</x-app-layout>

<script>
// Canvas Image Preview System
class CanvasImagePreview {
    constructor() {
        this.canvases = [];
        this.init();
    }

    init() {
        // Initialize all canvas elements
        document.querySelectorAll('.image-preview-canvas').forEach(canvas => {
            this.loadImage(canvas);
        });
    }

    loadImage(canvas) {
        const ctx = canvas.getContext('2d');
        const imageSrc = canvas.dataset.imageSrc;
        const fallbackSrc = canvas.dataset.fallbackSrc;
        const fileName = canvas.dataset.fileName;

        // Set canvas size
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * window.devicePixelRatio;
        canvas.height = rect.height * window.devicePixelRatio;
        ctx.scale(window.devicePixelRatio, window.devicePixelRatio);

        // Show loading state
        this.drawLoadingState(ctx, rect.width, rect.height);

        // Load image
        const img = new Image();
        img.crossOrigin = 'anonymous';
        
        img.onload = () => {
            this.drawImageWithEffects(ctx, img, rect.width, rect.height);
            this.addHoverEffect(canvas, ctx, img, rect.width, rect.height);
        };

        img.onerror = () => {
            // Try fallback image
            const fallbackImg = new Image();
            fallbackImg.crossOrigin = 'anonymous';
            
            fallbackImg.onload = () => {
                this.drawImageWithEffects(ctx, fallbackImg, rect.width, rect.height);
                this.addHoverEffect(canvas, ctx, fallbackImg, rect.width, rect.height);
            };
            
            fallbackImg.onerror = () => {
                this.drawErrorState(ctx, rect.width, rect.height, fileName);
            };
            
            fallbackImg.src = fallbackSrc;
        };

        img.src = imageSrc;
    }

    drawLoadingState(ctx, width, height) {
        ctx.fillStyle = '#f3f4f6';
        ctx.fillRect(0, 0, width, height);
        
        // Draw loading spinner
        ctx.strokeStyle = '#9ca3af';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(width / 2, height / 2, 15, 0, Math.PI * 1.5);
        ctx.stroke();
        
        // Add subtle gradient
        const gradient = ctx.createLinearGradient(0, 0, width, height);
        gradient.addColorStop(0, 'rgba(255, 255, 255, 0.1)');
        gradient.addColorStop(1, 'rgba(0, 0, 0, 0.1)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, width, height);
    }

    drawErrorState(ctx, width, height, fileName) {
        ctx.fillStyle = '#fef2f2';
        ctx.fillRect(0, 0, width, height);
        
        // Draw error icon
        ctx.fillStyle = '#ef4444';
        ctx.font = '24px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('⚠', width / 2, height / 2 - 10);
        
        ctx.font = '10px Arial';
        ctx.fillStyle = '#991b1b';
        ctx.fillText('Failed to load', width / 2, height / 2 + 15);
        
        // Add error border
        ctx.strokeStyle = '#fca5a5';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, width, height);
    }

    drawImageWithEffects(ctx, img, width, height) {
        // Calculate aspect ratio
        const imgRatio = img.width / img.height;
        const canvasRatio = width / height;
        
        let drawWidth, drawHeight, drawX, drawY;
        
        if (imgRatio > canvasRatio) {
            drawWidth = width;
            drawHeight = width / imgRatio;
            drawX = 0;
            drawY = (height - drawHeight) / 2;
        } else {
            drawHeight = height;
            drawWidth = height * imgRatio;
            drawX = (width - drawWidth) / 2;
            drawY = 0;
        }

        // Clear canvas
        ctx.clearRect(0, 0, width, height);
        
        // Add subtle background
        ctx.fillStyle = '#f9fafb';
        ctx.fillRect(0, 0, width, height);
        
        // Apply shadow for depth
        ctx.shadowColor = 'rgba(0, 0, 0, 0.1)';
        ctx.shadowBlur = 10;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 4;
        
        // Draw image
        ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
        
        // Reset shadow
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
        
        // Add subtle vignette effect
        const gradient = ctx.createRadialGradient(
            width / 2, height / 2, 0,
            width / 2, height / 2, Math.max(width, height) / 2
        );
        gradient.addColorStop(0, 'rgba(0, 0, 0, 0)');
        gradient.addColorStop(1, 'rgba(0, 0, 0, 0.05)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, width, height);
        
        // Add subtle border
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, width, height);
    }

    addHoverEffect(canvas, ctx, img, width, height) {
        let isHovering = false;
        let animationFrame = null;

        canvas.addEventListener('mouseenter', () => {
            isHovering = true;
            this.animateHover(canvas, ctx, img, width, height, true);
        });

        canvas.addEventListener('mouseleave', () => {
            isHovering = false;
            this.animateHover(canvas, ctx, img, width, height, false);
        });
    }

    animateHover(canvas, ctx, img, width, height, isEntering) {
        const duration = 300;
        const startTime = Date.now();
        const startScale = isEntering ? 1 : 1.05;
        const endScale = isEntering ? 1.05 : 1;

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeProgress = this.easeInOutCubic(progress);
            
            const currentScale = startScale + (endScale - startScale) * easeProgress;
            
            // Clear and redraw with scale
            ctx.clearRect(0, 0, width, height);
            ctx.save();
            
            const centerX = width / 2;
            const centerY = height / 2;
            
            ctx.translate(centerX, centerY);
            ctx.scale(currentScale, currentScale);
            ctx.translate(-centerX, -centerY);
            
            this.drawImageWithEffects(ctx, img, width, height);
            
            ctx.restore();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
        
        animationFrame = requestAnimationFrame(animate);
    }

    easeInOutCubic(t) {
        return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
    }
}

// Drag and Drop functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Canvas Image Preview
    new CanvasImagePreview();
    
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadContent = document.getElementById('uploadContent');
    const progressBar = document.getElementById('progressBar');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight() {
        dropZone.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        dropZone.classList.remove('border-gray-300', 'dark:border-gray-600');
    }

    function unhighlight() {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        dropZone.classList.add('border-gray-300', 'dark:border-gray-600');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        fileInput.files = files;
        uploadWithProgress();
    }

    function uploadWithProgress() {
        // Show progress indicator
        uploadProgress.classList.remove('hidden');
        uploadContent.classList.add('opacity-50');

        // Simulate progress (in real app, this would be based on actual upload progress)
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);

        // Submit form
        const formData = new FormData(uploadForm);
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
            }
        });

        xhr.addEventListener('load', function() {
            clearInterval(interval);
            progressBar.style.width = '100%';
            
            // Redirect to refresh the page with new files
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });

        xhr.addEventListener('error', function() {
            clearInterval(interval);
            uploadProgress.classList.add('hidden');
            uploadContent.classList.remove('opacity-50');
            alert('Upload failed. Please try again.');
        });

        xhr.open('POST', uploadForm.action);
        xhr.send(formData);
    }

    // File input change handler
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            uploadWithProgress();
        }
    });

    // Click to upload
    dropZone.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON') {
            fileInput.click();
        }
    });
});
</script>
