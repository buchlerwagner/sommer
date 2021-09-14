<?php
class elTireChange extends eventLoader {

    public function getContent() {
        return false;
    }

    public function getTemplate() {
        $type = $this->getExtraData('type');
        $html  = '{{ _("' . $type['name'] . '") }}: ';
        $html .= $this->getExtraData('manufacturer') . ' ' . $this->getExtraData('brand') . ' (' . $this->getExtraData('size') . ')';

        return $html;
    }

    public function getStructuredData() {
        return [];
    }
}