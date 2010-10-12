<?php

class Minify_SourceCollection {

    protected $_sources = array();

    protected $_contentType = null;

    public function addSource(Minify_Source_Interface $source)
    {
        if ($this->_contentType) {
            if ($source->getContentType() !== $this->_contentType) {
                throw new Exception('all source contentTypes must match');
            }
        } else {
            $this->_contentType = $source->getContentType();
        }
        $this->_sources[$source->getId()] = $source;
    }

    public function getContentType()
    {
        return $this->_contentType;
    }

    /**
     * @return array
     */
    public function getSources()
    {
        return $this->_sources;
    }

    /**
     * @param string $id
     * @return Minify_Source_File
     */
    public function getSource($id)
    {
        return isset($this->_sources[$id]) ? $this->_sources[$id] : null;
    }

    /**
     * If set, this string is made filename-safe and prepended to the cache id
     * returned. This can make it easier to see what cache files contain.
     * @var string
     */
    public $cacheReadableId = '';

    /**
     * Init functions can call this function to influence the cache id (e.g.
     * based on $_GET or other environment vars
     *
     * @param string $str
     * @param id $id of source
     */
    public function alterCacheId($str, $id = '*')
    {
        $this->_cacheIdArray[] = array($str, $id);
    }

    /**
     * This function's value should influence the cache id
     * @return array
     */
    public function getCacheIdArray()
    {
        return array($this->_cacheIdArray, $this->_processors, $this->_settings);
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
     *
     * @var array settings for processors
     */
    protected $_settings = array();

    public function getProcessors()
    {
        return $this->_processors;
    }

    public function addProcessor(Minify_Processor_Interface $processor) {
        $this->_processors[] = $processor;
    }

    protected $_processors = array();

    protected $_cacheIdArray = array();
}
