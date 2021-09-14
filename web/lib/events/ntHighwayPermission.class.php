<?php
class ntHighwayPermission extends ntEvent {

    public function check(): bool {
        return $this->checkDueDate();
    }

    public function setup() {
        $this->addVariable('url', '/fleet/view|fleet/' . $this->fleetId . '/');
    }

    public function getEvent(): eventCreator {

    }

}