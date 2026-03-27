@php
    $iconClasses = [
        'image' => 'text-emerald-500',
        'video' => 'text-blue-500', 
        'document' => 'text-red-500',
        'text' => 'text-purple-500',
        'generic' => 'text-gray-500'
    ];
@endphp

<!-- Enhanced SVG Icon Components -->

<!-- Image Icon with Animation -->
@if($type === 'image')
<svg class="w-6 h-6 {{ $iconClasses['image'] }} transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <defs>
        <linearGradient id="imageGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
        </linearGradient>
    </defs>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
          stroke="url(#imageGradient)">
        <animate attributeName="stroke-opacity" values="0.6;1;0.6" dur="3s" repeatCount="indefinite"/>
    </path>
    <circle cx="12" cy="8" r="1" fill="url(#imageGradient)">
        <animate attributeName="r" values="1;1.5;1" dur="2s" repeatCount="indefinite"/>
    </circle>
</svg>
@endif

<!-- Video Icon with Pulse Animation -->
@if($type === 'video')
<svg class="w-6 h-6 {{ $iconClasses['video'] }} transition-all duration-300 group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24">
    <defs>
        <radialGradient id="videoGradient">
            <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#1d4ed8;stop-opacity:1" />
        </radialGradient>
    </defs>
    <circle cx="12" cy="12" r="8" fill="url(#videoGradient)" opacity="0.1">
        <animate attributeName="r" values="8;10;8" dur="2s" repeatCount="indefinite"/>
        <animate attributeName="opacity" values="0.1;0.3;0.1" dur="2s" repeatCount="indefinite"/>
    </circle>
    <circle cx="12" cy="12" r="8" fill="none" stroke="url(#videoGradient)" stroke-width="2"/>
    <path d="M9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" fill="white"/>
</svg>
@endif

<!-- Document Icon with Fold Animation -->
@if($type === 'document')
<svg class="w-6 h-6 {{ $iconClasses['document'] }} transition-all duration-300 group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24">
    <defs>
        <linearGradient id="docGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#dc2626;stop-opacity:1" />
        </linearGradient>
    </defs>
    <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" 
          fill="url(#docGradient)"/>
    <path d="M12 2.586L15.414 6H12V2.586z" fill="white" opacity="0.3">
        <animateTransform attributeName="transform" type="rotate" values="0 12 4;5 12 4;0 12 4" dur="4s" repeatCount="indefinite"/>
    </path>
    <rect x="6" y="10" width="8" height="2" rx="1" fill="white" opacity="0.8"/>
    <rect x="6" y="14" width="6" height="2" rx="1" fill="white" opacity="0.6"/>
</svg>
@endif

<!-- Text Icon with Line Animation -->
@if($type === 'text')
<svg class="w-6 h-6 {{ $iconClasses['text'] }} transition-all duration-300 group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24">
    <defs>
        <linearGradient id="textGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#a855f7;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#9333ea;stop-opacity:1" />
        </linearGradient>
    </defs>
    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" 
          fill="url(#textGradient)"/>
    <line x1="9" y1="12" x2="15" y2="12" stroke="white" stroke-width="2" stroke-linecap="round">
        <animate attributeName="x2" values="9;15;9" dur="3s" repeatCount="indefinite"/>
    </line>
    <line x1="9" y1="16" x2="15" y2="16" stroke="white" stroke-width="2" stroke-linecap="round">
        <animate attributeName="x2" values="9;15;9" dur="3s" begin="0.5s" repeatCount="indefinite"/>
    </line>
</svg>
@endif

<!-- Generic File Icon with Bounce -->
@if($type === 'generic')
<svg class="w-6 h-6 {{ $iconClasses['generic'] }} transition-all duration-300 group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24">
    <defs>
        <linearGradient id="genericGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#6b7280;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#4b5563;stop-opacity:1" />
        </linearGradient>
    </defs>
    <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" 
          fill="url(#genericGradient)">
        <animateTransform attributeName="transform" type="bounce" values="0,0; 0,-3; 0,0" dur="2s" repeatCount="indefinite"/>
    </path>
    <circle cx="8" cy="10" r="1" fill="white" opacity="0.6"/>
    <circle cx="12" cy="10" r="1" fill="white" opacity="0.6"/>
    <circle cx="8" cy="14" r="1" fill="white" opacity="0.4"/>
    <circle cx="12" cy="14" r="1" fill="white" opacity="0.4"/>
</svg>
@endif

<!-- Animated Loading Spinner SVG -->
<svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>

<!-- Success Checkmark SVG -->
<svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
        <animate attributeName="stroke-dasharray" values="0 100;100 0" dur="1s" fill="freeze"/>
    </path>
</svg>

<!-- Error X SVG -->
<svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
        <animate attributeName="stroke-dasharray" values="0 100;100 0" dur="1s" fill="freeze"/>
    </path>
</svg>

<!-- Download Icon with Animation -->
<svg class="w-5 h-5 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
        <animateTransform attributeName="transform" type="translate" values="0,0; 0,2; 0,0" dur="2s" repeatCount="indefinite"/>
    </path>
</svg>

<!-- Upload Icon with Animation -->
<svg class="w-5 h-5 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
        <animateTransform attributeName="transform" type="translate" values="0,0; 0,-2; 0,0" dur="2s" repeatCount="indefinite"/>
    </path>
</svg>

<!-- Eye/Preview Icon with Pulse -->
<svg class="w-5 h-5 transition-all duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="3" opacity="0.3">
        <animate attributeName="r" values="3;5;3" dur="2s" repeatCount="indefinite"/>
        <animate attributeName="opacity" values="0.3;0.1;0.3" dur="2s" repeatCount="indefinite"/>
    </circle>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
</svg>

<!-- Crop Icon with Rotate Animation -->
<svg class="w-5 h-5 transition-all duration-200 hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5">
        <animateTransform attributeName="transform" type="rotate" values="0 12 12;360 12 12" dur="10s" repeatCount="indefinite"/>
    </path>
</svg>
