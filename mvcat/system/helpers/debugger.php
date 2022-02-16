<?php

// base debugger
trait debugger{

    // function to show custom error messages for easy debugging
    public function showErrorMessage ($header, $message = '') {
        require_once "../system/debugger/views/error.php";
        die();
    }
}

// debugger for the model
trait modelDebugger {

    // use base debugger
    public function error ($header, $message = '') {
        require_once "../system/debugger/views/error.php";
        die();
    }

    public function showDBConnectionError ($error) {
        $this->error(
            'Could Not Connect To Database',
            'PDO Connection could not be made to the database <ul>'.
            '<li> Please check if the DBMS is running properly. </li>'. 
            '<li> Make sure you have entered correct database credentials inside <b>"config/config.php"</b> file. </li>'. 
            '<li> Make sure you are using a mysql database (mvcat only supports mysql currently).</li>'.
            '<li>'. $error .'</li>'.
            '</ul>'
        );
    }
}

// debugger for code errors 
trait codeDebugger {
    // use base debugger
    public function codeDebuggerError ($header, $message = '') {
        require_once "../system/debugger/views/error.php";
        die();
    }

    // function to show view not found error
    public function showViewNotFoundError ($viewName, $debugInfo = 'no info found') {
        $this->codeDebuggerError(
            'View Not Found',
            'View <b>"' . $viewName . '"</b> not found. <ul>'.
            '<li> Invalid View called in:  <b>' . $debugInfo . '</b></li>'.
            '<li> Make make sure you have a file named <b>"' . $viewName . '.php"</b> inside <b>"app/views/"</b> directory. </li>'. 
            '<li> Make sure you have written the correct file name </li>'. 
            '</ul>'
        );
    }

    // function to show model not found error
    public function showModelNotFoundError ($modelName, $debugInfo = 'no info found') {
        $this->codeDebuggerError(
            'Model Not Found',
            'Model <b>"' . $modelName . '"</b> not found. <ul>'.
            '<li> Invalid Model called in:  <b>' . $debugInfo . '</b></li>'.
            '<li> Make make sure you have a file named <b>"' . $modelName . '.php"</b> inside <b>"app/models/"</b> directory. </li>'. 
            '<li> Make sure you have written the correct file name </li>'. 
            '</ul>'
        );
    }
}


// debugger for url errors
trait urlDebugger {
    // use base debugger
    public function urlDebuggerError ($header, $message = '') {
        require_once "../system/debugger/views/error.php";
        die();
    }

    // function to show controller not found error
    public function showControllerNotFoundError ($controllerName) {
        $this->urlDebuggerError(
            'Controller Not Found',
            'Controller <b>"' . $controllerName . '"</b> not found. <ul>'.
            '<li> Make make sure you have a file named <b>"' . $controllerName . '.php"</b> inside <b>"app/controllers/"</b> directory. </li>'.
            '<li> Also make sure the class name is same with the file name. </li>' . 
            '<li> Make sure you have entered the correct url </li>'. 
            '</ul>'
        );
    }

    // function to show method not found error
    public function showMethodNotFoundError ($controllerName, $methodName) {
        $this->urlDebuggerError(
            'Method Not Found',
            'Method <b>"' . $methodName . '"</b> not found. <ul>'.
            '<li> Please check if you have a public function named <b>"' . $methodName . '"</b> inside <b>"' . $controllerName . '.php"</b> file. </li>'. 
            '<li> Make sure you have entered the correct url </li>'. 
            '</ul>'
        );
    }
}