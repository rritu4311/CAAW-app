<div id="assetPreviewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" onclick="closeAssetPreview()"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-7xl w-full max-h-[95vh] overflow-hidden"
             onclick="event.stopPropagation()">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div id="previewIcon" class="w-10 h-10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path id="previewIconPath"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 id="previewTitle" class="text-lg font-semibold text-gray-900 dark:text-white"></h3>
                        <p id="previewMeta" class="text-sm text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>
                <button onclick="closeAssetPreview()" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Content -->
            <div id="previewContent" class="p-4 overflow-auto" style="max-height: calc(95vh - 120px);">
                <!-- Content will be dynamically loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
let currentAsset = null;
let cropper = null;

function openAssetPreview(assetId) {
    console.log('Opening asset preview for ID:', assetId);
    const modal = document.getElementById('assetPreviewModal');
    if (!modal) {
        console.error('Modal not found!');
        return;
    }
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    loadAsset(assetId);
}

function closeAssetPreview() {
    const modal = document.getElementById('assetPreviewModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    currentAsset = null;
    
    // Cleanup canvas preview
    if (canvasImagePreview) {
        canvasImagePreview.destroy();
        canvasImagePreview = null;
    }
    
    // Destroy cropper if it exists
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

async function loadAsset(assetId) {
    console.log('Loading asset data for ID:', assetId);
    try {
        const response = await fetch(`/assets/${assetId}/metadata`);
        console.log('Fetch response:', response);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const asset = await response.json();
        console.log('Asset data received:', asset);
        
        currentAsset = {
            ...asset,
            url: `/assets/${assetId}/preview`
        };
        
        console.log('Current asset set to:', currentAsset);
        updatePreviewHeader();
        updatePreviewContent();
    } catch (error) {
        console.error('Error loading asset:', error);
        console.error('Error details:', error.message);
        closeAssetPreview();
    }
}

function updatePreviewHeader() {
    const title = document.getElementById('previewTitle');
    const meta = document.getElementById('previewMeta');
    const icon = document.getElementById('previewIcon');
    const iconPath = document.getElementById('previewIconPath');
    
    title.textContent = currentAsset.name;
    
    let metaText = currentAsset.size;
    if (currentAsset.dimensions) metaText += ` • ${currentAsset.dimensions}`;
    if (currentAsset.duration) metaText += ` • ${currentAsset.duration}`;
    meta.textContent = metaText;
    
    const iconConfig = getIconConfig(currentAsset.type);
    icon.className = `w-10 h-10 rounded-lg flex items-center justify-center ${iconConfig.bg}`;
    iconPath.setAttribute('d', iconConfig.path);
    iconPath.parentElement.className = `w-6 h-6 ${iconConfig.color}`;
}

function updatePreviewContent() {
    console.log('Updating preview content for asset type:', currentAsset?.type);
    const content = document.getElementById('previewContent');
    
    if (!content) {
        console.error('Preview content element not found!');
        return;
    }
    
    // Cleanup previous canvas preview
    if (canvasImagePreview) {
        console.log('Cleaning up previous canvas preview');
        canvasImagePreview.destroy();
        canvasImagePreview = null;
    }
    
    if (!currentAsset) {
        console.error('No current asset to display!');
        return;
    }
    
    switch (currentAsset.type) {
        case 'image':
            console.log('Setting up image preview for:', currentAsset);
            content.innerHTML = getImagePreview();
            // Don't auto-initialize canvas - let user test it manually
            console.log('Image preview HTML set up. Canvas will be initialized when user clicks test button.');
            break;
        case 'video':
            console.log('Setting up video preview');
            content.innerHTML = getVideoPreview();
            break;
        case 'document':
            console.log('Setting up document preview');
            content.innerHTML = currentAsset.mime_type === 'application/pdf' ? getPdfPreview() : getDocumentPreview();
            break;
        case 'text':
            console.log('Setting up text preview');
            content.innerHTML = getTextPreview();
            break;
        default:
            console.log('Setting up generic preview');
            content.innerHTML = getGenericPreview();
    }
}

function getImagePreview() {
    return `
        <div class="space-y-4">
            <!-- Debug Info -->
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
                <p class="font-semibold">Debug Info:</p>
                <p>Asset URL: ${currentAsset.url}</p>
                <p>Asset Type: ${currentAsset.type}</p>
                <p>Asset Name: ${currentAsset.name}</p>
            </div>
            
            <!-- Social Media Style Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Image Preview</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">${new Date().toLocaleTimeString()}</p>
                        </div>
                    </div>
                    <button onclick="closeAssetPreview()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Canvas Container -->
                <div class="relative bg-gray-50 dark:bg-gray-900" style="height: 700px;">
                    <!-- Fallback Image for Testing -->
                    <img id="fallbackImage" src="${currentAsset.url}" alt="${currentAsset.name}" 
                         class="w-full h-full object-contain" 
                         style="display: block;"
                         onload="console.log('Fallback image loaded successfully')"
                         onerror="console.error('Fallback image failed to load'); this.style.display='none'; document.getElementById('canvasContainer').style.display='block';">
                    
                    <!-- Canvas Container (Hidden initially) -->
                    <div id="canvasContainer" style="display: none; position: absolute; inset: 0;">
                        <canvas id="previewCanvas" class="w-full h-full cursor-move"></canvas>
                        
                        <!-- Loading State -->
                        <div id="canvasLoading" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                            <div class="text-center">
                                <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-3"></div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Loading image...</p>
                            </div>
                        </div>
                        
                        <!-- Controls Overlay -->
                        <div class="absolute top-4 right-4 flex flex-col space-y-2">
                            <!-- Test Canvas Button -->
                            <button onclick="testCanvas()" class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-lg shadow-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Test Canvas">
                                <svg class="w-4 h-4 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </button>
                            
                            <!-- Zoom Controls -->
                            <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-lg shadow-lg p-1 flex flex-col space-y-1">
                                <button onclick="zoomIn()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors" title="Zoom In">
                                    <svg class="w-4 h-4 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                                    </svg>
                                </button>
                                <button onclick="zoomOut()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors" title="Zoom Out">
                                    <svg class="w-4 h-4 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                                    </svg>
                                </button>
                                <button onclick="resetZoom()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors" title="Reset Zoom">
                                    <svg class="w-4 h-4 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Zoom Indicator -->
                        <div class="absolute bottom-4 left-4 bg-black/70 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-sm font-medium">
                            <span id="zoomLevel">100%</span>
                        </div>
                        
                        <!-- Image Info Overlay -->
                        <div class="absolute bottom-4 right-4 bg-black/70 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-xs font-medium">
                            <span id="imageDimensions">${currentAsset.dimensions || 'Loading...'}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Content Section -->
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">${currentAsset.name}</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        ${currentAsset.size} • ${currentAsset.mime_type} • ${currentAsset.dimensions || 'Unknown dimensions'}
                    </p>
                    
                    <!-- Engagement Stats -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center space-x-6">
                            <button class="flex items-center space-x-2 text-gray-500 hover:text-red-500 transition-colors group">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span class="text-sm font-medium">Like</span>
                            </button>
                            <button class="flex items-center space-x-2 text-gray-500 hover:text-blue-500 transition-colors group">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span class="text-sm font-medium">Comment</span>
                            </button>
                            <button class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition-colors group">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m9.032 4.026a9.001 9.001 0 01-7.432 0m9.032-4.026A9.001 9.001 0 0112 3c-4.474 0-8.268 3.12-9.032 7.326m0 0A9.001 9.001 0 0012 21c4.474 0 8.268-3.12 9.032-7.326"></path>
                                </svg>
                                <span class="text-sm font-medium">Share</span>
                            </button>
                        </div>
                        <button class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getVideoPreview() {
    return `
        <div class="space-y-4">
            <div class="relative bg-black rounded-lg overflow-hidden">
                <video id="videoPlayer" src="${currentAsset.url}" controls class="w-full" onloadedmetadata="videoMetadataLoaded(event)" preload="metadata">
                    <!-- Fallback message if video fails to load -->
                    <div class="absolute inset-0 flex items-center justify-center bg-gray-900">
                        <div class="text-center text-white">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                            </svg>
                            <p class="text-sm">Loading video...</p>
                        </div>
                    </div>
                </video>
                <div class="absolute top-4 right-4">
                    <button onclick="downloadAsset()" class="bg-white dark:bg-gray-800 rounded-lg p-2 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Download">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="8" opacity="0.1">
                            <animate attributeName="r" values="8;10;8" dur="2s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.1;0.3;0.1" dur="2s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2"/>
                        <path d="M9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" fill="white"/>
                    </svg>
                    Video Information
                </h4>
                <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Duration:</span> 
                        <span class="ml-2 text-gray-900 dark:text-white font-medium" data-video-duration>
                            ${currentAsset.duration || 'Loading...'}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Resolution:</span> 
                        <span class="ml-2 text-gray-900 dark:text-white font-medium" data-video-resolution>
                            ${currentAsset.resolution || 'Loading...'}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">File Size:</span> 
                        <span class="ml-2 text-gray-900 dark:text-white font-medium">${currentAsset.size}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Format:</span> 
                        <span class="ml-2 text-gray-900 dark:text-white font-medium">${currentAsset.mime_type}</span>
                    </div>
                </div>
                
                <!-- Video Quality Indicator -->
                <div class="mb-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">Quality</span>
                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs font-medium">
                            ${currentAsset.resolution ? getQualityLabel(currentAsset.resolution) : 'Detecting...'}
                        </span>
                    </div>
                </div>
                
                <!-- Playback Speed Control -->
                <div class="pt-3 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">Playback Speed</span>
                        <select id="playbackSpeed" onchange="changePlaybackSpeed(this.value)" class="px-2 py-1 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">
                            <option value="0.5">0.5x</option>
                            <option value="0.75">0.75x</option>
                            <option value="1" selected>1x</option>
                            <option value="1.25">1.25x</option>
                            <option value="1.5">1.5x</option>
                            <option value="2">2x</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getPdfPreview() {
    return `
        <div class="space-y-4">
            <!-- PDF Thumbnail with Click to Expand -->
            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4">
                <div id="pdfThumbnail" class="cursor-pointer group" onclick="expandPdfViewer()">
                    <div class="relative">
                        <img src="/assets/${currentAsset.id}/thumbnail/medium" 
                             alt="${currentAsset.name} thumbnail"
                             class="w-full max-w-md mx-auto rounded-lg shadow-md hover:shadow-lg transition-shadow"
                             onerror="this.src='data:image/svg+xml;base64,${btoa('<svg width="300" height="400" xmlns="http://www.w3.org/2000/svg"><rect width="300" height="400" fill="%23f3f4f6"/><rect width="300" height="400" fill="none" stroke="%23d1d5db" stroke-width="2"/><g transform="translate(130, 180)" fill="%23ef4444"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></g><text x="150" y="220" text-anchor="middle" fill="%236b7280" font-family="Arial" font-size="14">PDF Preview</text></svg>')}'">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity rounded-lg flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity text-white text-center">
                                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                                <p class="text-sm font-medium">Click to expand</p>
                            </div>
                        </div>
                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                            PDF
                        </div>
                    </div>
                </div>
                
                <!-- Expanded PDF Viewer (Initially Hidden) -->
                <div id="expandedPdfViewer" class="hidden mt-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600">
                        <div class="flex items-center justify-between p-2 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">PDF Viewer</h4>
                            <div class="flex items-center space-x-2">
                                <button onclick="downloadPdf()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="Download PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </button>
                                <button onclick="collapsePdfViewer()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="Close">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <iframe src="https://docs.google.com/gview?embedded=1&url=${encodeURIComponent(window.location.origin + currentAsset.url)}" 
                                class="w-full h-96 rounded-b-lg"
                                frameborder="0">
                        </iframe>
                    </div>
                </div>
            </div>
            
            <!-- PDF Information and Actions -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                    </svg>
                    PDF Document
                </h4>
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div><span class="text-gray-600 dark:text-gray-400">File Size:</span> <span class="ml-2 text-gray-900 dark:text-white">${currentAsset.size}</span></div>
                    <div><span class="text-gray-600 dark:text-gray-400">Type:</span> <span class="ml-2 text-gray-900 dark:text-white">${currentAsset.mime_type}</span></div>
                    <div><span class="text-gray-600 dark:text-gray-400">Modified:</span> <span class="ml-2 text-gray-900 dark:text-white">${new Date(currentAsset.updated_at).toLocaleDateString()}</span></div>
                    <div><span class="text-gray-600 dark:text-gray-400">Pages:</span> <span class="ml-2 text-gray-900 dark:text-white">Unknown</span></div>
                </div>
                <div class="flex justify-center space-x-4">
                    <button onclick="downloadAsset()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                            </path>
                        </svg>
                        Download PDF
                    </button>
                    <a href="${currentAsset.url}" target="_blank" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                            </path>
                        </svg>
                        Open in New Tab
                    </a>
                </div>
            </div>
        </div>
    `;
}

function getQualityLabel(resolution) {
    const [width] = resolution.split('x').map(Number);
    
    if (width >= 3840) return '4K Ultra HD';
    if (width >= 2560) return '2K Quad HD';
    if (width >= 1920) return '1080p Full HD';
    if (width >= 1280) return '720p HD';
    if (width >= 854) return '480p SD';
    return '360p Low';
}

function getDocumentPreview() {
    const iconConfig = getIconConfig(currentAsset.type);
    return `
        <div class="space-y-4">
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 rounded-lg flex items-center justify-center ${iconConfig.bg} group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-16 h-16 ${iconConfig.color}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="${iconConfig.path}"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">${currentAsset.name}</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">${currentAsset.size} • ${currentAsset.mime_type}</p>
                
                <!-- Document Information Panel -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-left max-w-md mx-auto">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Document Details</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Type:</span>
                            <span class="text-gray-900 dark:text-white font-medium">${currentAsset.mime_type}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Size:</span>
                            <span class="text-gray-900 dark:text-white font-medium">${currentAsset.size}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Modified:</span>
                            <span class="text-gray-900 dark:text-white font-medium">${new Date(currentAsset.updated_at).toLocaleDateString()}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center space-x-4 mt-6">
                    <button onclick="downloadAsset()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                <animateTransform attributeName="transform" type="translate" values="0,0; 0,2; 0,0" dur="2s" repeatCount="indefinite"/>
                            </path>
                        </svg>
                        Download File
                    </button>
                </div>
            </div>
        </div>
    `;
}

function getTextPreview() {
    return `
        <div class="space-y-4">
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <pre id="textContent" class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap overflow-x-auto">Loading...</pre>
            </div>
        </div>
    `;
}

function getGenericPreview() {
    return `
        <div class="space-y-4">
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-400 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">${currentAsset.name}</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">${currentAsset.size} • ${currentAsset.mime_type}</p>
                <div class="flex justify-center space-x-4">
                    <button onclick="downloadAsset()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download File
                    </button>
                </div>
            </div>
        </div>
    `;
}

function getIconConfig(type) {
    const configs = {
        image: { 
            path: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 
            color: 'text-emerald-500', 
            bg: 'bg-emerald-100 dark:bg-emerald-900/30',
            hoverColor: 'hover:text-emerald-600',
            hoverBg: 'hover:bg-emerald-200 dark:hover:bg-emerald-900/50'
        },
        video: { 
            path: 'M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z', 
            color: 'text-blue-500', 
            bg: 'bg-blue-100 dark:bg-blue-900/30',
            hoverColor: 'hover:text-blue-600',
            hoverBg: 'hover:bg-blue-200 dark:hover:bg-blue-900/50'
        },
        document: { 
            path: 'M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z', 
            color: 'text-red-500', 
            bg: 'bg-red-100 dark:bg-red-900/30',
            hoverColor: 'hover:text-red-600',
            hoverBg: 'hover:bg-red-200 dark:hover:bg-red-900/50'
        },
        text: { 
            path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 
            color: 'text-purple-500', 
            bg: 'bg-purple-100 dark:bg-purple-900/30',
            hoverColor: 'hover:text-purple-600',
            hoverBg: 'hover:bg-purple-200 dark:hover:bg-purple-900/50'
        },
        generic: { 
            path: 'M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z', 
            color: 'text-gray-500', 
            bg: 'bg-gray-100 dark:bg-gray-700',
            hoverColor: 'hover:text-gray-600',
            hoverBg: 'hover:bg-gray-200 dark:hover:bg-gray-600'
        }
    };
    return configs[type] || configs.generic;
}

function toggleCrop() {
    const cropInterface = document.getElementById('cropInterface');
    const image = document.getElementById('cropImage');
    
    if (cropInterface.style.display === 'none') {
        cropInterface.style.display = 'block';
        
        // Initialize Cropper.js
        if (image && !cropper) {
            cropper = new Cropper(image, {
                aspectRatio: NaN, // Free aspect ratio initially
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: true,
            });
        }
        
        // Set initial values
        if (currentAsset.dimensions) {
            const [width, height] = currentAsset.dimensions.split('x').map(Number);
            document.getElementById('cropWidth').placeholder = width;
            document.getElementById('cropHeight').placeholder = height;
        }
    } else {
        cropInterface.style.display = 'none';
        
        // Destroy cropper to restore normal image
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }
}

function resetCrop() {
    if (cropper) {
        cropper.reset();
        document.getElementById('aspectRatio').value = 'free';
        document.getElementById('cropWidth').value = '';
        document.getElementById('cropHeight').value = '';
    }
}

function applyCrop() {
    if (!cropper) return;
    
    const aspectRatio = document.getElementById('aspectRatio').value;
    const quality = parseFloat(document.getElementById('cropQuality').value);
    const width = document.getElementById('cropWidth').value;
    const height = document.getElementById('cropHeight').value;
    
    // Update aspect ratio if not free
    if (aspectRatio !== 'free') {
        cropper.setAspectRatio(parseFloat(aspectRatio));
    }
    
    // Get cropped canvas
    const canvas = cropper.getCroppedCanvas({
        width: width ? parseInt(width) : undefined,
        height: height ? parseInt(height) : undefined,
        minWidth: 1,
        minHeight: 1,
        maxWidth: 4096,
        maxHeight: 4096,
        fillColor: '#fff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    if (canvas) {
        // Convert to blob and download
        canvas.toBlob(function(blob) {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.download = 'cropped_' + currentAsset.name;
            link.href = url;
            link.click();
            URL.revokeObjectURL(url);
        }, 'image/jpeg', quality);
    }
    
    toggleCrop();
}

function videoMetadataLoaded(event) {
    const video = event.target;
    const duration = formatDuration(video.duration);
    const resolution = `${video.videoWidth}x${video.videoHeight}`;
    
    // Update current asset with extracted metadata
    currentAsset.duration = duration;
    currentAsset.resolution = resolution;
    
    // Update the display
    updatePreviewHeader();
    
    // Update video info section if it exists
    const durationElement = document.querySelector('[data-video-duration]');
    const resolutionElement = document.querySelector('[data-video-resolution]');
    
    if (durationElement) durationElement.textContent = duration;
    if (resolutionElement) resolutionElement.textContent = resolution;
}

function changePlaybackSpeed(speed) {
    const video = document.getElementById('videoPlayer');
    if (video) {
        video.playbackRate = parseFloat(speed);
    }
}

function formatDuration(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

function downloadAsset() {
    const link = document.createElement('a');
    link.href = currentAsset.url;
    link.download = currentAsset.name;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Test function for canvas initialization
function testCanvas() {
    console.log('Testing canvas initialization...');
    
    // Hide fallback image and show canvas
    const fallbackImg = document.getElementById('fallbackImage');
    const canvasContainer = document.getElementById('canvasContainer');
    
    if (fallbackImg) {
        fallbackImg.style.display = 'none';
    }
    
    if (canvasContainer) {
        canvasContainer.style.display = 'block';
    }
    
    // Initialize canvas preview
    if (currentAsset && currentAsset.url) {
        console.log('Initializing canvas with URL:', currentAsset.url);
        canvasImagePreview = new CanvasImagePreviewModal();
        canvasImagePreview.init(currentAsset.url);
    } else {
        console.error('No current asset or URL available for canvas');
    }
}

// Canvas Image Preview System
let canvasImagePreview = null;

class CanvasImagePreviewModal {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.image = null;
        this.scale = 1;
        this.minScale = 0.1;
        this.maxScale = 5;
        this.isDragging = false;
        this.dragStartX = 0;
        this.dragStartY = 0;
        this.imageX = 0;
        this.imageY = 0;
        this.filters = {
            brightness: 100,
            contrast: 100,
            saturation: 100,
            blur: 0
        };
    }

    init(imageUrl) {
        console.log('Initializing Canvas Image Preview with URL:', imageUrl);
        this.canvas = document.getElementById('previewCanvas');
        if (!this.canvas) {
            console.error('Canvas element not found!');
            return;
        }

        this.ctx = this.canvas.getContext('2d');
        if (!this.ctx) {
            console.error('Could not get canvas context!');
            return;
        }
        
        console.log('Canvas and context obtained successfully');
        this.setupCanvas();
        this.loadImage(imageUrl);
        this.setupEventListeners();
    }

    setupCanvas() {
        const container = this.canvas.parentElement;
        const rect = container.getBoundingClientRect();
        
        // Set canvas size with device pixel ratio for sharp rendering
        const dpr = window.devicePixelRatio || 1;
        this.canvas.width = rect.width * dpr;
        this.canvas.height = rect.height * dpr;
        this.canvas.style.width = rect.width + 'px';
        this.canvas.style.height = rect.height + 'px';
        
        // Scale context for device pixel ratio
        this.ctx.scale(dpr, dpr);
        
        // Hide loading state
        const loading = document.getElementById('canvasLoading');
        if (loading) loading.style.display = 'none';
        
        console.log('Canvas setup:', {
            width: this.canvas.width,
            height: this.canvas.height,
            containerWidth: rect.width,
            containerHeight: rect.height,
            dpr: dpr
        });
    }

    loadImage(imageUrl) {
        console.log('Loading image:', imageUrl);
        this.image = new Image();
        this.image.crossOrigin = 'anonymous';
        
        this.image.onload = () => {
            console.log('Image loaded successfully:', {
                width: this.image.width,
                height: this.image.height,
                src: this.image.src
            });
            this.centerImage();
            this.render();
            this.updateImageInfo();
        };
        
        this.image.onerror = (error) => {
            console.error('Failed to load image for canvas preview:', error);
            console.error('Image URL was:', imageUrl);
            
            // Try to show error message on canvas
            const container = this.canvas.parentElement;
            const rect = container.getBoundingClientRect();
            this.ctx.fillStyle = '#fef2f2';
            this.ctx.fillRect(0, 0, rect.width, rect.height);
            this.ctx.fillStyle = '#ef4444';
            this.ctx.font = '16px Arial';
            this.ctx.textAlign = 'center';
            this.ctx.fillText('Failed to load image', rect.width / 2, rect.height / 2);
        };
        
        this.image.src = imageUrl;
    }

    centerImage() {
        if (!this.image) return;
        
        const container = this.canvas.parentElement;
        const rect = container.getBoundingClientRect();
        const canvasWidth = rect.width;
        const canvasHeight = rect.height;
        
        const canvasRatio = canvasWidth / canvasHeight;
        const imageRatio = this.image.width / this.image.height;
        
        if (imageRatio > canvasRatio) {
            this.scale = canvasWidth / this.image.width;
        } else {
            this.scale = canvasHeight / this.image.height;
        }
        
        this.imageX = (canvasWidth - this.image.width * this.scale) / 2;
        this.imageY = (canvasHeight - this.image.height * this.scale) / 2;
        
        this.updateZoomLevel();
        
        console.log('Center image:', {
            imageWidth: this.image.width,
            imageHeight: this.image.height,
            canvasWidth: canvasWidth,
            canvasHeight: canvasHeight,
            scale: this.scale,
            imageX: this.imageX,
            imageY: this.imageY
        });
    }

    setupEventListeners() {
        // Mouse events
        this.canvas.addEventListener('mousedown', (e) => this.handleMouseDown(e));
        this.canvas.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        this.canvas.addEventListener('mouseup', () => this.handleMouseUp());
        this.canvas.addEventListener('mouseleave', () => this.handleMouseUp());
        
        // Wheel event for zoom
        this.canvas.addEventListener('wheel', (e) => this.handleWheel(e));
        
        // Touch events for mobile
        this.canvas.addEventListener('touchstart', (e) => this.handleTouchStart(e));
        this.canvas.addEventListener('touchmove', (e) => this.handleTouchMove(e));
        this.canvas.addEventListener('touchend', () => this.handleTouchEnd());
    }

    handleMouseDown(e) {
        this.isDragging = true;
        const rect = this.canvas.getBoundingClientRect();
        this.dragStartX = e.clientX - rect.left - this.imageX;
        this.dragStartY = e.clientY - rect.top - this.imageY;
        this.canvas.style.cursor = 'grabbing';
    }

    handleMouseMove(e) {
        if (!this.isDragging) return;
        
        const rect = this.canvas.getBoundingClientRect();
        this.imageX = e.clientX - rect.left - this.dragStartX;
        this.imageY = e.clientY - rect.top - this.dragStartY;
        this.render();
    }

    handleMouseUp() {
        this.isDragging = false;
        this.canvas.style.cursor = 'move';
    }

    handleWheel(e) {
        e.preventDefault();
        
        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const scaleFactor = e.deltaY > 0 ? 0.9 : 1.1;
        const newScale = Math.max(this.minScale, Math.min(this.maxScale, this.scale * scaleFactor));
        
        if (newScale !== this.scale) {
            // Zoom towards mouse position
            const scaleChange = newScale - this.scale;
            this.imageX -= (x - this.imageX) * (scaleChange / this.scale);
            this.imageY -= (y - this.imageY) * (scaleChange / this.scale);
            this.scale = newScale;
            
            this.render();
            this.updateZoomLevel();
        }
    }

    handleTouchStart(e) {
        if (e.touches.length === 1) {
            this.isDragging = true;
            const rect = this.canvas.getBoundingClientRect();
            this.dragStartX = e.touches[0].clientX - rect.left - this.imageX;
            this.dragStartY = e.touches[0].clientY - rect.top - this.imageY;
        }
    }

    handleTouchMove(e) {
        e.preventDefault();
        if (e.touches.length === 1 && this.isDragging) {
            const rect = this.canvas.getBoundingClientRect();
            this.imageX = e.touches[0].clientX - rect.left - this.dragStartX;
            this.imageY = e.touches[0].clientY - rect.top - this.dragStartY;
            this.render();
        }
    }

    handleTouchEnd() {
        this.isDragging = false;
    }

    render() {
        if (!this.ctx || !this.image) return;
        
        const container = this.canvas.parentElement;
        const rect = container.getBoundingClientRect();
        
        // Clear the entire canvas area
        this.ctx.clearRect(0, 0, rect.width, rect.height);
        
        // Apply filters
        this.ctx.filter = `brightness(${this.filters.brightness}%) contrast(${this.filters.contrast}%) saturate(${this.filters.saturation}%) blur(${this.filters.blur}px)`;
        
        // Draw background
        this.ctx.fillStyle = '#f3f4f6';
        this.ctx.fillRect(0, 0, rect.width, rect.height);
        
        // Draw image
        this.ctx.drawImage(
            this.image,
            this.imageX,
            this.imageY,
            this.image.width * this.scale,
            this.image.height * this.scale
        );
        
        // Reset filter
        this.ctx.filter = 'none';
        
        console.log('Render called:', {
            imageX: this.imageX,
            imageY: this.imageY,
            scaledWidth: this.image.width * this.scale,
            scaledHeight: this.image.height * this.scale,
            canvasWidth: rect.width,
            canvasHeight: rect.height
        });
    }

    updateZoomLevel() {
        const zoomElement = document.getElementById('zoomLevel');
        if (zoomElement) {
            zoomElement.textContent = Math.round(this.scale * 100) + '%';
        }
    }

    updateImageInfo() {
        const dimensionsElement = document.getElementById('imageDimensions');
        if (dimensionsElement && this.image) {
            dimensionsElement.textContent = `${this.image.width} × ${this.image.height}`;
        }
    }

    zoomIn() {
        this.scale = Math.min(this.maxScale, this.scale * 1.2);
        this.render();
        this.updateZoomLevel();
    }

    zoomOut() {
        this.scale = Math.max(this.minScale, this.scale / 1.2);
        this.render();
        this.updateZoomLevel();
    }

    resetZoom() {
        this.centerImage();
        this.render();
    }

    setFilters(filters) {
        this.filters = { ...this.filters, ...filters };
        this.render();
    }

    resetFilters() {
        this.filters = {
            brightness: 100,
            contrast: 100,
            saturation: 100,
            blur: 0
        };
        
        // Reset UI controls
        document.getElementById('brightness').value = 100;
        document.getElementById('contrast').value = 100;
        document.getElementById('saturation').value = 100;
        document.getElementById('blur').value = 0;
        
        // Update labels
        const labels = document.querySelectorAll('#filtersPanel span.text-xs');
        labels[0].textContent = '100%';
        labels[1].textContent = '100%';
        labels[2].textContent = '100%';
        labels[3].textContent = '0px';
        
        this.render();
    }

    destroy() {
        if (this.canvas) {
            this.canvas.removeEventListener('mousedown', this.handleMouseDown);
            this.canvas.removeEventListener('mousemove', this.handleMouseMove);
            this.canvas.removeEventListener('mouseup', this.handleMouseUp);
            this.canvas.removeEventListener('mouseleave', this.handleMouseUp);
            this.canvas.removeEventListener('wheel', this.handleWheel);
            this.canvas.removeEventListener('touchstart', this.handleTouchStart);
            this.canvas.removeEventListener('touchmove', this.handleTouchMove);
            this.canvas.removeEventListener('touchend', this.handleTouchEnd);
        }
    }
}

// Canvas control functions
function zoomIn() {
    if (canvasImagePreview) canvasImagePreview.zoomIn();
}

function zoomOut() {
    if (canvasImagePreview) canvasImagePreview.zoomOut();
}

function resetZoom() {
    if (canvasImagePreview) canvasImagePreview.resetZoom();
}

function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    panel.classList.toggle('hidden');
}

function applyFilters() {
    if (!canvasImagePreview) return;
    
    const filters = {
        brightness: document.getElementById('brightness').value,
        contrast: document.getElementById('contrast').value,
        saturation: document.getElementById('saturation').value,
        blur: document.getElementById('blur').value
    };
    
    // Update labels
    const labels = document.querySelectorAll('#filtersPanel span.text-xs');
    labels[0].textContent = filters.brightness + '%';
    labels[1].textContent = filters.contrast + '%';
    labels[2].textContent = filters.saturation + '%';
    labels[3].textContent = filters.blur + 'px';
    
    canvasImagePreview.setFilters(filters);
}

function resetFilters() {
    if (canvasImagePreview) canvasImagePreview.resetFilters();
}

function toggleFullscreen() {
    const container = document.querySelector('#previewCanvas').parentElement;
    
    if (!document.fullscreenElement) {
        container.requestFullscreen().then(() => {
            setTimeout(() => {
                if (canvasImagePreview) {
                    canvasImagePreview.setupCanvas();
                    canvasImagePreview.render();
                }
            }, 100);
        });
    } else {
        document.exitFullscreen().then(() => {
            setTimeout(() => {
                if (canvasImagePreview) {
                    canvasImagePreview.setupCanvas();
                    canvasImagePreview.render();
                }
            }, 100);
        });
    }
}

// PDF Viewer Functions
function expandPdfViewer() {
    const thumbnail = document.getElementById('pdfThumbnail');
    const expanded = document.getElementById('expandedPdfViewer');
    
    if (thumbnail && expanded) {
        thumbnail.classList.add('hidden');
        expanded.classList.remove('hidden');
    }
}

function collapsePdfViewer() {
    const thumbnail = document.getElementById('pdfThumbnail');
    const expanded = document.getElementById('expandedPdfViewer');
    
    if (thumbnail && expanded) {
        thumbnail.classList.remove('hidden');
        expanded.classList.add('hidden');
    }
}

function downloadPdf() {
    const link = document.createElement('a');
    link.href = currentAsset.url;
    link.download = currentAsset.name;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Load text content for text files
async function loadTextContent() {
    if (currentAsset.type === 'text') {
        try {
            const response = await fetch(currentAsset.url);
            const text = await response.text();
            const textElement = document.getElementById('textContent');
            if (textElement) {
                textElement.textContent = text;
            }
        } catch (error) {
            console.error('Error loading text content:', error);
        }
    }
}

// Event listener for opening previews
document.addEventListener('DOMContentLoaded', function() {
    // Auto-load text content when text preview is shown
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                loadTextContent();
            }
        });
    });
    
    const contentElement = document.getElementById('previewContent');
    if (contentElement) {
        observer.observe(contentElement, { childList: true });
    }
});

// Global function for opening previews from anywhere
window.openAssetPreview = openAssetPreview;
window.closeAssetPreview = closeAssetPreview;
</script>
