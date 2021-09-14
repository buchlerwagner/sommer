<?php
class eventHandler extends ancestor {
    /**
     * @var $event eventCreator
     */
	protected $event;

	private $eventId;
	private $propertyId;
	private $properties = [];

	public function register(eventCreator $event){
        $this->event = $event;

        if($this->loadEvent()) {
            $this->saveEvent();
        }

		return $this;
	}

	public function trigger(){
        if($this->properties['prop_has_notification']){
            /**
             * @var $notification ntEvent
             */
            $notification = $this->owner->addByClassName('nt' . $this->event->getType(), false, [
                $this->propertyId,
                $this->event->getFleetId()
            ]);

            $this->owner->notifications->sendNotifications($notification);

            $this->owner->remove('nt' . $this->event->getType());
        }

        return $this;
    }

	private function getEventProperties(){
        return $this->properties;
    }

    private function loadEvent(){
        $property = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'property_types',
                [],
                [
                    'prop_event' => $this->event->getType()
                ]
            )
        );
        if($property){
            $valid = true;
            $this->propertyId = (int) $property['prop_id'];
            $this->properties = $property;
        }else{
            throw new Exception("The given event type (" . $this->event->getType() . ") is not exist!");
        }

	    return $valid;
    }

	private function saveEvent(){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLInsert(
                'events',
                [
                    'e_f_id' => $this->event->getFleetId(),
                    'e_prop_id' => $this->propertyId,
                    'e_ivi_id' => $this->event->getInvoiceId(),
                    'e_rf_id' => $this->event->getRefuelingId(),
                    'e_type' => $this->event->getType(),
                    'e_date' => $this->event->getDate(),
                    'e_data' => $this->event->getData(true),
                    'e_title' => $this->event->getTitle(),
                    'e_notes' => $this->event->getNote(),
                    'e_mileage' => $this->event->getMileage(),
                    'e_cost' => $this->event->getCost(),
                    'e_currency' => $this->event->getCurrency(),
                    'e_hidden' => ($this->event->isHidden() ? 1 : 0),
                    'e_created_by' => $this->owner->user->id,
                    'e_timestamp' => $this->event->getTimestamp(),
                ],
                [
                    'e_f_id',
                    'e_prop_id',
                    'e_ivi_id',
                    'e_rf_id'
                ]
            )
        );

        $this->eventId = $this->owner->db->getInsertRecordId();

        return $this;
    }

}