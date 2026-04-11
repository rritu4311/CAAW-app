<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the asset.
     */
    public function view(User $user, Asset $asset): bool
    {
        // Uploader can always view their own assets
        if ($asset->uploaded_by === $user->id) {
            return true;
        }

        // If asset is in draft status, only uploader can view it
        if ($asset->isDraft()) {
            return false;
        }

        // If asset is in review or approved, reviewer and admin can view it
        if ($asset->isInReview() || $asset->isApproved()) {
            return $user->canApproveInProject($asset->project);
        }

        return false;
    }

    /**
     * Determine whether the user can create assets.
     */
    public function create(User $user, Asset $asset): bool
    {
        return $user->canUploadToProject($asset->project);
    }

    /**
     * Determine whether the user can update the asset.
     */
    public function update(User $user, Asset $asset): bool
    {
        // Uploader can update their own assets
        if ($asset->uploaded_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the asset.
     */
    public function delete(User $user, Asset $asset): bool
    {
        // Uploader can delete their own assets
        if ($asset->uploaded_by === $user->id) {
            return true;
        }

        // Project owner can delete any asset
        if ($user->isProjectOwner($asset->project)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can submit the asset for review.
     */
    public function submitForReview(User $user, Asset $asset): bool
    {
        // Only uploader can submit their own assets for review
        if ($asset->uploaded_by !== $user->id) {
            return false;
        }

        return $asset->canBeSubmitted();
    }

    /**
     * Determine whether the user can approve the asset.
     */
    public function approve(User $user, Asset $asset): bool
    {
        // Only reviewers and admins can approve assets
        if (!$user->canApproveInProject($asset->project)) {
            return false;
        }

        return $asset->canBeApproved();
    }

    /**
     * Determine whether the user can reject the asset.
     */
    public function reject(User $user, Asset $asset): bool
    {
        // Only reviewers and admins can reject assets
        if (!$user->canApproveInProject($asset->project)) {
            return false;
        }

        return $asset->canBeApproved();
    }

    /**
     * Determine whether the user can request changes on the asset.
     */
    public function requestChanges(User $user, Asset $asset): bool
    {
        // Only reviewers and admins can request changes
        if (!$user->canApproveInProject($asset->project)) {
            return false;
        }

        return $asset->canBeApproved();
    }

    /**
     * Determine whether the user can add annotations to the asset.
     */
    public function addAnnotation(User $user, Asset $asset): bool
    {
        // Only allow annotations for image files
        if (!$asset->isImage()) {
            return false;
        }

        // If asset is in draft, only uploader can add annotations
        if ($asset->isDraft()) {
            return $asset->uploaded_by === $user->id;
        }

        // If asset is in review or approved, uploader, reviewers, and admins can add annotations
        if ($asset->isInReview() || $asset->isApproved()) {
            return $asset->uploaded_by === $user->id || $user->canApproveInProject($asset->project);
        }

        return false;
    }

    /**
     * Determine whether the user can add comments to the asset.
     */
    public function addComment(User $user, Asset $asset): bool
    {
        // If asset is in draft, only uploader can add comments
        if ($asset->isDraft()) {
            return $asset->uploaded_by === $user->id;
        }

        // If asset is in review or approved, uploader, reviewers, and admins can add comments
        if ($asset->isInReview() || $asset->isApproved()) {
            return $asset->uploaded_by === $user->id || $user->canApproveInProject($asset->project);
        }

        return false;
    }

    /**
     * Determine whether the user can update an annotation.
     */
    public function updateAnnotation(User $user, Asset $asset): bool
    {
        // Only the creator of the annotation can update it
        // This will be checked at the annotation level, but we need asset access first
        return $this->view($user, $asset);
    }

    /**
     * Determine whether the user can delete an annotation.
     */
    public function deleteAnnotation(User $user, Asset $asset): bool
    {
        // Only the creator of the annotation can delete it
        // This will be checked at the annotation level, but we need asset access first
        return $this->view($user, $asset);
    }
}
