<?php
class sectionTab extends formSections {
    const Type = 'tab';

    public function __construct($id, $title = '', $icon = '', $active = false){
        $this->id = $id;
        $this->title = $title;
        $this->icon = $icon;

        if(isset($_REQUEST['tab'])){
            if($_REQUEST['tab'] == $id) {
                $this->active = true;
            }else{
                $this->active = false;
            }
        }else {
            $this->active = $active;
        }

        $this->elements = [];
    }

    public function setActive(){
        $this->active = false;
        return $this;
    }

    public function getType():string {
        return self::Type;
    }
}