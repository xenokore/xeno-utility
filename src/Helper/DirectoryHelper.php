<?php

namespace Xenokore\Utility\Helper;

use Xenokore\Utility\Exception\DirectoryNotAccessibleException;

class DirectoryHelper
{
    public static function delete(string $path): bool
    {
        if (!self::isAccessible($path)) {
            throw new DirectoryNotAccessibleException("$path is not an accessible directory");
        }

        if (StringHelper::subtract($path, StringHelper::length($path) - 1, 1) !== '/') {
            $path .= '/';
        }

        foreach (glob($path . '*', GLOB_MARK) as $file) {
            if (is_dir($file)) {
                self::delete($file);
            } else {
                FileHelper::delete($file);
            }
        }
        
        return rmdir($path);
    }
    
    public static function clear(string $path): bool
    {
        if (self::delete($path)) {
            return self::isAccessible($path, true);
        }

        return false;
    }

    public static function createIfNotExist(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }

        @mkdir($path, 0777, true);

        return is_dir($path);
    }

    public static function isAccessible(string $path, bool $create = false): bool
    {
        if ($create && !self::createIfNotExist($path)) {
            return false;
        }
        
        return is_dir($path) && is_readable($path);
    }

    public static function getLastModifiedTimestamp(string ...$paths): int
    {
        $timestamp = 0;

        foreach ($paths as $path) {
            if (self::isAccessible($path)) {
                if (@filemtime($path) > $timestamp) {
                    $timestamp = @filemtime($path);
                }
                try {
                    foreach (new \DirectoryIterator($path) as $file) {
                        if (!$file->isDot()) {
                            if ($file->isDir()) {
                                if (($x = self::getLastModifiedTimestamp($file->getPath() . '/' . $file)) > $timestamp) {
                                    $timestamp = $x;
                                }
                            } elseif (($x = $file->getMTime()) > $timestamp) {
                                $timestamp = $x;
                            }
                        }
                    }
                } catch (\Exception $ex) {
                }
            }
        }

        return $timestamp;
    }

    public static function tree(string $path, array $exclude_files = []): array
    {
        $files = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file_info) {
            if ($file_info->isFile() || $file_info->isDir()) {
                if (!$exclude_files || !in_array(basename($file_info), $exclude_files)) {
                    $files[] = $file_info->getRealPath();
                }
            }
        }
        return $files;
    }
}
