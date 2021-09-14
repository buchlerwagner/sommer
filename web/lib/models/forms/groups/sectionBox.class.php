<?php
class sectionBox extends formSections {
    const Type = 'box';

    public function __construct($id, $title = '', $icon = '', $text = ''){
        $this->id = $id;
        $this->title = $title;
        $this->icon = $icon;
        $this->text = $text;
        $this->elements = [];
    }

    public function getType():string {
        return self::Type;
    }
}