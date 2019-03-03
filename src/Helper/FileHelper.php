<?php
// Credits: https://stackoverflow.com/a/32885706/5865844

namespace Xenokore\Utility\Helper;

class FileHelper
{
    /**
     * A list of file extensions and their corresponding mimetypes
     *
     * @var array
     */
    public const MIME_TYPES = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'pdf'   => 'application/pdf',
        'txt'   => 'text/plain',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'exe'   => 'application/octet-stream',
        'zip'   => 'application/zip',
        'doc'   => 'application/msword',
        'xls'   => 'application/vnd.ms-excel',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'gif'   => 'image/gif',
        'png'   => 'image/png',
        'jpeg'  => 'image/jpg',
        'jpg'   => 'image/jpg',
        'php'   => 'text/plain',
        'svg'   => 'image/svg+xml',
        'json'  => 'application/json',
        'ttf'   => 'font/ttf',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'otf'   => 'font/otf',
    ];

    public static function delete(string $file_path): bool
    {
        if (self::isAccessible($file_path)) {
            return @unlink($file_path);
        }

        return false;
    }

    public static function createIfNotExist(string $path): bool
    {
        if (file_exists($path) && is_file($path)) {
            return true;
        }

        file_put_contents($path, '');

        return file_exists($path) && is_file($path);
    }

    public static function isAccessible(string $path, bool $create = false): bool
    {
        if ($create && !self::createIfNotExist($path)) {
            return false;
        }
        
        return file_exists($path) && is_file($path) && is_readable($path);
    }

    public static function getLastModifiedTimestamp(string ...$paths): int
    {
        $timestamp = 0;

        foreach ($paths as $path) {
            if (self::isAccessible($path)) {
                if (@filemtime($path) > $timestamp) {
                    $timestamp = @filemtime($path);
                }
            }
        }

        return $timestamp;
    }

    /**
     * Sanitize a string so it can be used as a filename (Windows & Linux)
     *
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename): string
    {
        return preg_replace("/[^a-zA-Z0-9\._\-\ \+\(\)\[\]]/", "", $filename);
    }

    /**
     * Stream a file directly to the browser. If something goes wrong, an exception will be thrown.
     * TODO: throw a 'FileNotFoundException' instead of an 'Exception'
     *
     * @param string $file_path          Path of the file to serve
     * @param string $file_name          Name to serve the file as
     * @param string $mime_type          Mimetype to set, can be left empty to decide the mimetype by file extension
     * @param int $buffer_chunk_size     Buffer chunk size (default: 4096)
     * @param int $time_to_cache         Time to cache the file in seconds
     * @throws \Exception
     * @return void
     */
    public static function outputFileToBrowser(string $file_path, string $file_name, ?string $mime_type = null, int $buffer_chunk_size = 4096, int $time_to_cache = 2592000): void
    {
        if (!is_readable($file_path)) {
            throw new \Exception("{$file_path} is not readable");
            //throw new FileNotFoundException("{$file_path} is not readable");
        }
 
        $file_size               = @filesize($file_path);
        $file_last_modified_time = @filemtime($file_path);
        $file_name               = rawurldecode($file_name);
        $file_etag               = md5($file_name . $file_size . $file_last_modified_time);

        // Set mimetype or grab it from file extension
        if (empty($mime_type)) {
            $file_extension = strtolower(StringHelper::subtract(strrchr($file_path, "."), 1));
            if (array_key_exists($file_extension, self::MIME_TYPES)) {
                $mime_type = self::MIME_TYPES[$file_extension];
            } else {
                $mime_type = "application/force-download";
            }
        }

        // Turn off output buffering to decrease CPU usage
        @ob_end_clean();

        // Required for IE, otherwise Content-Disposition may be ignored
        if (ini_get('zlib.output_compression')) {
            @ini_set('zlib.output_compression', 'Off');
        }
 
        // Set mimetype
        header("Content-Type: {$mime_type}");
        
        // Force download
        if ($mime_type == 'application/force-download') {
            header("Content-Disposition: attachment; filename=\"{$file_name}\"");
            header("Content-Transfer-Encoding: binary");
            
            // Disable caching
            header("Cache-control: private");
            header('Pragma: private');
            header("Expires: Mon, 23 Jul 1997 05:00:00 GMT");
        } else {
            // // ETag support
            // $current_time_string = gmdate('D, d M Y H:i:s', $file_last_modified_time) . ' GMT';

            // $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
            // $if_none_match     = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH'], '"\'') : false;
            // if ((($if_none_match && $if_none_match == $file_etag) || !$if_none_match) && ($if_modified_since && $if_modified_since == $current_time_string)) {
            //     header('HTTP/1.1 304 Not Modified');
            //     die();
            // }

            // header("Last-Modified: $current_time_string");
            // header("ETag: \"{$file_etag}\"");

            // Allow client-side caching
            $cache_time_string = gmdate('D, d M Y H:i:s', time() + $time_to_cache) . ' GMT';
            header("Expires: $cache_time_string");
            header("Pragma: cache");
            header("Cache-Control: max-age=$time_to_cache");
        }
        
        // Allow download resuming
        header('Accept-Ranges: bytes');

        // Handle download resuming
        if (isset($_SERVER['HTTP_RANGE'])) {
            // Get byte range
            list($a, $range)         = explode("=", $_SERVER['HTTP_RANGE'], 2);
            list($range)             = explode(",", $range, 2);
            list($range, $range_end) = explode("-", $range);

            $range = intval($range);

            if (!$range_end) { // Download rest of file
                $range_end = $file_size - 1;
            } else {
                $range_end = intval($range_end);
            }
 
            $output_size = $range_end - $range + 1;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: {$output_size}");
            header("Content-Range: bytes {$range}-{$range_end}/{$file_size}");
        } else {
            $output_size = $file_size;
            header("Content-Length: {$file_size}");
        }
 
        // Open file handle
        if ($file_handle = fopen($file_path, 'r')) {
            // Set pointer if partial download
            if (isset($_SERVER['HTTP_RANGE'])) {
                fseek($file_handle, $range);
            }
 
            // Loop through file and output to browser
            $bytes_sent = 0;
            while (!feof($file_handle) && (!connection_aborted()) && ($bytes_sent < $output_size)) {
                $buffer = fread($file_handle, $buffer_chunk_size);

                echo $buffer;
                flush();

                $bytes_sent += StringHelper::length($buffer);
            }

            // Close handle
            fclose($file_handle);
        } else {
            throw new \Exception('unable to fopen() file');
            // throw new FailedToOpenFileException('unable to fopen() file');
        }
    }
}
