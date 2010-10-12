<?php

class Minify_SourceCollection_Combiner {

    /**
     * @var Minify_SourceCollection
     */
    public $collection = array();

    public $cacheIdPrefix = 'min_';

    protected $_glue = null;

    public function __construct(Minify_SourceCollection $collection, $glue = null)
    {
        $this->collection = $collection;
        if ($glue === null) {
            $glue = ($collection->getContentType() === 'application/x-javascript')
                ? "\n;"
                : "";
        }
        $this->_glue = $glue;
    }

    protected $_cacheId = null;

    public function getCacheId()
    {
        if (! $this->_cacheId) {
            // run all inits
            foreach ($this->collection->getSources() as $source) {
                $options = array(
                    'source' => $source,
                    'collection' => $this->collection,
                );
                foreach ($source->getProcessors() as $processor) {
                    $processor->init($options);
                }
            }
            $options = array(
                'collection' => $this->collection,
            );
            foreach ($this->collection->getProcessors() as $processor) {
                $processor->init($options);
            }
            $cacheIdArray = $this->collection->getCacheIdArray();
            foreach ($this->collection->getSources() as $source) {
                $cacheIdArray[] = array($source->getId(), $source->getProcessors(), $source->getSettings());
            }
            $hash = md5(serialize($cacheIdArray));
            $readable = preg_replace('/[^a-zA-Z0-9\\.=_,]/', '', $this->collection->cacheReadableId);
            $readable = preg_replace('/\\.+/', '.', $readable);
            $readable = substr($readable, 0, 250 - 34 - strlen($this->cacheIdPrefix));
            $this->_cacheId = $this->cacheIdPrefix . $readable . ' ' . $hash;
        }
        return $this->_cacheId;
    }

    public function getLastModified()
    {
        $max = 0;
        foreach ($this->collection->getSources() as $source) {
            $max = max($max, $source->getLastModified());
        }
        return $max;
    }

    /**
     * Combine and process all content
     * @return string
     */
    public function getContent()
    {
        if (! $this->_cacheId) {
            $this->getCacheId();
        }
        $pieces = array();
        foreach ($this->collection->getSources() as $source) {
            $piece = $source->getContent();
            foreach ($source->getProcessors() as $processor) {
                $piece = $processor->process($piece);
            }
        }
        $content = implode($this->_glue, $pieces);
        unset($pieces, $piece); // free some memory hopefully
        foreach ($this->collection->getProcessors() as $processor) {
            $content = $processor->process($content);
        }
        return $content;
    }
}
