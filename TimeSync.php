<?php

class TimeSync{
    static public $url = "http://api.timezonedb.com/v2/list-time-zone";
    private $APIkey;
    private $country;
    private $format;

    /**
     * Constructor.
     * @param type $key - api key (register timezonedb)
     * @param type $country - country code ISO 3166
     * @param type $format - JSON or XML, default JSON 
     */
    public function __construct($key,$country,$format = 'json') {
        $this->APIkey = $key;
        $this->country = $country;
        $this->format = $format;
        
        $this->sync();
    }
    
    private function getTimestamp(){     
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, self::$url."?key=$this->APIkey&format=$this->format&country=$this->country");
        $result = curl_exec($ch);
        curl_close ($ch);
        $result  = json_decode($result,TRUE);
        
        if($result['status']=='FAILED'){
            //log
            return false;
        }
               
        //var_dump($result);
        return (int) ($result['zones'][0]['timestamp'] - $result['zones'][0]['gmtOffset']);
    }
    
    private function sync(){
        $time_act = $this->getTimestamp();
        $time_svr = time();
        
        //echo "$time_act  :  $time_svr <br>"; 
        //echo date('d-m-Y H:i:s',$time_act)." : ".date('d-m-Y H:i:s',$time_svr);
        
        if($time_act != $time_svr){
            $t = date('H:i:s',$time_act);
            system("date --set '$t' ");
        }
    }
    
}

$n = new TimeSync('hdsgfhdfghdsgffg','PL');


