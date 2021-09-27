<?php
class editHostForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['host_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_HOST';
		$this->dbTable = 'hosts';

        $this->addControls(
            (new inputText('host_name', 'LBL_HOST_SITE_NAME'))
                ->setRequired(),
            (new inputText('host_host', 'LBL_HOST_NAME'))
                ->setRequired()
                ->setPrepend('https://'),
            (new inputText('host_public_site', 'LBL_PUBLIC_SITE')),
            (new inputText('host_default_email', 'LBL_DEFAULT_EMAIL'))
                ->setRequired(),
            (new groupRow('row1'))->addElements(
                (new inputSelect('host_application', 'LBL_APPLICATION'))
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->reset()->getApplications()),
                (new inputSelect('host_theme', 'LBL_THEME'))
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->reset()->getThemes())
            ),
            (new groupRow('row0'))->addElements(
                (new inputSelect('host_country', 'LBL_COUNTRY', DEFAULT_COUNTRY))
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->getCountries()),
                (new inputSelect('host_timezone', 'LBL_TIMEZONE', DEFAULT_TIMEZONE_ID))
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->getTimeZones())
            ),
            (new groupRow('row2'))->addElements(
                (new inputSelect('host_default_language', 'LBL_DEFAULT_LANGUAGE', DEFAULT_LANGUAGE))
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->reset()->getAllLanguages()),
                (new inputSelect('host_default_currency', 'LBL_DEFAULT_CURRENCY', DEFAULT_CURRENCY))
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->reset()->getAllCurrencies(false))
            ),
            (new groupRow('row3'))->addElements(
                (new inputCheckGroup('host_languages', 'LBL_SELECTABLE_LANGUAGES'))
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->reset()->getAllLanguages()),
                (new inputCheckGroup('host_currencies', 'LBL_SELECTABLE_CURRENCIES'))
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->reset()->getAllCurrencies(false))
            ),
            (new inputSwitch('host_force_ssl', 'LBL_FORCE_SSL'))
                ->setGroupClass('mb-0')
                ->setColor(enumColors::Warning()),
            (new inputSwitch('host_production', 'LBL_HOST_IS_PRODUCTION'))
                ->setColor(enumColors::Danger())

        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {
        if (!empty($this->values['host_host'])) {
            $res = $this->owner->db->getFirstRow(
                "SELECT host_id FROM " . DB_NAME_WEB . ".hosts WHERE host_host LIKE \"" . $this->owner->db->escapeString($this->values['host_host']) . "\" AND host_id != '" . $this->keyFields['host_id'] . "'"
            );
            if (!empty($res)) {
                $this->addError('ERR_10016', self::FORM_ERROR, ['host_host']);
            }
        }
        if(!in_array($this->values['host_default_language'], $this->values['host_languages'])){
            $this->addError('ERR_1000', self::FORM_ERROR, ['host_default_language']);
        }

        if(!in_array($this->values['host_default_currency'], $this->values['host_currencies'])){
            $this->addError('ERR_1000', self::FORM_ERROR, ['host_default_currency']);
        }
    }

    public function onBeforeSave() {
        $this->values['host_shop_id'] = $this->owner->shopId;
        $this->values['host_host'] = safeFileName($this->values['host_host']);
        $this->values['host_host'] = strtolower(trim($this->values['host_host']));

        if(Empty($this->values['host_force_ssl'])) $this->values['host_force_ssl'] = 0;
        if(Empty($this->values['host_production'])) $this->values['host_production'] = 0;
    }

    public function onAfterSave($statement) {
        $this->owner->mem->delete(HOST_SETTINGS . $this->values['host_host']);
    }

    public function onAfterInit() {
        if($this->keyFields['host_id']) {
            $this->setSubtitle($this->values['host_name'] . ' (' . $this->values['host_host'] . ')');
        }
    }
}
