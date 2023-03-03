<?php

/**
 * Abstraction for checking files with an external virus scanner.
 * Supports connections via TCP or socket and is focused on using ClamAV at the moment.
 * Derived from https://github.com/nextcloud/files_antivirus
 *
 * @author  Thomas Hackl <hackl@data-quest.de>
 * @author  Sebastian Biller <s.biller@tu-braunschweig.de>
 * @license GPL 2 or later
 * @since 5.3
 */

class Virusscanner
{
    // Contains the singleton used.
    protected static $instance;

    // Definitions for possible status.
    public const SCANRESULT_UNCHECKED = -1;
    public const SCANRESULT_CLEAN = 0;
    public const SCANRESULT_INFECTED = 1;

    /**
     * Scans the given path for viruses.
     *
     * @param string $path
     * @return array Contains the found virus signature, error message or is an empty array on successful scan
     */
    public static function scan(string $path): array
    {
        // Get virus scanner singleton.
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        $scanner = static::$instance;

        try {
            // Connect to scanner.
            $handle = $scanner->connect();
            // Read file.
            $file = $scanner->readFile($path);

            // ClamAV has a maximum stream length, so we need to track how much data has already been sent.
            $bytesWritten = $scanner->sendContent($handle, "nINSTREAM\n");

            // Send file chunks via socket or TCP.
            while ($chunk = @fread($file, 8192)) {
                $chunkLength = pack('N', strlen($chunk));

                // Send next chunk.
                if ($bytesWritten + strlen($chunk) <= Config::get()->VIRUSSCAN_MAX_STREAMLENGTH) {
                    $bytesWritten += $scanner->sendContent($handle, $chunkLength . $chunk);
                // Stream limit will be reached: abort.
                } else {
                    return [
                        'error' => _('Die Datei ist zu groß, um vom Virenscanner gelesen zu werden.')
                    ];
                }
            }

            fclose($file);

            // All chunks have been sent - signal stream end and get scanner response.
            $result = $scanner->finalize($handle);

            // Nothing found.
            if ($result['status'] == static::SCANRESULT_CLEAN) {
                return [];
            // Virus found or error.
            } else if ($result['status'] == static::SCANRESULT_INFECTED) {
                return [
                    'found' => $result['details']
                ];
            } else {
                return [
                    'error' => $result['details']
                ];
            }

        // There has been an error: send error message back.
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }

        return [];
    }

    /**
     * Finalized constructor so that the instantition in scan() will never fail.
     */
    protected final function __construct()
    {
    }

    /**
     * Establishes a connection to virus scanner via socket or TCP, depending on Stud.IP configuration.
     *
     * @return resource|null
     */
    protected function connect()
    {
        $handle = false;

        // Use socket connection.
        if (Config::get()->VIRUSSCAN_SOCKET) {
            $handle = @stream_socket_client('unix://' . Config::get()->VIRUSSCAN_SOCKET, $errno, $errstr, 5);
            // use TCP connection.
        } else if (Config::get()->VIRUSSCAN_HOST && Config::get()->VIRUSSCAN_PORT) {
            $handle = @fsockopen(Config::get()->VIRUSSCAN_HOST, Config::get()->VIRUSSCAN_PORT);
        }

        if ($handle === false) {
            throw new RuntimeException(_('Der Virenscanner ist nicht verfügbar.'));
        }

        return $handle;
    }

    /**
     * Get contents of the file to scan.
     *
     * @param string $path
     * @return resource
     */
    protected function readFile(string $path)
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException(_('Die Datei kann nicht gelesen werden.'));
        }

        return $handle;
    }

    /**
     * Send some content to the virus scanner.
     *
     * @param resource $handle
     * @param string $content
     * @return int
     */
    protected function sendContent($handle, string $content): int
    {
        $written = @fwrite($handle, $content);

        // An error has happened -> throw exception.
        if ($written === false) {
            throw new RuntimeException(_('Fehler bei der Kommunikation mit dem Virenscanner.'));
        // Return written byte count.
        } else {
            return $written;
        }
    }

    /**
     * All file chunks have been sent: we now signal the end of the stream by sending a "0".
     * Afterwarda, the response we got from virus scanner is parsed and (in case something was found)
     * the name of the virus is returned.
     *
     * @param resource $handle
     * @return array
     */
    protected function finalize($handle): array
    {
        // End stream to socket or TCP endpoint.
        $this->sendContent($handle, pack('N', 0));

        // Fetch virus scanner response.
        $response = fgets($handle);
        fclose($handle);

        // Parse response.
        $matches = [];

        // Possible response types.
        $rules = [
            [
                'match' => '/.*: OK$/',
                'status' => self::SCANRESULT_CLEAN
            ],
            [
                'match' => '/.*: (.*) FOUND$/',
                'status' => self::SCANRESULT_INFECTED
            ],
            [
                'match' => '/.*: (.*) ERROR$/',
                'status' => self::SCANRESULT_UNCHECKED
            ],
        ];

        $status = static::SCANRESULT_UNCHECKED;
        $details = _('Die Antwort des Virenscanners wurde nicht erkannt.');

        foreach ($rules as $rule) {
            if (preg_match($rule['match'], $response, $matches)) {
                $status = (int) $rule['status'];

                if ((int) $rule['status'] !== static::SCANRESULT_CLEAN) {
                    $details = $matches[1] ?? _('unbekannt');
                } else {
                    $details = '';
                }
                break;
            }
        }

        return [
            'status' => $status,
            'details' => $details
        ];
    }

}
