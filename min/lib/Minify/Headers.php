<?php

class Minify_Headers {

    public $headers = array();
    public $responseCode = 200;

    public function send()
    {
        if (headers_sent()) {
            return false;
        }
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        switch ($this->responseCode) {
            case 304: header('HTTP/1.0 304 Not Modified', true, 304);
                break;
            case 404: header('HTTP/1.0 404 Not Found', true, 404);
                break;
            case 500: header('HTTP/1.0 500 Internal Server Error', true, 500);
                break;
        }
        return true;
    }

    public function setEncoding(Minify_Env $env)
    {
        if ($env->encoding) {
            $this->headers['Content-Encoding'] = 'gzip';
        }
        if ($env->sendVaryHeader) {
            $this->headers['Vary'] = 'Accept-Encoding';
        }
    }

    public function setLastModified($time)
    {
        $this->headers['Last-Modified'] = self::gmtDate($time);
    }

    /**
     * Get a GMT formatted date for use in HTTP headers
     *
     * <code>
     * header('Expires: ' . HTTP_ConditionalGet::gmtdate($time));
     * </code>
     *
     * @param int $time unix timestamp
     *
     * @return string
     */
    public static function gmtDate($time)
    {
        return gmdate('D, d M Y H:i:s \G\M\T', $time);
    }
}