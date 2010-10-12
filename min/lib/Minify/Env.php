<?php

class Minify_Env {

    public $docRoot = null;
    public $requestUri = null;
    public $queryString = null;
    public $encoding = null;
    public $isDebugMode = null;
    public $isBuggyIe = null;
    public $sendVaryHeader = null;

    public function __construct($options = array())
    {
        $options = array_merge(array(
            'server' => $_SERVER,
            'get' => $_GET,
            'cookie' => $_COOKIE,
            'allowEncoding' => true,
            'allowDebug' => true,
            'allowIe6Encoding' => true,
        ), $options);
        
        $this->_server = $options['server'];
        $this->_get = $options['get'];
        $this->_cookie = $options['cookie'];
        $this->docRoot = $this->_server['DOCUMENT_ROOT'];
        $this->_setRequestUri();
        $this->queryString = $this->server('QUERY_STRING'); // may be empty, use method
        
        if (null === $this->isDebugMode) {
            $this->isDebugMode = false;
            if ($options['allowDebug']) {
                $minDebug = $this->cookie('minDebug');
                if (null !== $minDebug) {
                    foreach (preg_split('/\\s+/', $minDebug) as $debugUri) {
                        if (false !== strpos($this->requestUri, $debugUri)) {
                            $this->isDebugMode = true;
                            break;
                        }
                    }
                }
                // allow GET to override
                if (isset($this->_get['debug'])) {
                    $this->isDebugMode = true;
                }
            }
        }

        $this->isBuggyIe = $this->_isBuggyIe($options['allowIe6Encoding']);
        $this->sendVaryHeader = ! $this->isBuggyIe;
        
        if (null === $this->encoding) {
            $this->encoding = '';
            if ($options['allowEncoding']) {
                $ae = $this->server('HTTP_ACCEPT_ENCODING');
                if ($ae
                    && ! $this->isBuggyIe
                    && ! $this->isDebugMode
                    && (0 === strpos($ae, 'gzip,')              // most browsers
                        || 0 === strpos($ae, 'deflate, gzip,')) // opera
                ) {
                    $this->encoding = 'gzip';
                }
            }
        }
    }

    public function server($key)
    {
        return isset($this->_server[$key])
            ? $this->_server[$key]
            : null;
    }
    
    public function cookie($key)
    {
        return isset($this->_cookie[$key])
            ? $this->_cookie[$key]
            : null;
    }

    public function get($key)
    {
        return isset($this->_get[$key])
            ? $this->_get[$key]
            : null;
    }

    protected $_server = null;
    protected $_get = null;
    protected $_cookie = null;

    /**
     * Is the browser an IE version earlier than 6 SP2?
     *
     * @return bool
     */
    protected function _isBuggyIe($allowIe6Encoding)
    {
        $ua = $this->server('HTTP_USER_AGENT');
        // quick escape for non-IEs
        if (0 !== strpos($ua, 'Mozilla/4.0 (compatible; MSIE ')
            || false !== strpos($ua, 'Opera')) {
            return false;
        }
        // no regex = faaast
        $version = (float)substr($ua, 30);
        return $allowIe6Encoding
            ? ($version < 6 || ($version == 6 && false === strpos($ua, 'SV1')))
            : ($version < 7);
    }

    protected function _setRequestUri()
    {
        $this->requestUri = $this->_server['REQUEST_URI'];
    }
}