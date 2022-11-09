<?php

namespace Xenokore\Utility;

class ImageHelper {

    /**
     * Check if a 'gif' image is animated
     * @param string $filepath
     * @return boolean
     * @link https://stackoverflow.com/a/47907134
     */
    public static function isAnimatedGif(string $filepath): bool
    {

        if(!($fh = @fopen($filepath, 'rb'))){
            return false;
        }

        $count = 0;
        // An animated gif contains multiple "frames", with each frame having a header made up of:
        // - a static 4-byte sequence (\x00\x21\xF9\x04)
        // - 4 variable bytes
        // - a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)

        // We read through the file until we reach the end of the file, or until we've found at least 2 frame headers
        $chunk = false;
        while(!feof($fh) && $count < 2) {
            // Add the last 20 characters from the previous string, to make sure the searched pattern is not split.
            $chunk = ($chunk ? substr($chunk, -20) : "") . fread($fh, 1024 * 100); // Read 100 KiB at a time
            $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
        }

        fclose($fh);
        return $count > 1;
    }

    /**
     * Check if a 'webp' image is animated
     * @param string $filepath
     * @return boolean
     */
    public static function isAnimatedWebp(string $filepath): bool
    {
        $result = false;
        $fh = fopen($filepath, "rb");
        fseek($fh, 12);
        if(fread($fh, 4) === 'VP8X'){
          fseek($fh, 20);
          $myByte = fread($fh, 1);
          $result = ((ord($myByte) >> 1) & 1)?true:false;
        }
        fclose($fh);
        return $result;
      }

    /**
     * Remove EXIF data from an image file.
     * @param string $input  Filepath of image
     * @param string $output Output image
     * @return void
     * @link https://stackoverflow.com/a/47152957
     */
    public static function removeEXIF(string $input, string $output): void
    {
        $buffer_len = 4096;
        $fd_in = fopen($input, 'rb');
        $fd_out = fopen($output, 'wb');
        while (($buffer = fread($fd_in, $buffer_len)))
        {
            //  \xFF\xE1\xHH\xLLExif\x00\x00 - Exif
            //  \xFF\xE1\xHH\xLLhttp://      - XMP
            //  \xFF\xE2\xHH\xLLICC_PROFILE  - ICC
            //  \xFF\xED\xHH\xLLPhotoshop    - PH
            while (preg_match('/\xFF[\xE1\xE2\xED\xEE](.)(.)(exif|photoshop|http:|icc_profile|adobe)/si', $buffer, $match, PREG_OFFSET_CAPTURE))
            {
                $len = ord($match[1][0]) * 256 + ord($match[2][0]);
                fwrite($fd_out, substr($buffer, 0, $match[0][1]));
                $file_pos = $match[0][1] + 2 + $len - strlen($buffer);
                fseek($fd_in, $file_pos, SEEK_CUR);
                $buffer = fread($fd_in, $buffer_len);
            }
            fwrite($fd_out, $buffer, strlen($buffer));
        }
        fclose($fd_out);
        fclose($fd_in);
    }

    /**
     * Remove EXIF from a JPEG file.
     * @param string $old Path to original jpeg file (input).
     * @param string $new Path to new jpeg file (output).
     * @link https://stackoverflow.com/a/38862429
     */
    function removeEXIF2($old, $new)
    {
        // Open the input file for binary reading
        $f1 = fopen($old, 'rb');
        // Open the output file for binary writing
        $f2 = fopen($new, 'wb');

        // Find EXIF marker
        while (($s = fread($f1, 2))) {
            $word = unpack('ni', $s)['i'];
            if ($word == 0xFFE1) {
                // Read length (includes the word used for the length)
                $s = fread($f1, 2);
                $len = unpack('ni', $s)['i'];
                // Skip the EXIF info
                fread($f1, $len - 2);
                break;
            } else {
                fwrite($f2, $s, 2);
            }
        }

        // Write the rest of the file
        while (($s = fread($f1, 4096))) {
            fwrite($f2, $s, strlen($s));
        }

        fclose($f1);
        fclose($f2);
    }
}
