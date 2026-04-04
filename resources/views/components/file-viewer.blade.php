@props(['assets'])

<div x-data="{ 
    isOpen: false, 
    fileSrc: '', 
    fileType: '',
    fileName: '',
    fileSize: '',
    currentIndex: 0,
    videoDuration: null,
    videoWidth: null,
    videoHeight: null,
    imageScale: 1,
    isDragging: false,
    startX: 0,
    startY: 0,
    scrollLeft: 0,
    scrollTop: 0,
    showInfo: true,
    isLoading: true,
    pdfUrl: '',
    
    get filteredAssets() {
        return this.assets || [];
    },
    
    openFile(src, type, name = '', size = '') {
        this.fileSrc = src;
        this.fileType = type;
        this.fileName = name;
        this.fileSize = size;
        this.isOpen = true;
        this.isLoading = true;
        this.videoDuration = null;
        this.videoWidth = null;
        this.videoHeight = null;
        this.imageScale = 1;
        this.scrollLeft = 0;
        this.scrollTop = 0;
        this.showInfo = true;
        document.body.style.overflow = 'hidden';
        
        // Find current index
        if (this.assets) {
            this.currentIndex = this.assets.findIndex(a => a.file_path === src || a.url === src);
        }
        
        // Generate PDF viewer URL
        if (type === 'pdf') {
            this.pdfUrl = 'https://docs.google.com/gview?embedded=1&url=' + encodeURIComponent(src);
        }
        
        setTimeout(() => { this.isLoading = false; }, 300);
    },
    
    closeFile() {
        this.isOpen = false;
        this.fileSrc = '';
        this.fileType = '';
        this.fileName = '';
        this.fileSize = '';
        this.videoDuration = null;
        this.videoWidth = null;
        this.videoHeight = null;
        this.imageScale = 1;
        this.pdfUrl = '';
        document.body.style.overflow = '';
    },
    
    nextFile() {
        if (!this.assets || this.assets.length <= 1) return;
        this.currentIndex = (this.currentIndex + 1) % this.assets.length;
        const asset = this.assets[this.currentIndex];
        this.openFile(asset.file_path || asset.url, asset.file_type, asset.name, asset.formatted_size || '');
    },
    
    prevFile() {
        if (!this.assets || this.assets.length <= 1) return;
        this.currentIndex = (this.currentIndex - 1 + this.assets.length) % this.assets.length;
        const asset = this.assets[this.currentIndex];
        this.openFile(asset.file_path || asset.url, asset.file_type, asset.name, asset.formatted_size || '');
    },
    
    zoomIn() {
        this.imageScale = Math.min(this.imageScale + 0.25, 5);
    },
    
    zoomOut() {
        this.imageScale = Math.max(this.imageScale - 0.25, 0.25);
    },
    
    resetZoom() {
        this.imageScale = 1;
    },
    
    onWheel(event) {
        if (event.deltaY < 0) {
            this.zoomIn();
        } else {
            this.zoomOut();
        }
        event.preventDefault();
    },
    
    startDrag(event) {
        if (this.imageScale <= 1) return;
        this.isDragging = true;
        this.startX = event.clientX;
        this.startY = event.clientY;
        const container = event.currentTarget;
        this.scrollLeft = container.scrollLeft;
        this.scrollTop = container.scrollTop;
    },
    
    onDrag(event) {
        if (!this.isDragging) return;
        const dx = event.clientX - this.startX;
        const dy = event.clientY - this.startY;
        const container = event.currentTarget;
        container.scrollLeft = this.scrollLeft - dx;
        container.scrollTop = this.scrollTop - dy;
    },
    
    stopDrag() {
        this.isDragging = false;
    },
    
    formatDuration(seconds) {
        if (!seconds) return '--:--';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    },
    
    onVideoLoaded(event) {
        const video = event.target;
        this.videoDuration = video.duration;
        this.videoWidth = video.videoWidth;
        this.videoHeight = video.videoHeight;
        this.isLoading = false;
    },
    
    onImageLoad() {
        this.isLoading = false;
    },
    
    onIframeLoad() {
        this.isLoading = false;
    }
}" 
    x-show="isOpen" 
    x-cloak
    @keydown.escape.window="closeFile()"
    @keydown.arrow-right.window="if(isOpen) nextFile()"
    @keydown.arrow-left.window="if(isOpen) prevFile()"
    @open-file-viewer.window="openFile($event.detail.src, $event.detail.type, $event.detail.name, $event.detail.size)"
    class="fixed inset-0 z-50">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-950/95 backdrop-blur-sm" @click="closeFile()"></div>

    <!-- Top Bar -->
    <div class="absolute top-0 left-0 right-0 z-50 flex items-center justify-between px-4 py-3 bg-gray-900/80 backdrop-blur-md border-b border-gray-800">
        <!-- Left: File Info -->
        <div class="flex items-center gap-4 min-w-0 flex-1">
            <div class="flex items-center gap-3 min-w-0">
                <div class="p-2 bg-gray-800 rounded-lg flex-shrink-0">
                    <template x-if="fileType === 'image'">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </template>
                    <template x-if="fileType === 'video'">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </template>
                    <template x-if="fileType === 'pdf'">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </template>
                    <template x-if="['doc', 'docx'].includes(fileType)">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </template>
                    <template x-if="['xlsx', 'xls', 'csv'].includes(fileType)">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </template>
                    <template x-if="['txt', 'md', 'markdown'].includes(fileType)">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </template>
                </div>
                <div class="min-w-0">
                    <h3 class="text-white font-medium text-sm truncate" x-text="fileName || 'Untitled'"></h3>
                    <p class="text-gray-400 text-xs" x-text="fileSize ? fileSize + ' • ' + fileType.toUpperCase() : fileType.toUpperCase()"></p>
                </div>
            </div>
        </div>

        <!-- Center: Navigation -->
        <div class="flex items-center gap-2 px-4" x-show="assets && assets.length > 1">
            <button @click="prevFile()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <span class="text-gray-400 text-sm" x-text="(currentIndex + 1) + ' / ' + (assets ? assets.length : 0)"></span>
            <button @click="nextFile()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2">
            <!-- Info Toggle -->
            <button @click="showInfo = !showInfo" 
                    :class="showInfo ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800'"
                    class="p-2 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
            
            <!-- Download -->
            <a :href="fileSrc" download x-show="fileSrc" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            
            <!-- Close -->
            <button @click="closeFile()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="absolute inset-0 pt-16 pb-16 flex">
        <!-- Content -->
        <div class="flex-1 flex items-center justify-center p-4" @click.self="closeFile()">
            
            <!-- Loading Spinner -->
            <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center z-10">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-12 h-12 border-3 border-blue-500/30 border-t-blue-500 rounded-full animate-spin"></div>
                    <span class="text-gray-400 text-sm">Loading...</span>
                </div>
            </div>

            {{-- Image Viewer --}}
            <template x-if="fileType === 'image'">
                <div class="relative w-full h-full flex items-center justify-center">
                    <div class="w-full h-full overflow-auto flex items-center justify-center"
                         :class="imageScale > 1 ? 'cursor-grab active:cursor-grabbing' : ''"
                         @wheel.prevent="onWheel($event)"
                         @mousedown.prevent="startDrag($event)"
                         @mousemove.prevent="onDrag($event)"
                         @mouseup.prevent="stopDrag()"
                         @mouseleave.prevent="stopDrag()">
                        <img :src="fileSrc" 
                             @load="onImageLoad()"
                             :style="`transform: scale(${imageScale}); transition: transform 0.15s ease-out; max-width: ${imageScale > 1 ? 'none' : '100%'}; max-height: ${imageScale > 1 ? 'none' : '100%'};`"
                             class="object-contain select-none rounded-lg shadow-2xl" 
                             alt="Preview"
                             draggable="false">
                    </div>
                </div>
            </template>

            {{-- Video Viewer --}}
            <template x-if="fileType === 'video'">
                <div class="relative w-full h-full flex items-center justify-center p-4">
                    <div class="relative flex items-center justify-center" 
                         style="max-width: 100%; max-height: calc(75vh - 4rem);">
                        <video 
                            :src="fileSrc" 
                            controls 
                            controlsList="nodownload"
                            @loadedmetadata="onVideoLoaded($event)"
                            class="rounded-lg shadow-2xl bg-black"
                            style="max-width: 100%; max-height: calc(75vh - 4rem); width: auto; height: auto; object-fit: contain;">
                        </video>
                    </div>
                </div>
            </template>

            {{-- PDF Viewer --}}
            <template x-if="fileType === 'pdf'">
                <div class="relative w-full h-full max-w-6xl flex flex-col">
                    <!-- PDF Toolbar -->
                    <div class="flex items-center justify-between px-4 py-2 bg-gray-800 rounded-t-lg border-b border-gray-700">
                        <span class="text-gray-300 text-sm" x-text="fileName"></span>
                        <div class="flex items-center gap-2">
                            <a :href="fileSrc" download class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors" title="Download PDF">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                            <a :href="fileSrc" target="_blank" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors" title="Open in new tab">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- PDF Iframe -->
                    <div class="flex-1 bg-gray-900 rounded-b-lg overflow-hidden">
                        <iframe :src="fileSrc" 
                                @load="onIframeLoad()"
                                class="w-full h-full border-0"
                                type="application/pdf">
                        </iframe>
                    </div>
                </div>
            </template>

            {{-- Office Documents --}}
            <template x-if="['doc', 'docx', 'xlsx', 'xls', 'csv'].includes(fileType)">
                <div class="w-full h-full max-w-6xl bg-white rounded-lg shadow-2xl overflow-hidden flex items-center justify-center">
                    <div class="text-center p-8">
                        <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <template x-if="['doc', 'docx'].includes(fileType)">
                                <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </template>
                            <template x-if="['xlsx', 'xls', 'csv'].includes(fileType)">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </template>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2" x-text="fileName"></h3>
                        <p class="text-gray-500 mb-6">This file type cannot be previewed directly in the browser.</p>
                        <a :href="fileSrc" download class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download File
                        </a>
                    </div>
                </div>
            </template>

            {{-- Text Files --}}
            <template x-if="['txt', 'md', 'markdown'].includes(fileType)">
                <div class="w-full h-full max-w-4xl bg-white rounded-lg shadow-2xl overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between px-4 py-2 bg-gray-50 border-b">
                        <span class="text-sm text-gray-500" x-text="fileName"></span>
                        <span class="text-xs text-gray-400 uppercase" x-text="fileType"></span>
                    </div>
                    <iframe :src="fileSrc" @load="onIframeLoad()" class="flex-1 w-full border-0 bg-white"></iframe>
                </div>
            </template>

            {{-- Generic File --}}
            <template x-if="!['image', 'video', 'pdf', 'doc', 'docx', 'xlsx', 'xls', 'csv', 'txt', 'md', 'markdown'].includes(fileType)">
                <div class="bg-white rounded-2xl shadow-2xl p-12 text-center max-w-md">
                    <div class="w-24 h-24 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2" x-text="fileName || 'Unknown File'"></h3>
                    <p class="text-gray-500 mb-6">Preview not available for this file type.</p>
                    <a :href="fileSrc" download class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download File
                    </a>
                </div>
            </template>
        </div>

        <!-- Info Sidebar -->
        <div x-show="showInfo && fileType === 'video'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4"
             class="/w-72 bg-gray-900/90 backdrop-blur-md border-l border-gray-800 p-4 hidden lg:block">
            
            <div class="space-y-4">
                <div>
                    <br>
                    <br>
                    <h4 class="text-black font-medium mb-4">File Information</h4>
                </div>
                <div x-show="fileName">
                    <label class="text-gray-400 text-xs uppercase tracking-wider">Name</label>
                    <p class="text-black text-sm truncate" x-text="fileName"></p>
                </div>
                
                <div x-show="fileSize">
                    <label class="text-gray-400 text-xs uppercase tracking-wider">Size</label>
                    <p class="text-black text-sm" x-text="fileSize"></p>
                </div>
                
                <div x-show="fileType">
                    <label class="text-gray-400 text-xs uppercase tracking-wider">Type</label>
                    <p class="text-black text-sm uppercase" x-text="fileType"></p>
                </div>
                
                <template x-if="fileType === 'video' && videoDuration">
                    <div>
                        <label class="text-gray-400 text-xs uppercase tracking-wider">Duration</label>
                        <p class="text-black text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="formatDuration(videoDuration)"></span>
                        </p>
                    </div>
                </template>
                
                <template x-if="fileType === 'video' && videoWidth && videoHeight">
                    <div>
                        <label class="text-gray-400 text-xs uppercase tracking-wider">Resolution</label>
                        <p class="text-black text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="videoWidth + ' × ' + videoHeight"></span>
                        </p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Bottom Bar (Image Zoom Controls) -->
    <div x-show="fileType === 'image'" 
         class="absolute bottom-0 left-0 right-0 z-50 flex items-center justify-center gap-4 p-4 bg-gray-900/80 backdrop-blur-md border-t border-gray-800">
        <button @click="zoomOut()" 
                :disabled="imageScale <= 0.25"
                :class="imageScale <= 0.25 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-700'"
                class="p-2 text-gray-300 bg-gray-800 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
        </button>
        
        <span class="text-gray-300 text-sm font-medium min-w-[80px] text-center" x-text="Math.round(imageScale * 100) + '%'"></span>
        
        <button @click="zoomIn()" 
                :disabled="imageScale >= 5"
                :class="imageScale >= 5 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-700'"
                class="p-2 text-gray-300 bg-gray-800 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
        
        <div class="w-px h-6 bg-gray-700 mx-2"></div>
        
        <button @click="resetZoom()" 
                class="px-3 py-2 text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors text-sm">
            Reset
        </button>
        
        <div class="hidden sm:flex items-center gap-2 ml-4 text-gray-400 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Scroll to zoom • Drag to pan</span>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    
    /* Custom scrollbar for image container */
    .overflow-auto::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .overflow-auto::-webkit-scrollbar-track {
        background: transparent;
    }
    .overflow-auto::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.5);
        border-radius: 4px;
    }
    .overflow-auto::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.7);
    }
</style>
