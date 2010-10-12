<?php

interface Minify_Source_Interface {

    public function getId();

    public function getContent();

    public function getLastModified();

    public function getContentType();

    public function getSettings();

    public function setSetting($key, $value);

    public function getProcessors();

    public function addProcessor();
}