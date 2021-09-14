<?php
class ntSell extends ntEvent {

    public function check(): bool {
        return true;
    }

    public function setup() {
        $this->addVariable('url', '/fleet/view|fleet/' . $this->fleetId . '/');
    }

    public function getEvent(): eventCreator {

    }

}