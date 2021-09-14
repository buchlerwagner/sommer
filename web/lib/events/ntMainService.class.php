<?php
class ntMainService extends ntEvent {

    public function check(): bool {
        $isDue = $this->checkDueDate();
        $currentMileage = $this->car->getMileage();

        if(!$isDue) {
            $nextMileage = $this->settings['data']['lastMileage'] + $this->settings['mileage'];

            if ($currentMileage >= $nextMileage) {
                $isDue = true;
                $this->setUpdateField('lastMileage', $currentMileage);
            }
        }

        $this->addVariable('currentMileage', $currentMileage);

        return $isDue;
    }

    public function setup() {
        $this->addVariable('url', '/fleet/view|fleet/' . $this->fleetId . '/');
    }

    public function getEvent(): eventCreator {

    }

}