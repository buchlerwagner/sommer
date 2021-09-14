<?php
abstract class ntEvent extends ancestor {
    const FIX_VARIABLES = [
        'userId',
        'name',
        'firstName',
        'lastName',
        'hash',
        'email',
        'phone',
        'link',
        'licencePlate',
        'dueDate',
    ];

    /**
     * @var $car carData
     */
    protected $car;

    protected $fleetId;
    protected $properties = [];
    protected $settings = [];
    protected $onDate = false;

    /**
     * @var $recipients ntRecipients
     */
    private $recipients;

    private $propId;
    private $eventId = 0;
    private $variables = [];
    private $update = [];


    abstract public function check() : bool;

    abstract public function setup();

    abstract public function getEvent() : ?eventCreator;

    public function __construct($propId, $fleetId){
        $this->propId = $propId;
        $this->fleetId = $fleetId;
    }

    final public function init(){
        $this->car = $this->owner->addByClassName('carData');
        $this->car->init($this->fleetId);

        $this->loadProperties();
    }

    final public function finish(){
        $this->updateSettings();
        $this->reset();
    }

    private function reset(){
        $this->propId = null;
        $this->fleetId = null;
        $this->properties = [];
        $this->settings = [];
        $this->variables = [];

        $this->owner->remove('ntRecipients');
    }

    final public function prepareNotification(){
        $this->setup();

        $this->addVariable('licencePlate', $this->car->getLicencePlate());
        $this->addVariable('dueDate', $this->settings['next_event']);

        $this->recipients = $this->owner->addByClassName('ntRecipients', false, [
            $this->eventId,
            $this->properties
        ]);

        $this->recipients->loadUsers();
    }

    final public function getId(){
        return $this->eventId;
    }

    final public function getPropertyId(){
        return $this->propId;
    }

    final public function getRecipients():ntRecipients{
        if(!$this->recipients){
            throw new Exception('Recipients are not loaded!');
        }

        return $this->recipients;
    }

    final public function getTimeout(){
        return $this->properties['timeout'];
    }

    final public function getProperties(){
        return $this->properties;
    }

    final public function getVariables(){
        return $this->variables;
    }

    public function getNotificationTemplate(){
        return $this->properties['text_notification'];
    }

    public function getEmailTemplate(){
        return $this->properties['text_email'];
    }

    public function getEmailSubject(){
        return $this->properties['text_email_subject'];
    }

    public function getSMSTemplate(){
        return $this->properties['text_sms'];
    }

    public function createEvent(){
        if($this->properties['trigger_event']) {
            try {
                if ($event = $this->getEvent()) {
                    $this->owner->eventHandler->register($event);
                }
            }catch (Exception $e){

            }
        }
        return $this;
    }

    final protected function addVariable($key, $value){
        $this->variables[$key] = $value;
        return $this;
    }

    final protected function setUpdateField($field, $value){
        if(strpos($field, '_') === false){
            if(!isset($this->update['fp_data'])){
                if($this->settings['data']){
                    $this->update['fp_data'] = $this->settings['data'];
                }else {
                    $this->update['fp_data'] = [];
                }
            }

            $this->update['fp_data'][$field] = $value;
        }else {
            $this->update[$field] = $value;
        }
        return $this;
    }

    protected function checkDueDate(){
        $isDue = false;

        if ($this->settings['next_event']) {
            $now = strtotime(date('Y-m-d'));
            $dt = strtotime($this->settings['next_event']);
            $first = strtotime($this->settings['next_event'] . ' ' . $this->properties['1st_notification'] * -1 . ' days');
            $second = strtotime($this->settings['next_event'] . ' ' . $this->properties['2nd_notification'] * -1 . ' days');

            if ($dt <= $now || $first == $now || $second == $now) {
                $isDue = true;

                if($dt <= $now) {
                    $this->onDate = true;
                }
            }

        } else {
            $isDue = true;
            $this->onDate = true;
        }

        if($this->onDate){
            $this->calculateNextDueDate();
        }

        return $isDue;
    }

    protected function calculateNextDueDate($fromDate = false){
        $date = calculateNextDueDate($this->settings['recurrence'], $fromDate);
        $this->setUpdateField('fp_next_event', $date);

        return $date;
    }

    private function loadProperties(){
        $this->properties = [];
        $this->settings = [];

        $properties = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'fleet_properties',
                [],
                [
                    'fp_prop_id' => $this->propId,
                    'fp_f_id' => $this->fleetId
                ],
                [
                    'property_types' => [
                        'on' => [
                            'prop_id' => 'fp_prop_id'
                        ]
                    ]
                ]
            )
        );

        if(!$properties){
            $properties = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'property_types',
                    [],
                    [
                        'prop_id' => $this->propId,
                    ]
                )
            );
        }

        if($properties){
            foreach ($properties as $key => $val) {
                $prefix = strstr($key, '_', true);
                $subKey = substr($key, strpos($key, "_") + 1);

                switch($prefix){
                    case 'prop':
                        $this->properties[$subKey] = $val;
                        break;
                    case 'fp':
                    default:
                        $this->settings[$subKey] = $val;
                        break;
                }
            }

            $this->properties['scope'] = json_decode($this->properties['notification_scope'], true);
            if(!$this->properties['scope']) $this->properties['scope'] = [];
            unset($this->properties['notification_scope']);

            if($this->properties['variables']) {
                $this->properties['variables'] = explode('|', trim($this->properties['variables'], '|'));
            }

            if($this->settings) {
                $this->eventId = (int) $this->settings['id'];
                $this->settings['data'] = json_decode($this->settings['data'], true);
            }
        }
    }

    private function updateSettings(){
        if(!Empty($this->update)) {
            if(!Empty($this->update['fp_data']) && is_array($this->update['fp_data'])){
                $this->update['fp_data'] = json_encode($this->update['fp_data']);
            }

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'fleet_properties',
                    $this->update,
                    [
                        'fp_id' => $this->eventId,
                        'fp_prop_id' => $this->propId,
                        'fp_f_id' => $this->fleetId
                    ]
                )
            );
        }

        if(!Empty($this->variables)){
            $variables = array_merge(self::FIX_VARIABLES, array_keys($this->variables));
            if($variables && $variables != $this->properties['variables']){
                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLUpdate(
                        'property_types',
                        [
                            'prop_variables' => implode('|', $variables)
                        ],
                        [
                            'prop_id' => $this->propId
                        ]
                    )
                );
            }
        }
    }

}