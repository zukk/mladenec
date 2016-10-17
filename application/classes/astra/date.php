<?php
/**
 * Description of date
 *
 * @author mit08
 */
class Astra_Date {
    protected $year;
    protected $month;
    protected $day;
    
    public function __toString () {
        return sprintf ('%1$d-%2$02d-%3$02d', $this->year, $this->month, $this->day);
        //return $this->year . '-' . $this->month . '-' . $this->day;
    }
    
    public function to_timestamp() {
        return mktime(0, 0, 0, $this->month, $this->day, $this->year);
    }
}