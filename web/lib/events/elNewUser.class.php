<?php
class elNewUser extends eventLoader {

    public function getContent() {
        return false;
    }

    public function getTemplate() {
        return '<img src="' . $this->getExtraData('img') . '" class="rounded-circle" width="30" alt=""> {{ formatName("' . $this->getExtraData('firstname') . '", "' . $this->getExtraData('lastname') . '") }}';
    }

    public function getStructuredData() {
        return [];
    }
}