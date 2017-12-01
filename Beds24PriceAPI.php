<?php

class PriceBeds24{
    const PATH_LOG = "dump/sync_beds24/";
    private $auth = [];
    private $sql;
    private $roomId;
    private $dates;
    
    public function __construct($apiKey,$propKey,$roomId) {
        $this->auth['propKey'] = $propKey;
        $this->auth['apiKey'] = $apiKey;
        $this->roomId = $roomId;
    }
    
    public function set($timestamp,$minN,$P){
        $date = date('Ymd',$timestamp);
        $this->dates['dates'][$date]['m']=$minN;
        $this->dates['dates'][$date]['p1']=$P[0];
        if($P[1])
            $this->dates['dates'][$date]['p2']=$P[1];
        if($P[2])
            $this->dates['dates'][$date]['p3']=$P[2];
        if($P[3])
            $this->dates['dates'][$date]['p4']=$P[3];
        if($P[4])
            $this->dates['dates'][$date]['p5']=$P[4];
        if($P[5])
            $this->dates['dates'][$date]['p6']=$P[5];
        if($P[6])
            $this->dates['dates'][$date]['p7']=$P[6];
        if($P[7])
            $this->dates['dates'][$date]['p8']=$P[7];
        if($P[8])
            $this->dates['dates'][$date]['p9']=$P[8];
        if($P[9])
            $this->dates['dates'][$date]['p10']=$P[9];
    }
    
    public function send(){
        $this->dates['authentication'] = $this->auth;
        $this->dates['roomId'] = $this->roomId;
        //var_dump($this->data);
        $json = json_encode($this->dates);
        $url = "https://api.beds24.com/json/setRoomDates";       
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_POST, 1) ;
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($ch);
        curl_close ($ch);
        //echo $result;
    }
        
}