<?php
/**
 * Class Minify_Logger  
 * @package Minify
 */

/** 
 * Message logging class. Simple FirePHP wrapper
 * 
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 *
 */
class Minify_Logger {

    /**
     * @var FirePHP
     */
    protected $_firephp = null;

    /**
     * @param FirePHP $firephp
     */
    public function __construct(FirePHP $firephp)
    {
        $this->_firephp = $firephp;
    }

    /**
     * Pass a message to the logger (if set)
     *
     * @param string $msg
     * @param string $label (default = 'Minify')
     * @return null
     */
    public function log($msg, $label = 'Minify') {
        $this->_firephp->log($msg, $label);
    }   
}
