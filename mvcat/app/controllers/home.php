<?php

class home extends Controller{
    public function default () {
        $this->view('home', [
            'message' => 'this is template generated',
            'name' => 'Ashish Toppo',
            'age' => '20'
        ]);
    }

    
}