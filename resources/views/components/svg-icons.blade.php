{{-- SVG Icon Utility Component --}}

{{-- Usage: @svg('icon-name', ['class' => 'additional-classes', 'size' => 'w-6 h-6']) --}}

@php
 function getSvgIcon($iconName, $attributes = []) {
 $defaultClasses = 'transition-all duration-200';
 $size = $attributes['size'] ?? 'w-6 h-6';
 $additionalClasses = $attributes['class'] ?? '';
 $classes = "$defaultClasses $size $additionalClasses";
 
 $icons = [
 'image' => [
 'path' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
 'gradient' => 'imageGradient',
 'color' => '#10b981'
 ],
 'video' => [
 'path' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z',
 'gradient' => 'videoGradient',
 'color' => '#3b82f6'
 ],
 'document' => [
 'path' => 'M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z',
 'gradient' => 'docGradient',
 'color' => '#ef4444'
 ],
 'text' => [
 'path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
 'gradient' => 'textGradient',
 'color' => '#a855f7'
 ],
 'generic' => [
 'path' => 'M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z',
 'gradient' => 'genericGradient',
 'color' => '#6b7280'
 ],
 'download' => [
 'path' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
 'gradient' => null,
 'color' => '#059669'
 ],
 'upload' => [
 'path' => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
 'gradient' => null,
 'color' => '#3b82f6'
 ],
 'preview' => [
 'path' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
 'gradient' => null,
 'color' => '#1d4ed8'
 ],
 'crop' => [
 'path' => 'M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5',
 'gradient' => null,
 'color' => '#7c3aed'
 ],
 'delete' => [
 'path' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
 'gradient' => null,
 'color' => '#dc2626'
 ],
 'success' => [
 'path' => 'M5 13l4 4L19 7',
 'gradient' => null,
 'color' => '#10b981'
 ],
 'error' => [
 'path' => 'M6 18L18 6M6 6l12 12',
 'gradient' => null,
 'color' => '#ef4444'
 ],
 'loading' => [
 'path' => 'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z',
 'gradient' => null,
 'color' => '#3b82f6'
 ],
 'back' => [
 'path' => 'M10 19l-7-7m0 0l7-7m-7 7h18',
 'gradient' => null,
 'color' => '#6b7280'
 ]
 ];
 
 $icon = $icons[$iconName] ?? $icons['generic'];
 
 if ($icon['gradient']) {
 return "<svg class=\"$classes\" fill=\"none\" stroke=\"url(#{$icon['gradient']})\" viewBox=\"0 0 24 24\">
 <defs>
 <linearGradient id=\"{$icon['gradient']}\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\">
 <stop offset=\"0%\" style=\"stop-color:{$icon['color']};stop-opacity:1\" />
 <stop offset=\"100%\" style=\"stop-color:" . adjustColor($icon['color'], -20) . ";stop-opacity:1\" />
 </linearGradient>
 </defs>
 <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"{$icon['path']}\"/>
 </svg>";
 } else {
 return "<svg class=\"$classes\" fill=\"none\" stroke=\"{$icon['color']}\" viewBox=\"0 0 24 24\">
 <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"{$icon['path']}\"/>
 </svg>";
 }
 }
 
 function adjustColor($color, $percent) {
 // Simple color adjustment function
 $num = hexdec(ltrim($color, '#'));
 $amount = max(-255, min(255, $percent * 2.55));
 $r = max(0, min(255, ($num >> 16) + $amount));
 $g = max(0, min(255, (($num >> 8) & 0x00FF) + $amount));
 $b = max(0, min(255, ($num & 0x0000FF) + $amount));
 return '#' . str_pad(dechex($r << 16 | $g << 8 | $b), 6, '0', STR_PAD_LEFT);
 }
@endphp

{{-- Animated SVG Icons with Hover Effects --}}

@if($iconName === 'image-animated')
<svg class="{{ $classes }} group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

@if($iconName === 'video-animated')
<svg class="{{ $classes }} group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24">
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

@if($iconName === 'download-animated')
<svg class="{{ $classes }} hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
 <animateTransform attributeName="transform" type="translate" values="0,0; 0,2; 0,0" dur="2s" repeatCount="indefinite"/>
 </path>
</svg>
@endif

@if($iconName === 'preview-animated')
<svg class="{{ $classes }} hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <circle cx="12" cy="12" r="3" opacity="0.3">
 <animate attributeName="r" values="3;5;3" dur="2s" repeatCount="indefinite"/>
 <animate attributeName="opacity" values="0.3;0.1;0.3" dur="2s" repeatCount="indefinite"/>
 </circle>
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
</svg>
@endif

@if($iconName === 'loading-spinner')
<svg class="animate-spin {{ $classes }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>
@endif

@if($iconName === 'success-check')
<svg class="{{ $classes }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
 <animate attributeName="stroke-dasharray" values="0 100;100 0" dur="1s" fill="freeze"/>
 </path>
</svg>
@endif

@if($iconName === 'error-x')
<svg class="{{ $classes }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
 <animate attributeName="stroke-dasharray" values="0 100;100 0" dur="1s" fill="freeze"/>
 </path>
</svg>
@endif

{{-- Basic Static Icons --}}
@if(!in_array($iconName, ['image-animated', 'video-animated', 'download-animated', 'preview-animated', 'loading-spinner', 'success-check', 'error-x']))
 {!! getSvgIcon($iconName, $attributes) !!}
@endif
