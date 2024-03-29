<?php
    /**
     * File class
     * @author      Gerald Plusquellec
     */
    namespace Thin;
    class File
    {
        public static function create($file, $content = null)
        {
            static::delete($file);
            $create = @touch($file);
            if (null !== $content) {
                $fp = fopen($file, 'a');
                fwrite($fp, $content);
                fclose($fp);
            }
            umask(0000);
            chmod($file, 0777);
            return $create;
        }

        public static function append($file, $data)
        {
            $append = file_put_contents($file, $data, LOCK_EX | FILE_APPEND);
            umask(0000);
            chmod($file, 0777);
            return $append;
        }

        public static function exists($file)
        {
            return file_exists($file);
        }

        public static function get($file, $default = null)
        {
            return (static::exists($file)) ? file_get_contents($file) : Utils::value($default);
        }

        public static function put($file, $data, $chmod = 0777)
        {
            $put = file_put_contents($file, $data, LOCK_EX);
            umask(0000);
            chmod($file, 0777);
            return $put;
        }

        public static function delete($file)
        {
            if (is_dir($file)) {
                return static::rmdir($file);
            }
            if (true === static::exists($file)) {
                return @unlink($file);
            }
        }

        public static function move($file, $target)
        {
            return rename($file, $target);
        }

        public static function copy($file, $target)
        {
            $copy = copy($file, $target);
            umask(0000);
            chmod($target, 0777);
            return $copy;
        }

        public static function extension($file)
        {
            return pathinfo($file, PATHINFO_EXTENSION);
        }

        public static function basename($path)
        {
            return pathinfo($path, PATHINFO_BASENAME);
        }

        public static function type($file)
        {
            return filetype($file);
        }

        public static function size($path)
        {
            return filesize($path);
        }

        public static function date($file, $format = "YmDHis")
        {
            return date($format, filemtime($file));
        }

        public static function modified($file)
        {
            return filemtime($file);
        }

        public static function age($file)
        {
            return filemtime($file);
        }

        public static function mkdir($path, $chmod = 0777)
        {
            umask(0000);
            return (!is_dir($path)) ? mkdir($path, $chmod, true) : true;
        }

        public static function mvdir($source, $destination, $options = \FilesystemIterator::SKIP_DOTS)
        {
            return static::cpdir($source, $destination, true, $options);
        }

        public static function cpdir($source, $destination, $delete = false, $options = \FilesystemIterator::SKIP_DOTS)
        {
            umask(0000);
            if (!is_dir($source)) {
                return false;
            }

            if (!is_dir($destination)) {
                static::mkdir($destination, 0777);
            }

            $items = new \FilesystemIterator($source, $options);

            foreach ($items as $item) {
                $location = $destination . DS . $item->getBasename();

                if ($item->isDir()) {
                    $path = $item->getRealPath();
                    if (!static::cpdir($path, $location, $delete, $options)) {
                        return false;
                    }
                    if (true === $delete) {
                        @rmdir($item->getRealPath());
                    }
                } else  {
                    if(!copy($item->getRealPath(), $location)) {
                        return false;
                    }
                    if (true === $delete) {
                        @unlink($item->getRealPath());
                    }
                }
            }

            unset($items);
            if ($delete) {
                @rmdir($source);
            }

            return true;
        }

        public static function chmodDir($directory, $chmod = 0777)
        {
            umask(0000);
            if (!is_dir($directory)) {
                return false;
            }

            exec("chmod $chmod $directory");

            $items = new \FilesystemIterator($directory);

            foreach ($items as $item) {
                if (true === $item->isDir()) {
                    static::chmodDir($item->getRealPath(), $chmod);
                } else {
                    @chmod($item->getRealPath(), $chmod);
                }
            }
        }

        public static function rmdir($directory, $preserve = false)
        {
            umask(0000);
            if (!is_dir($directory)) {
                return false;
            }

            $items = new \FilesystemIterator($directory);

            foreach ($items as $item) {
                if (true === $item->isDir()) {
                    static::rmdir($item->getRealPath());
                } else {
                    @unlink($item->getRealPath());
                }
            }

            unset($items);
            if (false === $preserve) {
                @rmdir($directory);
            }
            return true;
        }

        public static function cleandir($directory)
        {
            return static::rmdir($directory, true);
        }

        public static function latest($directory, $options = \FilesystemIterator::SKIP_DOTS)
        {
            $latest = null;
            $time   = 0;
            $items  = new \FilesystemIterator($directory, $options);

            foreach ($items as $item) {
                if ($item->getMTime() > $time) {
                    $latest = $item;
                    $time = $item->getMTime();
                }
            }

            return $latest;
        }


        public static function isFileComplete($path, $waitTime = 2)
        {
            // récupération de la taille du fichier
            $sizeBefore = static::size($path);

            // pause
            sleep($waitTime);

            // purge du cache mémoire PHP (car sinon filesize retourne la même valeur qu'à l'appel précédent)
            clearstatcache();

            // récupération de la taille du fichier après
            $size = static::size($path);

            return ($sizeBefore === $size);

        }

        /**
         * Permet de lire le contenu d'un répertoire lorsqu'on n'a pas accès à la SPL FileSystemIterator (version PHP < 5.3)
         *
         * @param string $path le chemin du répertoire
         * @return array tableau contenant tout le contenu du répertoire
         */
        public static function readdir($path)
        {
            // initialisation variable de retour
            $ret = array();

            // on gère par sécurité la fin du path pour ajouter ou pas le /
            if('/' != substr($path, -1)) {
                $path .= '/';
            }

            // on vérifie que $path est bien un répertoire
            if(is_dir($path)) {
                // ouverture du répertoire
                if($dir = opendir($path)) {
                    // on parcours le répertoire
                    while(false !== ($dirElt = readdir($dir))) {
                        if ($dirElt != '.' && $dirElt != '..') {
                            if (!is_dir($path . $dirElt)) {
                                $ret[] = $path . $dirElt;
                            } else {
                                $ret[] = static::readdir($path . $dirElt);
                            }
                        }
                    }

                    // fermeture du répertoire
                    closedir($dir);
                } else {
                    throw new Exception('error while opening ' . $path);
                }
            }else{
                throw new Exception($path . ' is not a directory');
            }

            return Arrays::flatten($ret);
        }

        public static function mime($extension, $default = 'application/octet-stream')
        {
            Config::load('mimes');
            $mimes = (null !== config::get('mimes')) ? config::get('mimes') : array();

            if (!Arrays::exists($extension, $mimes)) {
                return $default;
            }

            return (Arrays::isArray($mimes[$extension])) ? Arrays::first($mimes[$extension]) : $mimes[$extension];
        }

        public static function download($fileLocation, $maxSpeed = 5120)
        {
            if (connection_status() != 0) return false;

            $tab = explode(DS, $fileLocation);
            $fileName = Arrays::last($tab);

            $extension = Inflector::lower(substr($fileName, strrpos($fileName, '.') + 1));

            /* List of File Types */
            $fileTypes['swf'] = 'application/x-shockwave-flash';
            $fileTypes['pdf'] = 'application/pdf';
            $fileTypes['exe'] = 'application/octet-stream';
            $fileTypes['zip'] = 'application/zip';
            $fileTypes['doc'] = 'application/msword';
            $fileTypes['docx'] = 'application/msword';
            $fileTypes['xls'] = 'application/vnd.ms-excel';
            $fileTypes['xlsx'] = 'application/vnd.ms-excel';
            $fileTypes['ppt'] = 'application/vnd.ms-powerpoint';
            $fileTypes['pptx'] = 'application/vnd.ms-powerpoint';
            $fileTypes['gif'] = 'image/gif';
            $fileTypes['png'] = 'image/png';
            $fileTypes['jpeg'] = 'image/jpg';
            $fileTypes['bmp'] = 'image/bmp';
            $fileTypes['jpg'] = 'image/jpg';
            $fileTypes['rar'] = 'application/rar';
            $fileTypes['ace'] = 'application/ace';

            $fileTypes['ra'] = 'audio/x-pn-realaudio';
            $fileTypes['ram'] = 'audio/x-pn-realaudio';
            $fileTypes['ogg'] = 'audio/x-pn-realaudio';

            $fileTypes['wav'] = 'video/x-msvideo';
            $fileTypes['wmv'] = 'video/x-msvideo';
            $fileTypes['avi'] = 'video/x-msvideo';
            $fileTypes['asf'] = 'video/x-msvideo';
            $fileTypes['divx'] = 'video/x-msvideo';

            $fileTypes['mp3'] = 'audio/mpeg';
            $fileTypes['mp4'] = 'audio/mpeg';
            $fileTypes['mpeg'] = 'video/mpeg';
            $fileTypes['mpg'] = 'video/mpeg';
            $fileTypes['mpe'] = 'video/mpeg';
            $fileTypes['mov'] = 'video/quicktime';
            $fileTypes['swf'] = 'video/quicktime';
            $fileTypes['3gp'] = 'video/quicktime';
            $fileTypes['m4a'] = 'video/quicktime';
            $fileTypes['aac'] = 'video/quicktime';
            $fileTypes['m3u'] = 'video/quicktime';

            $contentType = isAke($fileTypes, $extension, 'application/octet-stream');

            header("Cache-Control: public");
            header("Content-Transfer-Encoding: binary\n");
            header("Content-Type: $contentType");

            $contentDisposition = 'attachment';

            if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
                $fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
                header("Content-Disposition: $contentDisposition;filename=\"$fileName\"");
            } else {
                header("Content-Disposition: $contentDisposition;filename=\"$fileName\"");
            }

            header("Accept-Ranges: bytes");
            $range = 0;
            $size = filesize($fileLocation);
            $range = isAke($_SERVER, 'HTTP_RANGE', null);

            if (!is_null($range)) {
                list($a, $range) = explode("=", $range);
                $range = repl($range, "-", $range);
                $size2 = $size - 1;
                $new_length = $size - $range;
                header("HTTP/1.1 206 Partial Content");
                header("Content-Length: $new_length");
                header('Content-Range: bytes ' . $range . $size2 . '/' . $size);
            } else {
                $size2 = $size - 1;
                header("Content-Range: bytes 0-$size2/$size");
                header("Content-Length: " . $size);
            }

            if ($size < 1) {
                die('Zero byte file! Aborting download');
            }

            $fp = fopen($fileLocation, "rb");

            fseek($fp, $range);

            while (!feof($fp) && (connection_status() == 0)) {
                set_time_limit(0);
                print (fread($fp, 1024 * $maxSpeed));
                flush();
                ob_flush();
                sleep(1);
            }
            fclose($fp);

            exit;

            return ((connection_status() == 0) && !connection_aborted());
        }
    }
