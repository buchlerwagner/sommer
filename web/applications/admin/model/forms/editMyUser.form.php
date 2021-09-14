<?php
class editMyUserForm extends formBuilder {
    public function setupKeyFields() {
        $this->setKeyFields(['us_id', 'us_ug_id']);
    }

    public function setup() {
		$this->dbTable = DB_NAME_WEB . '.users';
        $this->rights = 'my-users';
        $this->readonly = true;
        $this->keyFields['us_ug_id'] = $this->owner->user->getGroupId();
        $this->title = 'LBL_VIEW_USER';

        $general = (new groupFieldset('general-data'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputSelect('us_title', 'LBL_PERSON_TITLE'))
                    ->setColSize('col-3')
                    ->setOptions($this->owner->lib->getList('titles')),
                (new inputText('us_lastname', 'LBL_LASTNAME'))
                    ->setRequired()
                    ->setColSize('col-5'),
                (new inputText('us_firstname', 'LBL_FIRSTNAME'))
                    ->setRequired()
                    ->setColSize('col-4')
            ),
            (new groupRow('row2'))->addElements(
                (new inputText('us_birth_place', 'LBL_BIRTH_PLACE'))
                    ->setColSize('col-4'),
                (new inputDate('us_birth_date', 'LBL_BIRTH_DATE'))
                    ->setIcon('fas fa-calendar')
                    ->setMaxDate(date('Y-m-d'))
                    ->setColSize('col-3'),
                (new inputText('us_mother_name', 'LBL_MOTHER_NAME'))
                    ->setColSize('col-5')
            )
        );

        $contact = (new groupFieldset('contact-data', 'LBL_CONTACT'))->addElements(
            (new inputText('us_email', 'LBL_EMAIL'))->setRequired(),
            (new inputText('us_phone', 'LBL_PHONE'))
        );

        $address = (new groupFieldset('address-data', 'LBL_ADDRESS'))->addElements(
            (new groupRow('row3'))->addElements(
                (new inputSelect('us_country', 'LBL_COUNTRY', 'HU'))
                    ->setOptions($this->owner->lib->getList('countries'))
                    ->setColSize('col-3'),
                (new inputText('us_zip', 'LBL_ZIP'))
                    ->onlyNumbers()
                    ->setColSize('col-2'),
                (new inputText('us_city', 'LBL_CITY'))
                    ->setColSize('col-7'),
                (new inputText('us_address', 'LBL_ADDRESS'))
                    ->setColSize('col-12')
            )
        );

        if($this->parameters['keyvalues'][1]){
            $role = (new groupFieldset('user-role', 'LBL_ROLES'))->addElements(
                (new inputSelect('us_role', false, 'USER'))
                    ->setOptions($this->owner->lib->getList('roles', ['group' => USER_GROUP_PARTNERS]))
            );
        }else {
            $role = (new groupFieldset('user-role', 'LBL_ROLES'))->addElements(
                (new groupRow('row4'))->addElements(
                    (new inputSelect('us_ug_id', 'LBL_GROUP'))
                        ->setColSize('col-12 col-sm-6')
                        ->setOptions($this->owner->lib->getList('groups', ['limit' => $this->owner->user->getUserGroups()])),
                    (new inputSelect('us_role', 'LBL_ROLE', 'USER'))
                        ->setColSize('col-12 col-sm-6')
                        ->setOptions($this->owner->lib->getList('roles', ['group' => USER_GROUP_PARTNERS]))
                )
            );
        }

        $this->addTabs(
            (new sectionTab('user', 'LBL_USER_DATA', 'fal fa-id-card', true))->addElements(
                $general,
                $contact,
                $address,
                $role
            )
        );

        $sectionFleet = (new sectionTab('fleet', 'LBL_USER_CARS', 'fal fa-cars'))->addElements(
            (new subTable('user-cars'))->add($this->loadSubTable('userCars', false, ['readonly' => true, 'myFleet' => true]))
        );

        $sectionCards = (new sectionTab('cards', 'LBL_FUEL_CARDS', 'fal fa-credit-card'))->addElements(
            (new subTable('user-fuel-cards'))->add($this->loadSubTable('userFuelCards', false, ['readonly' => true]))
        );

        $this->addTabs(
            $sectionFleet,
            $sectionCards
        );

	}

	public function onAfterInit() {
        $this->setSubtitle($this->values['us_lastname'] . ' ' . $this->values['us_firstname']);
        $this->owner->setPageTitle($this->values['us_lastname'] . ' ' . $this->values['us_firstname']);
	}

	public function onValidate() {
		if (!empty($this->values['us_email'])) {
			$res = $this->owner->db->getFirstRow(
				"SELECT us_id FROM " . DB_NAME_WEB . ".users WHERE us_email LIKE \"" . $this->owner->db->escapeString($this->values['us_email']) . "\" AND us_id != '" . $this->keyFields['us_id'] . "'"
			);
			if (!empty($res)) {
				$this->addError('ERR_10009', self::FORM_ERROR, ['us_email']);
			}
		}
	}

	public function onBeforeSave() {
        if(!$this->values['us_birth_date']) $this->values['us_birth_date'] = null;
    }

    public function onAfterLoadValues() {
        if(!$this->values['us_birth_date']) $this->values['us_birth_date'] = '';
    }
}
