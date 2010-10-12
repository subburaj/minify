<?php

class Minify_Server {

    public $encodeLevel = 9;
    public $isPublic = true;
    public $charset = 'utf-8';
    public $maxAge = 1800;
    public $alwaysServe = false;

    /**
     *
     * @var Minify_Cache_Abstract
     */
    protected $_cache = null;

    /**
     *
     * @var Minify_Env
     */
    protected $_env = null;

    public function __construct(Minify_Env $env, Minify_Cache_Abstract $cache)
    {
        $this->_env = $env;
        $this->_cache = $cache;
    }

    public function servePrecached($cacheId, $lastModified, Minify_Env $env, $headers = array())
    {
        if ($env->encoding === 'gzip') {
            $cacheId .= '.gz';
        }
        if (! $this->_cache->isValid($cacheId, 0)) {
            return false;
        }
    }


    public function serve(Minify_SourceCollection_Combiner $combiner, Minify_Logger $logger = null)
    {
        // determine encoding
        if ($this->allowEncoding) {
            $sendVary = true;
            // sniff request header
            require_once 'HTTP/Encoder.php';
            // depending on what the client accepts, $contentEncoding may be
            // 'x-gzip' while our internal encodeMethod is 'gzip'. Calling
            // getAcceptedEncoding(false, false) leaves out compress and deflate as options.
            $encoding = self::sniffEncoding();
            $sendVary = ! HTTP_Encoder::isBuggyIe();
        } else {
            self::$_options['encodeMethod'] = ''; // identity (no encoding)
        }

        $lastModified = $combiner->getLastModified();

        // check client cache
        require_once 'HTTP/ConditionalGet.php';
        $cgOptions = array(
            'lastModifiedTime' => $lastModified
            ,'isPublic' => $this->isPublic
            ,'encoding' => $this->encoding
        );
        if ($this->maxAge > 0) {
            $cgOptions['maxAge'] = $this->maxAge;
        } elseif ($this->alwaysServe) {
            $cgOptions['invalidate'] = true;
        }
        $cg = new HTTP_ConditionalGet($cgOptions);
        if ($cg->cacheIsValid) {
            // client's cache is valid
            $cg->sendHeaders();
            return array(
                'statusCode' => 304
                ,'headers' => $cg->getHeaders()
            );
        } else {
            // client will need output
            $headers = $cg->getHeaders();
            unset($cg);
        }

        $contentType = $combiner->collection->getContentType();
        $cacheId = $combiner->getCacheId();

        $fullCacheId = ($this->encoding)
            ? $cacheId . '.gz'
            : $cacheId;
        // check cache for valid entry
        $cacheIsReady = $this->_cache->isValid($fullCacheId, $lastModified);
        if ($cacheIsReady) {
            $cacheContentLength = $this->_cache->getSize($fullCacheId);
        } else {
            // generate & cache content
            try {
                $content = $combiner->getContent();
            } catch (Exception $e) {
                if ($logger) {
                    $logger->log($e->getMessage());
                }
                self::_errorExit(self::$_options['errorHeader'], self::URL_DEBUG);
            }
            $this->_cache->store($cacheId, $content);
            if (function_exists('gzencode')) {
                $this->_cache->store($cacheId . '.gz', gzencode($content, $this->encodeLevel));
            }
        }
        if (! $cacheIsReady && $this->encoding) {
            // still need to encode
            $content = gzencode($content, $this->encodeLevel);
        }

        // add headers
        $headers['Content-Length'] = $cacheIsReady
            ? $cacheContentLength
            : ((function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2))
                ? mb_strlen($content, '8bit')
                : strlen($content)
            );
        $headers['Content-Type'] = $this->charset
            ? $contentType . '; charset=' . $this->charset
            : $contentType;
        if ($this->encoding !== '') {
            $headers['Content-Encoding'] = $this->encoding;
        }
        if (self::$_options['encodeOutput'] && $sendVary) {
            $headers['Vary'] = 'Accept-Encoding';
        }

        if (! self::$_options['quiet']) {
            // output headers & content
            foreach ($headers as $name => $val) {
                header($name . ': ' . $val);
            }
            if ($cacheIsReady) {
                self::$_cache->display($fullCacheId);
            } else {
                echo $content;
            }
        } else {
            return array(
                'success' => true
                ,'statusCode' => 200
                ,'content' => $cacheIsReady
                    ? self::$_cache->fetch($fullCacheId)
                    : $content
                ,'headers' => $headers
            );
        }
    }

}