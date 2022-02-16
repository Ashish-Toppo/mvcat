<?php

trait template{

    // hold the values
    private $vars = [];

    public function assignTemplateValues ($key, $value) {
        $this->vars[$key] = $value;
    }

    public function renderTemplate ($templateName) {
        $path = "../app/views/". $templateName .".html";

        $contents = file_get_contents($path);

        foreach ($this->vars as $key => $value) {
            $contents = preg_replace('/\[\['.$key.'\]\]/', $value, $contents);
        }

        eval('?>'. $contents .'<?php');
    }
}