<?php
class elRefueling extends eventLoader {

    public function getContent() {
        return false;
    }

    public function getTemplate() {
        $html = $this->getExtraData('amount') . ' ' . $this->getExtraData('unit') . ' {{ _("' . $this->getExtraData('type') . '")|lower }}';

        if($this->getExtraData('product')){
            $html .= ' (' .  $this->getExtraData('product') . ')';
        }

        if($this->getExtraData('location')){
            $html .= '<br><span class="text-80"><i class="fas fa-location-circle mr-1"></i>' .  $this->getExtraData('location') . '</span>';
        }

        return $html;
    }

    public function getStructuredData() {
        return [];
    }
}