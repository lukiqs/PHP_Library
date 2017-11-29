<?php
/**
 * Class RemoveOldLogs is responsible for remove old logs. Lic free, open sourse
 * @author lukiqs
 */
final class RemoveOldLogs{
    private $mainDir = [];
    
    public function __construct() { }
    
    /**
     * The method remove files in implemented folder paths. 
     * You should use addForRemove method, to add path.
     * @throws Exception 
     */
    public function remove(){
        
        try{ 
            if(!$this->correctScriptRun())
                throw new Exception("Skrypt nie moze byc uruchamiany z poziomu przegladarki.");
            
            for($i=0;$i<count($this->mainDir);$i++){
                try{  
                    if(!is_dir($this->mainDir[$i]['dir']))
                        throw new Exception("Katalog {$this->mainDir[$i]['dir']} nie istnieje.\n");
                        
                    if ($dh = opendir($this->mainDir[$i]['dir'])){
                        while (($file = readdir($dh)) !== false){
                            if($file != "." && $file != "..")
                                if(!is_dir($this->mainDir[$i]['dir'].$file)  && 
                                        (time() - filemtime($this->mainDir[$i]['dir'].$file) >= $this->mainDir[$i]['time'])){
                                    if(!unlink($this->mainDir[$i]['dir'].$file))
                                            echo "Nie mozna usunc pliku ".$this->mainDir[$i]['dir'].$file;  
                                    //else echo "Usuwam ".$this->mainDir[$i]['dir'].$file."\n";
                                }
                        }
                    closedir($dh);
                    }
                        
                } catch (Exception $ex) {
                    echo $ex->getMessage();
                }                
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    
    /**
     * The method add folder path.
     * @param string $dir - Path folder.
     * @param int $time - Time in second. You can use static function afterDays.
     */
    public function addForRemove($dir,$time){
        $array['dir'] = $dir;
        $array['time'] = $time;
        array_push($this->mainDir, $array);
    }
    
    /**
     * The method check mode of run.
     * @return boolean - Return false where script run by browser, otherwise return true.
     */
    private function correctScriptRun(){
        if(php_sapi_name() == "cli") 
            return true;
        else 
            return false;
    }
    
    /**
     * The method count seconds.
     * @param int $number - number of days
     * @return int Time in seconds
     */
    public static function afterDays($number){
        return $number*86400;
    }
}

//example
$n = new RemoveOldLogs();
$n->addForRemove("/SR/SRprod/SR/templates_c/", RemoveOldLogs::afterDays(2));
$n->remove();