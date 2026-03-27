<div id="assetPreviewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" onclick="closeAssetPreview()"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-hidden"
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
            <div id="previewContent" class="p-4 overflow-auto" style="max-height: calc(90vh - 120px);">
                <!-- Content will be dynamically loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
let currentAsset = null;
let cropper = null;

function openAssetPreview(assetId) {
    const modal = document.getElementById('assetPreviewModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    loadAsset(assetId);
}

function closeAssetPreview() {
    const modal = document.getElementById('assetPreviewModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    currentAsset = null;
    
    // Destroy cropper if it exists
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

async function loadAsset(assetId) {
    try {
        const response = await fetch(`/assets/${assetId}/metadata`);
        const asset = await response.json();
        
        currentAsset = {
            ...asset,
            url: `/assets/${assetId}/preview`
        };
        
        updatePreviewHeader();
        updatePreviewContent();
    } catch (error) {
        console.error('Error loading asset:', error);
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
    const content = document.getElementById('previewContent');
    
    switch (currentAsset.type) {
        case 'image':
            content.innerHTML = getImagePreview();
            break;
        case 'video':
            content.innerHTML = getVideoPreview();
            break;
        case 'document':
            content.innerHTML = currentAsset.mime_type === 'application/pdf' ? getPdfPreview() : getDocumentPreview();
            break;
        case 'text':
            content.innerHTML = getTextPreview();
            break;
        default:
            content.innerHTML = getGenericPreview();
    }
}

function getImagePreview() {
    return `
        <div class="space-y-4">
            <div class="relative">
                <img id="cropImage" src="${currentAsset.url}" alt="${currentAsset.name}" 
                     class="w-full h-auto rounded-lg shadow-lg">
                <div class="absolute top-4 right-4 flex space-x-2">
                    <button onclick="toggleCrop()" class="bg-white dark:bg-gray-800 rounded-lg p-2 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Crop Image">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300 transition-all duration-200 hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5">
                            </path>
                        </svg>
                    </button>
                    <button onclick="downloadAsset()" class="bg-white dark:bg-gray-800 rounded-lg p-2 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Download">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="cropInterface" style="display: none;" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Crop Image</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Aspect Ratio</label>
                        <select id="aspectRatio" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                            <option value="free">Free</option>
                            <option value="1">1:1 (Square)</option>
                            <option value="1.333">4:3</option>
                            <option value="1.777">16:9</option>
                            <option value="0.75">3:4</option>
                            <option value="0.562">9:16</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Quality</label>
                        <select id="cropQuality" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                            <option value="0.9">High (90%)</option>
                            <option value="0.8" selected>Medium (80%)</option>
                            <option value="0.6">Low (60%)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Width (px)</label>
                            <input type="number" id="cropWidth" placeholder="Auto" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Height (px)</label>
                            <input type="number" id="cropHeight" placeholder="Auto" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="applyCrop()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Apply Crop
                    </button>
                    <button onclick="resetCrop()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset
                    </button>
                    <button onclick="toggleCrop()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors text-sm">Cancel</button>
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
