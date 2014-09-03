<?php
    function isAke($tab, $key, $default = null)
    {
        if (is_array($tab)) {
            return array_key_exists($key, $tab) ? $tab[$key] : $default;
        }
        return $default;
    }

    function render($args = array())
    {
        header('content-type: application/json; charset=utf-8');
        die(json_encode($args));
    }

    function forbidden($reason = 'NA')
    {
        $infos = array(
            'status'    => 403,
            'message'   => "Forbidden $reason"
        );
        render($infos);
    }

    function success($message)
    {
        $infos = array(
            'status'    => 200,
            'message'   => $message
        );
        render($infos);
    }

    if (count($_REQUEST)) {
        $action = isAke($_REQUEST, 'action');
        if(strlen($action)) {
            $bucket = new Bucket($action);
        } else {
            forbidden('no action');
        }
    } else {
        forbidden('no request');
    }

    class Bucket
    {
        private $args;
        private $dir;
        private $bucket;

        public function __construct($action)
        {
            if (!method_exists($this, $action)) {
                forbidden('unknow action ' . $action);
            }
            $this->args = $_REQUEST;
            $bucket = isAke($this->args, 'bucket');
            if (!strlen($bucket)) {
                forbidden('no bucket');
            }
            $dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'data';
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $this->dir = $dir . DIRECTORY_SEPARATOR . $bucket;
            if (!is_dir($this->dir)) {
                mkdir($this->dir);
            }
            $this->dir .= '/';
            $this->bucket = $bucket;
            $this->check();
            $this->$action();
        }

        public function __get($key)
        {
            if (isset($this->$key)) {
                return $this->$key;
            }
            return isAke($this->args, $key);
        }

        public function __isset($key)
        {
            if (isset($this->$key)) {
                return true;
            }
            $val = isAke($this->args, $key);
            return strlen($val) > 0 ; true : false;
        }

        public function __set($key, $value)
        {
            $this->args[$key] = $value;
            return $this;
        }

        private function check()
        {
            $expires = glob($this->dir . "expire::*");
            if (count($expires)) {
                foreach ($expires as $expire) {
                    $tab = explode("::", $expire);
                    $time = end($tab);
                    if ($time > 0 && $time < time()) {
                        unlink($expire);
                    }
                }
            }
        }

        private function upload()
        {
            $data = isAke($this->args, 'data');
            if (!strlen($data)) {
                forbidden('no data');
            }
            $name = isAke($this->args, 'name');
            if (!strlen($name)) {
                forbidden('no name');
            }
            if (!is_dir($this->dir . 'upload')) {
                mkdir($this->dir . 'upload');
            }
            $fileData = $this->dir . 'upload/' . $name;
            $url = 'http://' . $_SERVER['SERVER_NAME'] . '/bucket/data/' . $this->bucket . '/upload/' . $name;
            file_put_contents($fileData, $data);
            success($url);
        }

        private function set()
        {
            $key = isAke($this->args, 'key');
            if (!strlen($key)) {
                forbidden('no key');
            }

            $value = isAke($this->args, 'value');
            if (!strlen($value)) {
                forbidden('no value');
            }

            $expire = isAke($this->args, 'expire');
            if (!strlen($expire)) {
                forbidden('no expire');
            }

            $fileData       = $this->dir . $key;
            $fileExpire     = $this->dir . "expire::$key::$expire";
            if (file_exists($fileExpire)) {
                unlink($fileExpire);
            } else {
                $expires = glob($this->dir . "expire::$key::*");
                if (count($expires)) {
                    foreach ($expires as $expire) {
                        unlink($expire);
                    }
                }
            }
            if (file_exists($fileData)) {
                unlink($fileData);
            }
            file_put_contents($fileData, $value);
            file_put_contents($fileExpire, "1");
            success(true);
        }

        private function del()
        {
            $key = isAke($this->args, 'key');
            if (!strlen($key)) {
                forbidden('no key');
            }
            $data = false;
            if (file_exists($this->dir . $key)) {
                unlink($this->dir . $key);
                $data = true;
            }
            success($data);
        }

        private function get()
        {
            $key = isAke($this->args, 'key');
            if (!strlen($key)) {
                forbidden('no key');
            }
            $data = null;
            $exists = file_exists($this->dir . $key);
            if (true === $exists) {
                $data = file_get_contents($this->dir . $key);
            }
            success($data);
        }

        private function all()
        {
            $pattern = isAke($this->args, 'pattern');
            if (!strlen($pattern)) {
                forbidden('no pattern');
            }
            $keys = glob($this->dir . $pattern);
            $collection = array();
            if (count($keys)) {
                foreach ($keys as $key) {
                    $data = file_get_contents($key);
                    $key = str_replace($this->dir, '', $key);
                    $collection[$key] = $data;
                }
            }
            success($collection);
        }

        private function keys()
        {
            $pattern = isAke($this->args, 'pattern');
            if (!strlen($pattern)) {
                forbidden('no pattern');
            }
            $keys = glob($this->dir . $pattern);
            $collection = array();
            if (count($keys)) {
                foreach ($keys as $key) {
                    $key = str_replace($this->dir, '', $key);
                    array_push($collection, $key);
                }
            }
            success($collection);
        }
    }
