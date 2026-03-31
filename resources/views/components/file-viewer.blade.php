@props(['assets'])

<div x-data="{ 
    isOpen: false, 
    fileSrc: '', 
    fileType: '',
    videoDuration: null,
    videoWidth: null,
    videoHeight: null,
    imageScale: 1,
    isDragging: false,
    startX: 0,
    startY: 0,
    scrollLeft: 0,
    scrollTop: 0,
    openFile(src, type) {
        this.fileSrc = src;
        this.fileType = type;
        this.isOpen = true;
        this.videoDuration = null;
        this.videoWidth = null;
        this.videoHeight = null;
        this.imageScale = 1;
        this.scrollLeft = 0;
        this.scrollTop = 0;
        document.body.style.overflow = 'hidden';
    },
    closeFile() {
        this.isOpen = false;
        this.fileSrc = '';
        this.fileType = '';
        this.videoDuration = null;
        this.videoWidth = null;
        this.videoHeight = null;
        this.imageScale = 1;
        document.body.style.overflow = '';
    },
    zoomIn() {
        this.imageScale = Math.min(this.imageScale + 0.25, 4);
    },
    zoomOut() {
        this.imageScale = Math.max(this.imageScale - 0.25, 0.5);
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
    }
}" 
    x-show="isOpen" 
    x-cloak
    @keydown.escape.window="closeFile()"
    @open-file-viewer.window="openFile($event.detail.src, $event.detail.type)"
    class="fixed inset-0 z-50 bg-black"
    @click="closeFile()">

    <!-- Close Button -->
    <button @click="closeFile()" class="absolute top-4 right-4 z-50 p-2 bg-white/10 hover:bg-white/20 rounded-full transition-colors">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Content Container -->
    <div class="w-full h-full flex flex-col items-center justify-center p-8" @click.stop>
        {{-- Image Viewer with Zoom --}}
        <template x-if="fileType === 'image'">
            <div class="relative w-full h-full flex items-center justify-center">
                <!-- Scrollable Image Container -->
                <div class="w-full h-full overflow-auto cursor-grab active:cursor-grabbing flex items-center justify-center"
                     @wheel.prevent="onWheel($event)"
                     @mousedown.prevent="startDrag($event)"
                     @mousemove.prevent="onDrag($event)"
                     @mouseup.prevent="stopDrag()"
                     @mouseleave.prevent="stopDrag()">
                    <img :src="fileSrc" 
                         :style="`transform: scale(${imageScale}); transition: transform 0.1s ease-out; max-width: none; max-height: none;`"
                         class="object-contain select-none" 
                         alt="View"
                         draggable="false">
                </div>
            </div>
        </template>

        {{-- Video Viewer with Metadata --}}
        <template x-if="fileType === 'video'">
            <div class="flex flex-col items-center gap-2 max-w-full max-h-full">
                <video 
                    :src="fileSrc" 
                    controls 
                    class="max-w-[60vw] max-h-[60vh] object-contain"
                    @loadedmetadata="onVideoLoaded($event)">
                </video>
                {{-- Video Metadata --}}
                <div class="flex items-center gap-4 text-white text-sm bg-black/50 px-4 py-2 rounded-lg">
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="formatDuration(videoDuration)"></span>
                    </div>
                    <div class="w-px h-4 bg-white/30"></div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span x-text="videoWidth && videoHeight ? videoWidth + 'x' + videoHeight : '--x--'"></span>
                    </div>
                </div>
            </div>
        </template>

        {{-- PDF Viewer --}}
        <template x-if="fileType === 'pdf'">
            <iframe :src="fileSrc" class="w-full h-full"></iframe>
        </template>

        {{-- Document Viewer (DOC, DOCX, XLSX, XLS, CSV, TXT, MD) --}}
        <template x-if="['doc', 'docx', 'xlsx', 'xls', 'csv', 'txt', 'md', 'markdown'].includes(fileType)">
            <iframe :src="fileSrc" class="w-full h-full bg-white"></iframe>
        </template>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
