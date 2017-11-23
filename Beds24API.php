<?php

abstract class Beds24{
    const PATH_LOG = "dump/sync_beds24/";
    protected $auth = [];
    
    protected function setPropKey($key){
        $this->auth['propKey'] = (string) $key;
    }
    
    protected function setAPIKey($key){
        $this->auth['apiKey'] = (string) $key;
    }
    
    protected function getAuth(){
        if($this->auth['propKey'] != null)
            return $this->auth;
        else
            return false;
    }    
}

class Reservation extends Beds24{
    static public $countOfConnections = 0;
    private $data = [];
    
    public function __construct($propKey = null, $apiKey = null) {
        $this->setPropKey($propKey);
        $this->setAPIKey($apiKey);
    }
    
    protected function setConfig($roomId, $bookId = null, $propKey = null, $apiKey = null){
        $this->data['roomId'] = (string) $roomId;
        if($bookId != null) 
            $this->data['bookId'] = (string) $bookId;
        if($propKey != null)
            $this->setPropKey($propKey);
        if($apiKey != null)
            $this->setAPIKey($apiKey);
    }
    
    protected function set($keyInInturs,$arDate,$deDate,$status = 1, $autoSend = false){
        $this->data['status'] = (string) $status;
        $this->data['firstNight'] = (string) $arDate;
        $this->data['lastNight'] = (string) $deDate;
        $this->data['guestFirstName'] = (string) $keyInInturs;
        $this->data['notifyUrl'] = "true";
        $this->data['notifyHost'] = "true";
        $temp[0] = [ "code" => "", "text" => "" ];
        $this->data['infoItems'] = $temp;  
        
        if($autoSend)
            $this->debugJson ();
            return $this->send();
    }
    
    protected function send(){
        $this->data['authentication'] = $this->getAuth();
        $json = json_encode($this->data);
        $url = "https://api.beds24.com/json/setBooking";      
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_POST, 1) ;
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($ch);
        curl_close ($ch);
        sleep(5);
        ++self::$countOfConnections;
        
        $arrayResult = json_decode($result);
        if(isset($arrayResult->error))
            $this->createLog($arrayResult);
        if(isset($arrayResult->bookId))
            return $arrayResult->bookId;
        else return false;
    }

    public function debugJson(){
        $this->data['authentication'] = $this->getAuth();
        
        echo json_encode($this->data);
    }
    
    protected function createLog($data){
        file_put_contents(self::PATH_LOG."error_res/log_".date('d_m_Y_H_i_s').'.txt', print_r($data,1)."\n".print_r($this->data,1)."\n".print_r(get_object_vars($this), 1)."\n".print_r($_SERVER, 1));
    }
}
