<?php

/**
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function config($key, $default = null)
{
    static $config = null;

    $makeValueGetter = function ($key) {
        $path = explode('.', $key);
        $getValue = function ($config, $value) {
            return is_array($config) && array_key_exists($value, $config)
                ? $config[$value]
                : null;
        };
        return function ($config) use ($path, $getValue) {
            return array_reduce($path, $getValue, $config);
        };
    };

    $getConfig = function ($configDir) {
        $absFilePath = function ($file) use ($configDir) {
            return $configDir . DIRECTORY_SEPARATOR . $file;
        };
        $isValidConfigFile = function ($file) {
            return is_file($file) && pathinfo($file, PATHINFO_EXTENSION) == 'php';
        };
        $filesReduce = function ($acc, $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $config = require $file;
            return array_merge($acc, [$filename => $config]);
        };
        $filesList = scandir($configDir);
        $absoluteList = array_map($absFilePath, $filesList);
        $filteredList = array_filter($absoluteList, $isValidConfigFile);
        return array_reduce($filteredList, $filesReduce, []);
    };

    if (is_null($config)) {
        $config = $getConfig(CONFIG_DIR);
    }

    $getter = $makeValueGetter($key);

    return $getter($config) ?: $default;
}

/**
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env($key, $default = null)
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    $value = getenv($key);

    return !is_bool($value) ? $value : $default;
}

/**
 * Hides all secrets from
 */
function secure(array $array): array
{
    $stopWords = ['password', 'key', 'token', 'secret'];
    return array_reduce(array_keys($array), function ($acc, $key) use ($stopWords, $array) {
        $words = explode('_', strtolower($key));
        if (sizeof(array_intersect($words, $stopWords)) > 0) {
            $acc[$key] = '<hidden>';
        } else {
            $acc[$key] = is_array($array[$key]) ? secure($array[$key]) : $array[$key];
        }
        return $acc;
    }, []);
}

/**
 * Maps a file extensions to a mimetype.
 *
 * @param $extension string The file extension.
 *
 * @return string|null
 * @link http://svn.apache.org/repos/asf/httpd/httpd/branches/1.3.x/conf/mime.types
 */
function mimetype_from_extension($extension)
{
    static $mimetypes = [
        '7z' => 'application/x-7z-compressed',
        'aac' => 'audio/x-aac',
        'ai' => 'application/postscript',
        'aif' => 'audio/x-aiff',
        'asc' => 'text/plain',
        'asf' => 'video/x-ms-asf',
        'atom' => 'application/atom+xml',
        'avi' => 'video/x-msvideo',
        'bmp' => 'image/bmp',
        'bz2' => 'application/x-bzip2',
        'cer' => 'application/pkix-cert',
        'crl' => 'application/pkix-crl',
        'crt' => 'application/x-x509-ca-cert',
        'css' => 'text/css',
        'csv' => 'text/csv',
        'cu' => 'application/cu-seeme',
        'deb' => 'application/x-debian-package',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dvi' => 'application/x-dvi',
        'eot' => 'application/vnd.ms-fontobject',
        'eps' => 'application/postscript',
        'epub' => 'application/epub+zip',
        'etx' => 'text/x-setext',
        'flac' => 'audio/flac',
        'flv' => 'video/x-flv',
        'gif' => 'image/gif',
        'gz' => 'application/gzip',
        'htm' => 'text/html',
        'html' => 'text/html',
        'ico' => 'image/x-icon',
        'ics' => 'text/calendar',
        'ini' => 'text/plain',
        'iso' => 'application/x-iso9660-image',
        'jar' => 'application/java-archive',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'text/javascript',
        'json' => 'application/json',
        'latex' => 'application/x-latex',
        'log' => 'text/plain',
        'm4a' => 'audio/mp4',
        'm4v' => 'video/mp4',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mov' => 'video/quicktime',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'mp4a' => 'audio/mp4',
        'mp4v' => 'video/mp4',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpg4' => 'video/mp4',
        'oga' => 'audio/ogg',
        'ogg' => 'audio/ogg',
        'ogv' => 'video/ogg',
        'ogx' => 'application/ogg',
        'pbm' => 'image/x-portable-bitmap',
        'pdf' => 'application/pdf',
        'pgm' => 'image/x-portable-graymap',
        'png' => 'image/png',
        'pnm' => 'image/x-portable-anymap',
        'ppm' => 'image/x-portable-pixmap',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ps' => 'application/postscript',
        'qt' => 'video/quicktime',
        'rar' => 'application/x-rar-compressed',
        'ras' => 'image/x-cmu-raster',
        'rss' => 'application/rss+xml',
        'rtf' => 'application/rtf',
        'sgm' => 'text/sgml',
        'sgml' => 'text/sgml',
        'svg' => 'image/svg+xml',
        'swf' => 'application/x-shockwave-flash',
        'tar' => 'application/x-tar',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'torrent' => 'application/x-bittorrent',
        'ttf' => 'application/x-font-ttf',
        'txt' => 'text/plain',
        'wav' => 'audio/x-wav',
        'webm' => 'video/webm',
        'wma' => 'audio/x-ms-wma',
        'wmv' => 'video/x-ms-wmv',
        'woff' => 'application/x-font-woff',
        'wsdl' => 'application/wsdl+xml',
        'xbm' => 'image/x-xbitmap',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xml' => 'application/xml',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'yaml' => 'text/yaml',
        'yml' => 'text/yaml',
        'zip' => 'application/zip',
    ];

    $extension = strtolower($extension);

    return isset($mimetypes[$extension])
        ? $mimetypes[$extension]
        : null;
}

function array_some(array $array, callable $fn): bool
{
    foreach ($array as $value) {
        if ($fn($value)) {
            return true;
        }
    }
    return false;
}

function array_all(array $array, callable $fn): bool
{
    foreach ($array as $value) {
        if (!$fn($value)) {
            return false;
        }
    }
    return true;
}