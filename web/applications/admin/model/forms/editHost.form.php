<?php
class editHostForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['host_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_HOST';
		$this->dbTable = 'hosts';

        $general = (new sectionTab('general', 'LBL_HOST_SETTINGS', '', true))
            ->addElements(
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
                (new inputSwitch('host_share_session', 'LBL_SHARE_SESSION_SUBDOMAINS'))
                    ->setGroupClass('mb-0'),
                (new inputSwitch('host_production', 'LBL_HOST_IS_PRODUCTION'))
                    ->setColor(enumColors::Danger()),
                (new inputSwitch('host_maintenance', 'LBL_HOST_MAINTENANCE'))
                    ->setColor(enumColors::Danger())
        );

        $smtp = (new sectionTab('smtp', 'LBL_SMTP_SETTINGS'))
            ->addElements(
                (new inputText('host_smtp_host', 'LBL_SMTP_HOST')),
                (new groupRow('row5'))->addElements(
                    (new inputSelect('host_smtp_port', 'LBL_SMTP_PORT', 2525))
                        ->setColSize('col-6')
                        ->setOptions([
                            'TLS' => [
                                25 => 25,
                                587 => 587,
                                2525 => 2525,
                                8025 => 8025,
                            ],
                            'SSL' => [
                                443 => 443,
                                465 => 465,
                                8465 => 8465,
                            ]
                        ]),
                    (new inputSelect('host_smtp_ssl', 'LBL_SMTP_SSL', 'TLS'))
                        ->setColSize('col-6')
                        ->setOptions([
                            'TLS' => 'TLS',
                            'SSL' => 'SSL',
                        ])
                ),
                (new groupRow('row4'))->addElements(
                    (new inputText('host_smtp_user', 'LBL_SMTP_USER'))
                        ->setColSize('col-6'),
                    (new inputPassword('host_smtp_pwd', 'LBL_SMTP_PASSWORD'))
                        ->setColSize('col-6')
                        ->showTogglePassword()
                )
            );

        $httpAuth = (new sectionTab('auth', 'LBL_HTTP_AUTHENTICATION'))
            ->addElements(
                (new inputSwitch('host_protect', 'LBL_PROTECT_WEBSITE'))
                    ->changeState(1, enumChangeAction::Editable(), '#host_auth_user, #host_auth_password')
                    ->changeDefaultState(enumChangeAction::Readonly(), '#host_auth_user, #host_auth_password')
                    ->setGroupClass('mb-0'),

                (new groupRow('row6'))->addElements(
                    (new inputText('host_auth_user', 'LBL_AUTH_USER'))
                        ->setColSize('col-6'),
                    (new inputPassword('host_auth_password', 'LBL_AUTH_PASSWORD'))
                        ->setColSize('col-6')
                        ->showTogglePassword()
                ),

                (new inputText('host_auth_realm', 'LBL_AUTH_REALM', 'Restricted area')),
                (new inputText('host_auth_error', 'LBL_AUTH_ERROR_MESSAGE', 'You are not authorized to see this page.'))
            );

        $this->addTabs($general, $smtp, $httpAuth);

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

        if(!Empty($this->values['host_protect'])){
            if(Empty($this->values['host_auth_user'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_auth_user']);
            }
            if(Empty($this->values['host_auth_password'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_auth_password']);
            }
            if(Empty($this->values['host_auth_realm'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_auth_realm']);
            }
            if(Empty($this->values['host_auth_error'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_auth_error']);
            }
        }
    }

    public function onBeforeSave() {
        $this->values['host_shop_id'] = $this->owner->shopId;
        $this->values['host_host'] = safeFileName($this->values['host_host']);
        $this->values['host_host'] = strtolower(trim($this->values['host_host']));

        if(Empty($this->values['host_force_ssl'])) $this->values['host_force_ssl'] = 0;
        if(Empty($this->values['host_production'])) $this->values['host_production'] = 0;
        if(Empty($this->values['host_share_session'])) $this->values['host_share_session'] = 0;
        if(Empty($this->values['host_protect'])) $this->values['host_protect'] = 0;
        if(Empty($this->values['host_maintenance'])) $this->values['host_maintenance'] = 0;

        if(Empty($this->values['host_smtp_port'])) $this->values['host_smtp_port'] = 0;
        if(Empty($this->values['host_smtp_ssl'])) $this->values['host_smtp_ssl'] = 'SSL';

        if($this->values['host_smtp_pwd']){
            $this->values['host_smtp_pwd'] = serialize(cryptString(SMTP_HASH_KEY, $this->values['host_smtp_pwd']));
        }else{
            $this->values['host_smtp_pwd'] = '';
        }

        if($this->values['host_auth_password']){
            $this->values['host_auth_password'] = serialize(cryptString(SMTP_HASH_KEY, $this->values['host_auth_password']));
        }else{
            $this->values['host_auth_password'] = '';
        }

    }

    public function onAfterSave($statement) {
        $this->owner->mem->delete(HOST_SETTINGS . $this->values['host_host']);
    }

    public function onAfterLoadValues() {
        if(!Empty($this->values['host_smtp_pwd'])){
            $pwd = unserialize($this->values['host_smtp_pwd']);
            $this->values['host_smtp_pwd'] = deCryptString(SMTP_HASH_KEY, $pwd);
        }

        if(!Empty($this->values['host_auth_password'])){
            $pwd = unserialize($this->values['host_auth_password']);
            $this->values['host_auth_password'] = deCryptString(SMTP_HASH_KEY, $pwd);
        }
    }

    public function onAfterInit() {
        if($this->keyFields['host_id']) {
            $this->setSubtitle($this->values['host_name'] . ' (' . $this->values['host_host'] . ')');
        }
    }
}
