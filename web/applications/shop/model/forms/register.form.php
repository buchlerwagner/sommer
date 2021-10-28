<?php
class registerForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['us_id']);
    }

    public function setup() {
        $this->title = 'MENU_REGISTER';
        $this->dbTable = 'users';
        $this->boxed = false;
        $this->displayErrors = true;

        $this->addControls(
            (new inputFloatingLabelText('us_lastname', 'LBL_LASTNAME'))
                ->setRequired(),
            (new inputFloatingLabelText('us_firstname', 'LBL_FIRSTNAME'))
                ->setRequired(),
            (new inputFloatingLabelText('us_email', 'LBL_EMAIL'))
                ->setRequired(),
            (new inputFloatingLabelText('us_phone', 'LBL_PHONE'))
                ->onlyNumbers('+'),
            (new inputCheckbox('agree_privacy', 'LBL_I_AGREE_PRIVACY_POLICY', 0))
                ->setRequired()
        );

        $this->addButtons(
            new buttonSave('BTN_REGISTER', 'btn-theme')
        );
    }

    public function onValidate() {
        if (!empty($this->values['us_email'])) {
            $res = $this->owner->db->getFirstRow(
                "SELECT us_id FROM users WHERE us_shop_id = " . $this->owner->shopId . " AND us_email LIKE \"" . $this->owner->db->escapeString($this->values['us_email']) . "\""
            );
            if (!empty($res)) {
                $this->addError('ERR_10009', self::FORM_ERROR, ['us_email']);
            }
        }

        if (empty($this->values['agree_privacy'])) {
            $this->addError('ERR_2004', self::FORM_ERROR, ['agree_privacy']);
        }
    }

    public function onBeforeSave() {
        $this->values['us_birth_date'] = null;
        $this->values['us_group'] = USER_GROUP_CUSTOMERS;
        $this->values['us_role'] = USER_ROLE_USER;
        $this->values['us_registered'] = 'NOW()';
        $this->values['us_timezone'] = $this->owner->hostConfig['timeZoneID'];
        $this->values['us_shop_id'] = $this->owner->shopId;
        $this->values['us_enabled'] = 1;
        $this->values['us_hash'] = uuid::v4();
        $this->values['us_password'] = password_hash(generateRandomString(32), PASSWORD_DEFAULT);

        unset($this->values['agree_terms']);
    }

    public function onAfterSave($statement) {
        if($this->keyFields['us_id']){
            $this->owner->email->prepareEmail(
                'register',
                $this->keyFields['us_id'],
                [
                    'id' => $this->keyFields['us_id'],
                    'email' => $this->values['us_email'],
                    'link' => $this->owner->user->setId($this->keyFields['us_id'])->getPasswordChangeLink('REGISTER'),
                ]
            );
        }

        $this->owner->pageRedirect($this->owner->getPageName('register') . '?success');
    }
}