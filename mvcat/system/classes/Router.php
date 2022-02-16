<?php

class Router extends Framework {
    

    public function __construct () {
       
        // get url
        $url = $this->url();

        $controllerPath = '../app/controllers/'.$this->controller.'.php';

        if(!empty($url)) {
            // set the controller value
            $this->setController($url[0]);

            // set controller path
            $controllerPath = '../app/controllers/' . $url[0] . '.php';

            // show error message if controller file does not exists
            if(!file_exists($controllerPath)) {
                $this->showControllerNotFoundError($this->debugger['controller']);
            }

            // unset the 0th value in $url
            unset($url[0]);
        }

        // initialize the controller
        require_once($controllerPath);
        $this->controller = new $this->controller;

        // if second parameter exists
        if(isset($url[1]) && !empty($url[1])) {
            // set method value
            $this->setMethod($url[1]);
            
            // show error message if method does not exists
            if(!method_exists($this->controller, $this->method)) {
                $this->showMethodNotFoundError($this->debugger['controller'], $this->debugger['method']);
            }

            // unset the 1th value in $url
            unset($url[1]);

        }

       

        // set the params
        if(isset($url)) {
            $this->params = $url;
        } else {
            $this->params = [];
        }

        call_user_func_array([$this->controller, $this->method], $this->params);
    }
}