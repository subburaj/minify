<?php
/**
 * Class Minify_Source_File
 * @package Minify
 */

/**
 * A file to be minified by Minify.
 *
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 */
class Minify_Source_File implements Minify_Source_Interface {

    public function getProcessors()
    {
        return $this->_processors;
    }

    public function addProcessor(Minify_Processor_Interface $processor) {
        $this->_processors[] = $processor;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getLastModified()
    {
        return $this->_lastModified;
    }

    /**
     * @return string content of this source
     */
    public function getContent()
    {
        $content = file_get_contents($this->_filepath);
        // remove UTF-8 BOM if present
        return ("\xef\xbb\xbf" === substr($content, 0, 3))
            ? substr($content, 3)
            : $content;
    }

    public function getContentType()
    {
        return $this->_contentType;
    }

    public function getSettings()
    {
        return $this->_settings;
    }

    public function setSetting($key, $value)
    {
        $this->_settings[$key] = $value;
    }

    /**
     * Create a Minify_Source
     *
     * In the $spec array(), you must provide a 'filepath' to an existing
     * file.
     *
     * @param array $spec options
     */
    public function __construct($spec)
    {
        if (! empty($spec['filepath'])) {
            $this->_filepath = $spec['filepath'];
        } else {
            throw new Exception('filename not specified');
        }
        if (! empty($spec['id'])) {
            $this->_id = $spec['filepath'];
        }
        if (! empty($spec['contentType'])) {
            $this->_contentType = $spec['contentType'];
        } else {
            $segments = explode('.', $spec['filepath']);
            $ext = strtolower(array_pop($segments));
            switch ($ext) {
                case 'js'   : $this->_contentType = 'application/x-javascript';
                              break;
                case 'css'  : $this->_contentType = 'text/css';
                              break;
                case 'htm'  : // fallthrough
                case 'html' : $this->_contentType = 'text/html';
                              break;
            }
        }
        if (! empty($spec['lastModified'])) {
            $this->_lastModified = $spec['lastModified'];
        } else {
            $this->_lastModified = filemtime($spec['filepath']);
            if (! empty($spec['filemtimeOffset'])) {
                $this->_lastModified += $spec['filemtimeOffset'];
            }
        }
        if (! empty($spec['processors'])) {
            foreach ((array) $spec['processors'] as $processor) {
                $this->addProcessor($processor);
            }
        }
    }

    protected $_lastModified = null;

    /**
     * @var string unique id for this source
     */
    protected $_id = null;

    /**
     * @var string HTTP Content Type (Minify expects one of the constants Minify::TYPE_*)
     */
    protected $_contentType = 'text/plain';

    /**
     * @var array settings for processors
     */
    protected $_settings = array();

    protected $_processors = array();

    /**
     * @var string full path of file
     */
    protected $_filepath = null;
}
