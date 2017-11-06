<?php

/**
 * Klass MicroTime
 */
class MicroTime{
    private $times = [];
    
    /**
     * 
     */
    public function __construct() {}

    /**
     * Method start measurement.
     * @param string $name - name of measurement
     */
    public function Start($name){
        $this->times["$name"]['start'] = microtime(true);
    }
    
    /**
     * Method stop measurement.
     * @param string $name - name of measurement
     */
    public function Stop($name){
        $this->times["$name"]['stop'] = microtime(true);
    }
    
    /**
     * Destructor schow results.
     */
    public function __destruct() {
        echo " 
            <center>
            <table>
                <tr>
                    <th>Name of measurement</th>
                    <th>Time (seconds)</th>
                </tr>
        ";
        
        foreach($this->times as $A => $B){
            $tmp = ($this->times[$A]['stop'] - $this->times[$A]['start'])*1000;
            
            echo "<tr><td>$A</td><td>$tmp</td></tr>";
        }
        echo "</table>";

    }
}
//example

$test = new MicroTime();

$test->Start('work time');
//sth code work
usleep(1000);
$test->Stop('work time');

$test->Start('sleep time');
//sleeping
usleep(5000);
$test->Stop('sleep time');

?>