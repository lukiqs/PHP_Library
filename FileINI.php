<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileINI
 *
 * @author lukiqs
 */
class FileINI {
    private $fileName;
    private $content= array();
    private $defSection;

    public function __construct($path = null,$section = null) {
        if($path != null){
            $this->fileName = $path;
            $this->load();
            $this->defSection = $section;
        }        
    }
    
    public function save(){
        $file = "";
        foreach($this->content as $key => $val){
            $file.="[$key]".PHP_EOL;
            foreach($val as $sKey => $sVal){
                $sVal = (string) $sVal;
                $file.="$sKey = '$sVal'".PHP_EOL;
            }
        }
        
        write_file($this->fileName, $file, 'w');
    }
    
    private function load(){
            $this->content = parse_ini_file($this->fileName,true);
    }
    
    public function get($key,$section = null){
        if($section != null)
            return $this->content[$section][$key];
        else
            return $this->content[$this->defSection][$key];
    }
    
    public function set($key, $value,$section = null){
        if($section != null)
            $this->content[$section][$key] = $value;
        else
            $this->content[$this->defSection][$key] = $value;
    }

    public function __destruct() {
        //$this->save();
    }
}