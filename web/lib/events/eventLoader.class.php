<?php
abstract class eventLoader {
    protected $fid;

    private $data;
    private $properties;

    private $delete = false;
    private $showInvoice = false;
    private $showCost = false;

    protected $buttons = [];

    public function __construct(array $eventData, array $properties){
        if(!Empty($eventData['e_data'])){
            $eventData['data'] = json_decode($eventData['e_data'], true);
            unset($eventData['e_data']);
        }

        $this->data = $eventData;
        $this->properties = $properties;
        $this->fid = $this->getValue('e_id');
    }

    abstract public function getContent();

    abstract public function getTemplate();

    abstract public function getStructuredData();

    public function getId(){
        return $this->fid;
    }

    public function getTitle(){
        return ($this->getValue('e_title') ?: $this->getProperty('name'));
    }

    public function getDate(){
        return ($this->getValue('e_date') ?: $this->getValue('e_timestamp'));
    }

    public function getNotes(){
        return $this->getValue('e_notes');
    }

    public function getMileage(){
        return ($this->getValue('e_mileage') ? number_format($this->getValue('e_mileage'), 0, ',', ' ') . ' km' : false);
    }

    public function getCost(){
        $cost = false;

        if($this->showInvoice && $this->getValue('e_cost')){
            $cost = [
                'price' => $this->getValue('e_cost'),
                'currency' => $this->getValue('e_currency')
            ];
        }

        return $cost;
    }

    public function getIcon(){
        return $this->getProperty('icon');
    }

    public function getLink(){
        return false;
    }

    public function getColor(){
        return $this->getProperty('level');
    }

    public function getPosition(){
        return $this->getProperty('timeline_position');
    }

    public function showInvoice($show = true){
        $this->showInvoice = $show;
        return $this;
    }

    public function showCost($show = true){
        $this->showCost = $show;
        return $this;
    }

    public function getInvoice(){
        $invoice = false;

        if($this->showInvoice && $this->getValue('ivi_id')){
            $invoice = [
                'id' => $this->getValue('ivi_id'),
                'number' => $this->getValue('ivi_number')
            ];

            if($this->getValue('ivi_filename') && $this->getValue('ivi_hash')){
                $invoice['previewUrl'] = '/ajax/preview/?type=invoice_incoming&id=' . $this->getValue('ivi_id') . '&hash=' . $this->getValue('ivi_hash');
                $invoice['downloadUrl'] = '/file.php?type=invoice_incoming&id=' . $this->getValue('ivi_id') . '&hash=' . $this->getValue('ivi_hash');
                $invoice['filename'] = $this->getValue('ivi_filename');
                $invoice['hash'] = $this->getValue('ivi_hash');
            }

            if($this->getValue('e_rf_id')){
                $invoice['refueling'] = '/ajax/forms/viewRefueling/' . $this->getValue('e_rf_id') . '|' . $this->getValue('e_ivi_id') . '/?view=1';
            }
        }

        return $invoice;
    }

    public function setDeleteable($delete = true){
        $this->delete = $delete;
        return $this;
    }

    public function isDeleteable(){
        return $this->delete;
    }

    protected function getProperty($key){
        $property = false;

        if($this->properties[$key]) {
            $property = $this->properties[$key];
        }

        return $property;
    }

    protected function getValue($key){
        $value = false;

        if($this->data[$key]) {
            $value = $this->data[$key];
        }

        return $value;
    }

    protected function getAllData(){
        $data = false;

        if($this->data['data']) {
            $data = $this->data['data'];
        }

        return $data;
    }

    protected function getExtraData($key){
        $data = false;

        if($this->data['data'][$key]) {
            $data = $this->data['data'][$key];
        }

        return $data;
    }

    protected function addButtons(formButton ...$button){
        $this->buttons[] = $button->init();
        return $this;
    }

    public function getButtons(){
        return $this->buttons;
    }
}