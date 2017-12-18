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
    
    /**
     * The method save data in configuration file
     */
    public function save(){
        $file = "";
        foreach($this->content as $key => $val){
            $file.="[$key]".PHP_EOL;
            foreach($val as $sKey => $sVal){
                $sVal = (string) $sVal;
                $file.="$sKey = '$sVal'".PHP_EOL;
            }
        }
        
        file_put_contents($this->fileName, $file);
    }
    
    /**
     * The method load configuration file
     */
    private function load(){
            $this->content = parse_ini_file($this->fileName,true);
    }
    
    /**
     * The method get value
     * @param string $key - name of key
     * @param string $section - name of section (optional)
     * @return string - value
     */
    public function get($key,$section = null){
        if($section != null)
            return $this->content[$section][$key];
        else
            return $this->content[$this->defSection][$key];
    }
    
    /**
     * The method set configuration
     * @param string $key - name of key
     * @param string $value - value
     * @param string $section - name of section (optional)
     */
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
