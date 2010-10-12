<?php

interface Minify_Processor_Interface {
    public function init($options = array());

    public function process($str);
}