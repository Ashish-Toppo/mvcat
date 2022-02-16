<?php

class Framework{

    // use trait debugger (for easy debugging)
    use debugger;
    use codeDebugger;
    use urlDebugger;

    // set default controller and method
    public $controller = 'home';
    public $method = 'default';
    public $params = [];

    // initialize debugger values
    public $debugger = [
        'controller' => '', 'method' => '',
    ]; 

    public function __construct () {
        // set debugger values
        $this->debugger['controller'] = $this->controller;
        $this->debugger['method'] = $this->method;
    }
    
    // function to get the url
    protected function url () {
        if(isset($_GET['url'])) {
            $url = $_GET['url'];
            $url = rtrim($url); // trim from the right side
            $url = filter_var($url, FILTER_SANITIZE_URL); // filter unwanted characters from the url 
            $url = explode("/", $url); 
            return $url;
        }
    }

    // function to set the controller
    protected function setController ($name) {
        $this->controller = $name;
        $this->debugger['controller'] = $name;
    }

    protected function setMethod ($name) {
        $this->method = $name;
        $this->debugger['method'] = $name;
    }

    public function getDebugInfo ($i = 0) {
        $fileinfo = 'no_file_info';
        $backtrace = debug_backtrace();  
        if(!empty($backtrace[$i]) && is_array($backtrace[$i])) {
            $fileinfo = $backtrace[$i]['file'] . ":" . $backtrace[$i]['line'];
        }

        return $fileinfo;
    }
}