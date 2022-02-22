<?php
class editInvoiceProviderForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['iv_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_INVOICE_PROVIDER';
		$this->dbTable = 'invoice_providers';
        $this->upload = true;

        $this->addControls(
            (new inputText('iv_name', 'LBL_NAME'))
                ->setRequired(),
            (new inputSelect('iv_provider', 'LBL_PROVIDER'))
                ->setOptions(Invoices::getProviders())
                ->setRequired(),

            (new inputSwitch('iv_test_mode', 'LBL_TEST_MODE'))
                ->setColor(enumColors::Danger()),

            (new inputText('iv_user_name', 'LBL_USER_NAME')),

            (new inputPassword('iv_password', 'LBL_PASSWORD'))
                ->showTogglePassword(),

            (new inputText('iv_api_key', 'LBL_API_KEY')),

            (new inputSwitch('iv_enabled', 'LBL_ENABLED'))
                ->setColor(enumColors::Success())
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['iv_shop_id'] = $this->owner->shopId;
        if(Empty($this->values['iv_test_mode'])) $this->values['iv_test_mode'] = 0;
        if(Empty($this->values['iv_enabled'])) $this->values['iv_enabled'] = 0;

        if($this->values['iv_password']){
            $this->values['iv_password'] = serialize(cryptString(SMTP_HASH_KEY, $this->values['pp_password']));
        }else{
            $this->values['iv_password'] = '';
        }

        if($this->values['iv_enabled']){
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'invoice_providers',
                    [
                        'iv_enabled' => 0
                    ],
                    [
                        'iv_shop_id' => $this->owner->shopId
                    ]
                )
            );
        }
    }

    public function onAfterLoadValues() {
        if(!Empty($this->values['iv_password'])){
            $pwd = unserialize($this->values['iv_password']);
            $this->values['iv_password'] = deCryptString(SMTP_HASH_KEY, $pwd);
        }
    }

    public function onAfterInit() {
        if($this->keyFields['iv_id']) {
            $this->setSubtitle($this->values['iv_name']);
        }
    }
}
