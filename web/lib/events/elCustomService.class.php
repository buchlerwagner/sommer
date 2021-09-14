<?php
class elCustomService extends eventLoader {

    public function getContent() {
        return false;
    }

    public function getTemplate() {
        return $this->getExtraData('serviceTitle');
    }

    public function getStructuredData() {
        return [];
    }
}