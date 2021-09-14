<?php
class timelineForm extends formBuilder {
    public function setupKeyFields() {
        $this->setKeyFields(['prop_id']);
    }

	public function setup() {
		$this->dbTable = "property_types";
		$this->title = 'LBL_EDIT_TIMELINE';

        $this->addExtraField('prop_name');

        $general = (new groupFieldset('general-data'))->addElements(
            (new inputSwitch('prop_timeline', 'LBL_SHOW_ON_TIMELINE')),
            (new inputSelect('prop_level', 'LBL_COLOR'))
                ->setOptions($this->owner->lists->setEmptyItem('LBL_DEFAULT')->getColors()),
            (new inputRadio('prop_timeline_position', 'LBL_POSITION', 'other'))
                ->setOptions([
                    'other' => 'LBL_OTHER_THAN_PREVIOUS',
                    'left'  => 'LBL_LEFT',
                    'right' => 'LBL_RIGHT',
                ]),
            (new inputHidden('scope', 'prop_event_scope', null, true))
        );

        $groups = (new groupInclude('user-level-selector2', [
            'label' => 'LBL_SHOW_FOR_THE_FOLLOWING_GROUPS',
            'formname' => $this->name,
            'name' => 'prop_event_scope',
            'userlevels' => $GLOBALS['USER_ROLES']
        ]));

        $this->addControls(
            $general,
            $groups
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

	public function onAfterInit() {
        $this->setSubtitle($this->values['prop_name']);

		if(!is_array($this->values['prop_event_scope'])) {
			$group = $this->values['prop_event_scope'];
			$this->values['prop_event_scope'] = json_decode($group, true);
		}

		$this->getControl('user-level-selector2')->setData(
		    [
		        'values' => $this->values
            ]
        );

		$this->removeControl('scope');
	}

	public function onAfterLoadValues() {
        $this->values['prop_level'] = ucfirst($this->values['prop_level']);
    }

    public function onBeforeSave() {
		$this->values['prop_event_scope'] = json_encode($this->values['prop_event_scope']);
		if(!$this->values['prop_event_scope'] || $this->values['prop_event_scope'] == 'null') $this->values['prop_event_scope'] = NULL;

		if(!$this->values['prop_timeline']) !$this->values['prop_timeline'] = 0;
        $this->values['prop_level'] = strtolower($this->values['prop_level']);
        if(!$this->values['prop_level']) $this->values['prop_level'] = NULL;
	}

}
