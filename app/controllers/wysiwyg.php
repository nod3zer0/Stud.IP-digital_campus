<?php
/**
 * wysiwyg.php - Provide web services for the WYSIWYG editor.
 *
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */

class WysiwygController extends AuthenticatedController
{
    const FOLDER_NAME = 'Wysiwyg Uploads';
    const FOLDER_DESCRIPTION = 'Vom WYSIWYG Editor hochgeladene Dateien.';

    /**
     * Handle the WYSIWYG editor's file uploads.
     *
     * Files must be posted as an HTML array named "files":
     *   <input type="file" name="files[]" multiple />
     *
     * Files will be stored in a folder named "Wysiwyg Uploads". If the
     * folder doesn't exist, it will be created.
     *
     * Results are returned as JSON-encoded array:
     *
     * [{"name": filename, "type": mime-type, "url": download-link},
     *  {"name": filename, "type": mime-type, "error": error-message},
     *  ...]
     *
     * Each array-entry corresponds to a single file, each file that was
     * sent with the post request has exactly one entry.
     *
     * Entries with the property "url" correspond to successful uploads.
     * Entries with the property "error" correspond to failed uploads.
     */
    public function upload_action()
    {
        try {
            CSRFProtection::verifyUnsafeRequest();

            $user = User::findCurrent();

            //try to find an already existing WYSIWYG folder inside the
            //user's personal file area:
            $wysiwyg_folder = Folder::findOneBySql(
                "range_id = :user_id
                AND folder_type = 'PublicFolder'
                AND name = :wysiwyg_name ",
                [
                    'user_id' => $user->id,
                    'wysiwyg_name' => self::FOLDER_NAME
                ]
            );

            if (!$wysiwyg_folder) {
                //get the top folder of the user's personal file area and its FolderType:
                $top_folder = Folder::findTopFolder($user->id)->getTypedFolder();

                $wysiwyg_folder = new PublicFolder(Folder::build([
                    'user_id' => $user->id,
                    'name' => self::FOLDER_NAME,
                    'description' => self::FOLDER_DESCRIPTION
                ]));

                if (!$top_folder->createSubfolder($wysiwyg_folder)) {
                    $this->render_json(_('WYSIWYG-Ordner für hochgeladene Dateien konnte nicht erstellt werden!'));
                    return;
                }
            } else {
                $wysiwyg_folder = $wysiwyg_folder->getTypedFolder();
            }

            //Ok, we have our folder where we can store the uploaded files in:
            $response = [];

            if (!$wysiwyg_folder->isWritable($user->id)) {
                throw new AccessDeniedException();
            }
            if (Request::isPost() && is_array($_FILES['files'])) {
                $validatedFiles = FileManager::handleFileUpload(
                    $_FILES['files'],
                    $wysiwyg_folder,
                    $GLOBALS['user']->id
                );

                if (count($validatedFiles['error']) > 0) {
                    // error during upload: display error message:
                    $this->render_json(_('Beim Hochladen ist ein Fehler aufgetreten ') . "\n" .
                        join("\n", $validatedFiles['error'])
                    );
                    return;
                }

                //all files were uploaded successfully:
                $storedFiles = [];
                foreach ($validatedFiles['files'] as $file) {
                    $fileref = $file->getFileRef();
                    $response['files'][] = [
                        'name' => $fileref->name,
                        'type' => $fileref->mime_type,
                        'url'  => $fileref->getDownloadURL()
                    ];
                }
            }
        } catch (AccessDeniedException $e) {
            $response = $e->getMessage();
        }
        $this->render_json($response); // send HTTP response to client
    }

    /**
     * Display the WYSIWYG editor's help window.
     */
    public function a11yhelp_action()
    {
        // nothing to do
        PageLayout::setTitle(_('Hilfe zur Bedienung des Editors'));
    }
}
