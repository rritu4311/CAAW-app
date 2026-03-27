<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class AssetPreviewController extends Controller
{
    /**
     * Generate preview for an asset based on its type
     */
    public function preview(Asset $asset)
    {
        if (!$asset->path || !Storage::exists($asset->path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return match (true) {
            $asset->isImage() => $this->previewImage($asset),
            $asset->isVideo() => $this->previewVideo($asset),
            $asset->isDocument() => $this->previewDocument($asset),
            $asset->isText() => $this->previewText($asset),
            default => $this->previewGeneric($asset)
        };
    }

    /**
     * Generate thumbnail for an asset
     */
    public function thumbnail(Asset $asset, $size = 'medium')
    {
        if (!$asset->path || !Storage::exists($asset->path)) {
            return $this->defaultThumbnail($size);
        }

        return match (true) {
            $asset->isImage() => $this->imageThumbnail($asset, $size),
            $asset->isVideo() => $this->videoThumbnail($asset, $size),
            $asset->isDocument() => $this->documentThumbnail($asset, $size),
            default => $this->defaultThumbnail($size)
        };
    }

    /**
     * Generate PDF thumbnail using CDN service
     */
    private function generatePdfThumbnail(Asset $asset, $size = 'medium')
    {
        $sizes = [
            'small' => [150, 200],
            'medium' => [300, 400],
            'large' => [600, 800],
        ];
        
        [$width, $height] = $sizes[$size] ?? $sizes['medium'];
        $pdfUrl = Storage::url($asset->path);
        
        // Use Google Docs Viewer as CDN for PDF thumbnails
        $thumbnailUrl = "https://docs.google.com/gview?embedded=1&url=" . urlencode($pdfUrl);
        
        return redirect($thumbnailUrl);
    }

    /**
     * Get enhanced video metadata
     */
    private function getEnhancedVideoMetadata(Asset $asset)
    {
        $videoPath = Storage::path($asset->path);
        
        // Try to get basic video info using ffprobe if available
        $metadata = [
            'duration' => 'Unknown',
            'resolution' => 'Unknown',
            'bitrate' => 'Unknown',
            'format' => $asset->mime_type,
            'size' => $asset->formatted_size,
        ];
        
        // For now, return placeholder data
        // In production, you could use:
        // - PHP-FFMpeg library
        // - Server-side ffprobe calls
        // - Client-side JavaScript extraction
        
        return $metadata;
    }

    /**
     * Get asset metadata
     */
    public function metadata(Asset $asset)
    {
        if (!$asset->path || !Storage::exists($asset->path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $metadata = [
            'id' => $asset->id,
            'name' => $asset->original_name ?? $asset->name,
            'size' => $asset->formatted_size,
            'mime_type' => $asset->mime_type,
            'type' => $this->getAssetType($asset),
            'created_at' => $asset->created_at,
            'updated_at' => $asset->updated_at,
        ];

        if ($asset->isVideo()) {
            $metadata = array_merge($metadata, $this->getEnhancedVideoMetadata($asset));
        } elseif ($asset->isImage()) {
            $metadata = array_merge($metadata, $this->getImageMetadata($asset));
        }

        return response()->json($metadata);
    }

    /**
     * Preview image with crop functionality
     */
    private function previewImage(Asset $asset)
    {
        $imageData = Storage::get($asset->path);
        
        return Response::make($imageData, 200, [
            'Content-Type' => $asset->mime_type,
            'Content-Disposition' => 'inline; filename="' . $asset->original_name . '"',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    /**
     * Preview video with streaming support
     */
    private function previewVideo(Asset $asset)
    {
        $videoPath = Storage::path($asset->path);
        $fileSize = Storage::size($asset->path);
        
        $headers = [
            'Content-Type' => $asset->mime_type,
            'Accept-Ranges' => 'bytes',
        ];

        // Handle range requests for video streaming
        if (request()->hasHeader('Range')) {
            $range = request()->header('Range');
            $rangeParts = explode('=', $range);
            $rangeParts = explode('-', $rangeParts[1]);
            
            $start = intval($rangeParts[0]);
            $end = isset($rangeParts[1]) ? intval($rangeParts[1]) : $fileSize - 1;
            $length = $end - $start + 1;
            
            $headers['Content-Range'] = "bytes $start-$end/$fileSize";
            $headers['Content-Length'] = $length;
            $headers['Accept-Ranges'] = 'bytes';
            
            $videoData = Storage::get($asset->path);
            $videoData = substr($videoData, $start, $length);
            
            return Response::make($videoData, 206, $headers);
        }

        $headers['Content-Length'] = $fileSize;
        
        return Response::make(Storage::get($asset->path), 200, $headers);
    }

    /**
     * Preview document (PDF first page)
     */
    private function previewDocument(Asset $asset)
    {
        if ($asset->file_type === 'pdf') {
            return $this->previewPDF($asset);
        }
        
        return $this->previewGeneric($asset);
    }

    /**
     * Preview PDF first page as image
     */
    private function previewPDF(Asset $asset)
    {
        // For now, return the PDF file directly
        // In a production environment, you might want to use a library like Imagick or PDF.js
        $pdfData = Storage::get($asset->path);
        
        return Response::make($pdfData, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $asset->original_name . '"',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    /**
     * Preview text files
     */
    private function previewText(Asset $asset)
    {
        $textData = Storage::get($asset->path);
        
        return Response::make($textData, 200, [
            'Content-Type' => $asset->mime_type,
            'Content-Disposition' => 'inline; filename="' . $asset->original_name . '"',
        ]);
    }

    /**
     * Generic file preview
     */
    private function previewGeneric(Asset $asset)
    {
        return response()->json([
            'type' => 'generic',
            'name' => $asset->original_name ?? $asset->name,
            'size' => $asset->formatted_size,
            'mime_type' => $asset->mime_type,
            'download_url' => Storage::url($asset->path),
        ]);
    }

    /**
     * Generate image thumbnail with actual resizing
     */
    private function imageThumbnail(Asset $asset, $size)
    {
        $imageData = Storage::get($asset->path);
        
        try {
            // Try to use GD for image resizing
            $imageInfo = getimagesizefromstring($imageData);
            if ($imageInfo === false) {
                return $this->defaultThumbnail($size, 'image');
            }
            
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mime = $imageInfo['mime'];
            
            // Calculate new dimensions
            $sizes = [
                'small' => [150, 150],
                'medium' => [300, 300],
                'large' => [600, 600],
            ];
            
            [$maxWidth, $maxHeight] = $sizes[$size] ?? $sizes['medium'];
            
            // Calculate aspect ratio
            $aspectRatio = $width / $height;
            
            if ($width > $height) {
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $aspectRatio;
            } else {
                $newHeight = $maxHeight;
                $newWidth = $maxHeight * $aspectRatio;
            }
            
            // Create image resource
            $image = imagecreatefromstring($imageData);
            if ($image === false) {
                return $this->defaultThumbnail($size, 'image');
            }
            
            // Create new image
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Handle transparency for PNG
            if ($mime === 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Resize image
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Capture output
            ob_start();
            
            switch ($mime) {
                case 'image/jpeg':
                    imagejpeg($newImage, null, 85);
                    break;
                case 'image/png':
                    imagepng($newImage, null, 6);
                    break;
                case 'image/gif':
                    imagegif($newImage);
                    break;
                case 'image/webp':
                    imagewebp($newImage, null, 85);
                    break;
                default:
                    imagejpeg($newImage, null, 85);
                    break;
            }
            
            $thumbnailData = ob_get_contents();
            ob_end_clean();
            
            // Clean up
            imagedestroy($image);
            imagedestroy($newImage);
            
            return Response::make($thumbnailData, 200, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'public, max-age=31536000',
            ]);
            
        } catch (\Exception $e) {
            // Fallback to original image if GD fails
            return Response::make($imageData, 200, [
                'Content-Type' => $asset->mime_type,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }
    }

    /**
     * Generate video thumbnail (placeholder with better visual)
     */
    private function videoThumbnail(Asset $asset, $size)
    {
        $sizes = [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ];
        
        [$width, $height] = $sizes[$size] ?? $sizes['medium'];
        
        // Create a better video placeholder SVG
        $svg = <<<SVG
<svg width="{$width}" height="{$height}" xmlns="http://www.w3.org/2000/svg">
    <rect width="{$width}" height="{$height}" fill="#dbeafe"/>
    <rect width="{$width}" height="{$height}" fill="none" stroke="#3b82f6" stroke-width="2"/>
    <g transform="translate({$width/2 - 20}, {$height/2 - 20})" fill="#3b82f6">
        <circle cx="20" cy="20" r="16" opacity="0.2"/>
        <circle cx="20" cy="20" r="16" fill="none" stroke="#3b82f6" stroke-width="2"/>
        <path d="M16 12a6 6 0 018.485 0l3.536 3.536a6 6 0 010 8.485l-3.536 3.536a6 6 0 01-8.485 0l-3.536-3.536a6 6 0 010-8.485l3.536-3.536z"/>
        <path d="M18 20l4-4m0 0l-4-4m4 4h-8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </g>
    <text x="{$width/2}" y="{$height - 20}" text-anchor="middle" fill="#1e40af" font-family="Arial" font-size="14">Video</text>
</svg>
SVG;
        
        return Response::make($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    /**
     * Generate document thumbnail with better PDF placeholder
     */
    private function documentThumbnail(Asset $asset, $size)
    {
        if ($asset->file_type === 'pdf') {
            $sizes = [
                'small' => [150, 200],
                'medium' => [300, 400],
                'large' => [600, 800],
            ];
            
            [$width, $height] = $sizes[$size] ?? $sizes['medium'];
            
            // Create a PDF-like placeholder
            $svg = <<<SVG
<svg width="{$width}" height="{$height}" xmlns="http://www.w3.org/2000/svg">
    <rect width="{$width}" height="{$height}" fill="#fee2e2"/>
    <rect width="{$width}" height="{$height}" fill="none" stroke="#ef4444" stroke-width="2"/>
    <g transform="translate({$width/2 - 20}, {$height/2 - 40})" fill="#ef4444">
        <rect x="0" y="0" width="40" height="50" rx="2" opacity="0.2"/>
        <rect x="0" y="0" width="40" height="50" rx="2" fill="none" stroke="#ef4444" stroke-width="2"/>
        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
    </g>
    <!-- Document lines -->
    <g opacity="0.3">
        <rect x="20" y="{$height/2 + 20}" width="{$width - 40}" height="2" fill="#991b1b"/>
        <rect x="20" y="{$height/2 + 30}" width="{$width - 60}" height="2" fill="#991b1b"/>
        <rect x="20" y="{$height/2 + 40}" width="{$width - 50}" height="2" fill="#991b1b"/>
        <rect x="20" y="{$height/2 + 50}" width="{$width - 70}" height="2" fill="#991b1b"/>
    </g>
    <text x="{$width/2}" y="{$height - 20}" text-anchor="middle" fill="#991b1b" font-family="Arial" font-size="14">PDF Document</text>
</svg>
SVG;
            
            return Response::make($svg, 200, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }
        
        return $this->defaultThumbnail($size, $asset->file_type);
    }

    /**
     * Default thumbnail for unsupported types
     */
    private function defaultThumbnail($size, $type = 'generic')
    {
        $sizes = [
            'small' => [64, 64],
            'medium' => [128, 128],
            'large' => [256, 256],
        ];
        
        [$width, $height] = $sizes[$size] ?? $sizes['medium'];
        
        // Create a simple SVG placeholder
        $svg = $this->createPlaceholderSVG($width, $height, $type);
        
        return Response::make($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    /**
     * Create SVG placeholder
     */
    private function createPlaceholderSVG($width, $height, $type)
    {
        $colors = [
            'image' => '#10b981',
            'video' => '#3b82f6',
            'pdf' => '#ef4444',
            'doc' => '#6366f1',
            'generic' => '#6b7280',
        ];
        
        $color = $colors[$type] ?? $colors['generic'];
        $icons = [
            'image' => '<path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
            'video' => '<path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>',
            'pdf' => '<path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>',
            'doc' => '<path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>',
            'generic' => '<path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>',
        ];
        
        $icon = $icons[$type] ?? $icons['generic'];
        
        return <<<SVG
<svg width="{$width}" height="{$height}" xmlns="http://www.w3.org/2000/svg">
    <rect width="{$width}" height="{$height}" fill="{$color}" opacity="0.1"/>
    <rect width="{$width}" height="{$height}" fill="none" stroke="{$color}" stroke-width="2"/>
    <g transform="translate({$width/2 - 12}, {$height/2 - 12})" fill="{$color}">
        {$icon}
    </g>
</svg>
SVG;
    }

    /**
     * Get video metadata
     */
    private function getVideoMetadata(Asset $asset)
    {
        // In production, you'd use FFmpeg to get actual metadata
        return [
            'duration' => '0:00',
            'resolution' => '1920x1080',
            'bitrate' => 'unknown',
        ];
    }

    /**
     * Get image metadata
     */
    private function getImageMetadata(Asset $asset)
    {
        // In production, you'd use GD or Imagick to get actual metadata
        return [
            'dimensions' => '1920x1080',
            'format' => 'JPEG',
        ];
    }

    /**
     * Get asset type category
     */
    private function getAssetType(Asset $asset)
    {
        return match (true) {
            $asset->isImage() => 'image',
            $asset->isVideo() => 'video',
            $asset->isDocument() => 'document',
            $asset->isText() => 'text',
            default => 'generic'
        };
    }
}
