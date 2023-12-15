<?php

/**
 *
 * @author Brendan Wells brendan.wells6@gmail.com
 *
 * Filer class
 *
 */

namespace Files;

/**
 *
 * Class for handling file operations
 * 
 * @param    array  $rawUpload The html upload array
 *
 */
interface FilerInterface {
    public function handleUpload($rawUpload);
    public function handleDownload($csvInMemory, $mode);
}

//abstract class Filer implements FilerInterface
abstract class Filer
{

    // @const string where new uploads are moved
    private const UPLOADDIRECTORY = '../uploads/';

    // @const string where new csv files are offered for download
    private const DOWNLOADDIRECTORY = '../downloads/';

    // @const string the filedname ID used on the html form
    protected const FILEFIELD = 'fileUpload';

    // @const string list of allowed file extensions to be uploaded
    private const ALLOWEDTYPES = ['csv'];

    // @const string allowed mime type an uploaded file can be
    private const ALLOWEDMIMES = [
        'text/x-comma-separated-values',
        'text/comma-separated-values',
        'application/octet-stream',
        'application/x-csv',
        'text/x-csv',
        'text/csv',
        'application/csv',
        'text/plain'
    ];

    /**
     *
     * Accepts html file upload array and triggers
     * validations
     * 
     * @param    array  $rawUpload The html upload array
     *
     */
    public function __construct($rawUpload)
    {
        if ($this->validatedTmpUpload($rawUpload)) {
            $this->handleUpload($rawUpload);
        }
    }

    /**
     *
     * Accepts html file upload array and triggers
     * validations before passing on for storage
     * 
     * @param    array  $rawUpload The html upload array
     *
     */
    private function validatedTmpUpload($fileToTest)
    {
        // Do we have a file to test
        if (isset($fileToTest)) {

            // Does the file extension make sense
            $uploadExtension = strtolower(pathinfo($fileToTest[self::FILEFIELD]['full_path'], PATHINFO_EXTENSION));
            if (in_array($uploadExtension, self::ALLOWEDTYPES)) {

                // Check mime type again known good mimes
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $fileToTest[self::FILEFIELD]['tmp_name']);
                finfo_close($finfo);
                if (in_array($mime, self::ALLOWEDMIMES)) {

                    // Present, correct extension, mime checks out
                    return true;
                }
            }
        }
        return $validatedUpload = false;
    }

    /**
     *
     * Accepts html file upload array and triggers
     * after validation, moves and renames the file
     * 
     * @param    array  $rawUpload The html upload array
     * 
     * @return  string The path to the uploaded file
     *
     */
    public function handleUpload($rawUpload)
    {
        if (!is_file($rawUpload[self::FILEFIELD]['tmp_name'])) {
            throw new \Exception('601: Csv File Not Found');
        } else {
            try {
                // Move the file from the upload_tmp_dir php.ini location to the configured location
                move_uploaded_file($rawUpload[self::FILEFIELD]['tmp_name'], self::UPLOADDIRECTORY . $rawUpload[self::FILEFIELD]['name']);
            } catch (\Exception $e) {
                echo ''. $e->getMessage() .'';
            }
        }

        // Return actual path and original name of uploaded validated and moved file
        return self::UPLOADDIRECTORY . basename($rawUpload[self::FILEFIELD]['name']);
    }

    /**
     *
     * Accepts an array of values and writes them to a
     * CSV formatted file
     * 
     * @param   array  $csvInMemory The array of values
     * @param   string  $mode "simple" | "detail"
     * 
     * @return  string The path to the written file
     * 
     * either work only with the values prescribed in the brief
     * or show off a little
     *
     */
    public function handleDownload($csvInMemory, $mode) {

        // Get a unique filename for this export
        $csvExportFilename = $this->generateFilename();

        // Open a file write handle
        $fp = fopen(self::DOWNLOADDIRECTORY.$csvExportFilename, 'wb');

        // Iterate over array and output csv rows
        if ($mode == 'simple') {

            foreach ($csvInMemory as $key => $fields) {
                fputcsv($fp, [$key, $fields]);
            }

        } else if ($mode == 'detail') {
            foreach ($csvInMemory as $fields) {
                fputcsv($fp, $fields);
            }
        } else {
            throw new \Exception('no mode set for download prep');
        }

        // Close the handle
        fclose($fp);

        // Return a path for the HRef
        return substr(str_replace('\\', '/', self::DOWNLOADDIRECTORY.$csvExportFilename), 3);
    }

    /**
     *
     * Helper to return a filename with
     * generated hash for uniqueness
     * 
     * @return  string The generated filename
     * 
     */
    private function generateFilename()
    {

        // Convert full disk path to relative for download links
        $relativeDownloadsPath = substr(self::DOWNLOADDIRECTORY, strlen(getcwd())+1);

        // Get the date in a simple format (ran out of time to implement something more suitable like Carbon::)
        date_default_timezone_set('Europe/London');
        $fileDate = date('Y-m-d-H-i-s');

        // Generate unique Id as the downloads are commonn to all users and we don't want users guessing other users generated file names
        $uniqueId = uniqid();

        // Return the compounded relative path and filename ready for use
        return $relativeDownloadsPath.$fileDate.'_'.$uniqueId.'.csv';

    }
}
