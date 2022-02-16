<?php

class Controller extends Framework{

    // use template trait
    use template;

    // function to load a view
    public function view ($viewName, $data = []) {

        foreach ($data as $key => $val) {
            $this->assignTemplateValues($key, $val);
        }

        if(file_exists("../app/views/".$viewName.".html")) {
            $this->renderTemplate($viewName);
        } else {
            $this->showViewNotFoundError($viewName, $this->getDebugInfo(1));
        }
    }

    // function to load a model
    public function model ($modelName) {
        if (file_exists("../app/models/". $modelName .".php")) {
            require_once "../app/models/".$modelName.".php";
            return new $modelName;
        } else {
            $this->showModelNotFoundError($modelName, $this->getDebugInfo(1));
        }
    }

    // function to get inputs from user
    public function input ($inputName) {
        if($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'post') {
            return trim(strip_tags($_POST[$inputName]));
        } else {
            if($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'get') {
                return trim(strip_tags($_GET[$inputName]));
            }
        }
    }

    // function to set a session
    public function setSession ($sessionName, $sessionValue) {

        if(!empty($sessionName) && !empty($sessionValue)) {
            $_SESSION[$sessionName] = $sessionValue;
        }
    }

    // function to get a session
    public function getSession ($sessionName) {
        if(!empty($sessionName)) {
            return $_SESSION[$sessionName];
        }
    }

    // function to unset a session
    public function unsetSession ($sessionName) {
        if(!empty($sessionName)) {
            unset($_SESSION[$sessionName]);
        }
    }

    // function to destroy all sessions
    public function destroy () {
        session_destroy();
    }

    
}