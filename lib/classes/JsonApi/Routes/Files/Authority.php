<?php

namespace JsonApi\Routes\Files;

use User;
use JsonApi\Routes\Courses\Authority as CoursesAuth;
use JsonApi\Routes\Users\Authority as UsersAuth;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authority
{
    public static function canShowFileArea(User $user, \SimpleORMap $resource): bool
    {
        if (
            $resource instanceof \Course &&
            !CoursesAuth::canShowCourse($user, $resource, CoursesAuth::SCOPE_EXTENDED)
        ) {
            return false;
        }

        $info = \PluginManager::getInstance()->getPluginInfo('CodeDocuments');
        if (!\PluginManager::getInstance()->isPluginActivated($info['id'], $resource->getId())) {
            return false;
        }

        return ($folder = \Folder::findTopFolder($resource->getId())) &&
            ($rootFolder = $folder->getTypedFolder()) &&
            $rootFolder->isVisible($user->getId());
    }

    public static function canShowFolder(User $user, \FolderType $folder)
    {
        return $folder->isReadable($user->id);
    }

    public static function canUpdateFolder(User $user, \FolderType $folder)
    {
        return $folder->isEditable($user->id);
    }

    public static function canDeleteFolder(User $user, \FolderType $folder)
    {
        return $folder->isEditable($user->id);
    }

    public static function canShowFileRef(User $user, \FileRef $fileRef)
    {
        return $fileRef->getFileType()->isVisible($user->id) || $fileRef->getFileType()->isDownloadable($user->id);
    }

    public static function canUpdateFileRef(User $user, \FileRef $fileRef)
    {
        return $fileRef->getFileType()->isWritable($user->id);
    }

    public static function canDeleteFileRef(User $user, \FileRef $fileRef)
    {
        return $fileRef->getFileType()->isWritable($user->id);
    }

    public static function canDownloadFileRef(User $user, \FileRef $fileRef)
    {
        return $fileRef->getFileType()->isDownloadable($user->id);
    }

    public static function canShowFile(User $user, \File $file)
    {
        return $file['user_id'] === $user->id ||
            0 <
                count(
                    $file->refs->filter(function (\FileRef $ref) use ($user) {
                        $folder = $ref->foldertype;

                        return $folder && $folder->isVisible($user->id) && $folder->isReadable($user->id);
                    }, 1)
                );
    }

    public static function canUpdateFile(User $user, \File $file)
    {
        return 0 <
            count(
                $file->refs->filter(function (\FileRef $ref) use ($user) {
                    return $ref->getFileType()->isWritable($user->id);
                }, 1)
            );
    }

    public static function canIndexCourse(User $user, \Course $course)
    {
        return CoursesAuth::canShowCourse($user, $course, CoursesAuth::SCOPE_EXTENDED);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public static function canIndexInstitute(User $user, \Institute $institute)
    {
        return true;
    }

    public static function canIndexUser(User $user, \User $otherUser)
    {
        return UsersAuth::canShowUser($user, $otherUser);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public static function canShowTermsOfUse(User $user, \ContentTermsOfUse $terms)
    {
        return true; // !!
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public static function canIndexTermsOfUse(User $user)
    {
        return true; // !!
    }

    public static function canCreateSubfolder(User $user, \FolderType $folder)
    {
        return $folder->isSubfolderAllowed($user->id);
    }

    public static function canCreateFileRefsInFolder(User $user, \FolderType $folder)
    {
        return $folder->isWritable($user->id);
    }

    public static function canCopyFolder(User $user, \FolderType $sourceFolder, \FolderType $destinationFolder)
    {
        return self::canCreateFileRefsInFolder($user, $destinationFolder) && self::canShowFolder($user, $sourceFolder);
    }
}
