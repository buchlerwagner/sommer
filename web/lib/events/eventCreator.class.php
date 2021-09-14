<?php
class eventCreator {
    private $type;
    private $fleetId;
    private $date;
    private $timestamp = false;
    private $mileage = 0;
    private $cost = 0;
    private $invoiceId = 0;
    private $refuelingId = 0;
    private $currency = '';
    private $title = '';
    private $note = '';
    private $hidden = false;
    private $data = [];

    public function __construct($eventType, $fleetId){
        $this->type = $eventType;
        $this->fleetId = (int) $fleetId;
    }

    public function getType(){
        return $this->type;
    }

    public function getFleetId(){
        return $this->fleetId;
    }

    public function setDate($date){
        $this->date = standardDate($date);
        return $this;
    }

    public function getDate(){
        if(!$this->date){
            $this->date = date('Y-m-d');
        }

        return $this->date;
    }

    public function setMileage($mileage){
        $this->mileage = $mileage;
        return $this;
    }

    public function getMileage(){
        return $this->mileage;
    }

    public function setCost($cost, $currency){
        $this->cost = $cost;
        $this->currency = $currency;
        return $this;
    }

    public function getCost(){
        return $this->cost;
    }

    public function getCurrency(){
        return $this->currency;
    }

    public function setTitle($title){
        $this->title = $title;
        return $this;
    }

    public function getTitle(){
        return $this->title;
    }

    public function setNote($note){
        $this->note = $note;
        return $this;
    }

    public function getNote(){
        return $this->note;
    }

    public function setData($data){
        $this->data = $data;
        return $this;
    }

    public function getData($json = false){
        if(!Empty($this->data)) {
            return ($json ? json_encode($this->data) : $this->data);
        }else{
            return null;
        }
    }

    public function setHidden(){
        $this->hidden = true;
        return $this;
    }

    public function isHidden(){
        return $this->hidden;
    }

    public function setInvoiceId($invoiceId, $refuelingId = 0){
        $this->invoiceId = (int) $invoiceId;
        $this->refuelingId = (int) $refuelingId;
        return $this;
    }

    public function getInvoiceId(){
        return $this->invoiceId;
    }

    public function getRefuelingId(){
        return $this->refuelingId;
    }

    public function setTimestamp($timestamp){
        $this->timestamp = $timestamp;
        return $this;
    }

    public function getTimestamp(){
        return ($this->timestamp ?: date('Y-m-d H:i:s'));
    }
}