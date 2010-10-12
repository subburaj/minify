<?php

class Minify_Cache_Abstract {

    /**
     * @var Minify_Logger
     */
    public $logger = null;

    /**
     * Send message to the logger
     * @param string $msg
     * @return null
     */
    protected function _log($msg)
    {
        if ($this->logger) {
            $this->logger->log($msg);
        }
    }

    abstract function store($id, $data);

    abstract function getSize($id);

    abstract function isValid($id, $srcMtime);

    abstract function display($id);

    abstract function fetch($id);
}