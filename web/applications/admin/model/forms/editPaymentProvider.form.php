<?php
class editPaymentProviderForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['pp_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PAYMENT_PROVIDER';
		$this->dbTable = 'payment_providers';
        $this->upload = true;

        $this->addControls(
            (new inputText('pp_name', 'LBL_NAME'))
                ->setRequired(),
            (new inputSelect('pp_provider', 'LBL_PROVIDER'))
                ->setOptions(Payments::getProviders())
                ->setRequired(),

            (new groupRow('row1'))->addElements(
                (new inputText('pp_shopid', 'LBL_SHOP_ID'))
                    ->setColSize('col-6')
                    ->setRequired(),

                (new inputSelect('pp_currency', 'LBL_CURRENCY', DEFAULT_CURRENCY))
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->reset()->getAllCurrencies(false))
            ),

            (new inputSwitch('pp_test_mode', 'LBL_TEST_MODE'))
                ->setColor(enumColors::Danger()),

            (new inputPassword('pp_password', 'LBL_PASSWORD'))
                ->showTogglePassword(),

            (new inputText('pp_url_frontend', 'LBL_FRONTEND_URL'))
                ->setRequired(),

            (new inputText('pp_url_return', 'LBL_RETURN_URL')),

            (new inputText('pp_url_backend', 'LBL_CALLBACK_URL')),

            (new groupRow('row2'))->addElements(
                (new inputFile('upload_file', 'LBL_PRIVATE_KEY'))
                    ->setColSize('col-12')
                    ->addData('max-file-size', 1024)
                    ->addData('theme', 'fas')
                    ->addData('show-upload', 'false')
                    ->addData('show-caption', 'true')
                    ->addData('show-remove', 'false')
                    ->addData('show-cancel', 'false')
                    ->addData('show-close', 'false')
                    ->addData('allowed-file-extensions', '["pem", "key", "pub"]')
                    ->addData('show-preview', 'false')
                    ->notDBField(),

                (new inputText('pp_private_key', false))
                    ->setColSize('col-12')
                    ->setReadonly(),

                (new inputCheckbox('removeFile', 'LBL_REMOVE_FILE', 0))
                    ->setColSize('col-12')
                    ->notDBField()
            )

        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {

    }

    public function onBeforeSave() {
        $this->values['pp_shop_id'] = $this->owner->shopId;
        if(Empty($this->values['pp_test_mode'])) $this->values['pp_test_mode'] = 0;

        if($this->values['pp_password']){
            $this->values['pp_password'] = serialize(cryptString(SMTP_HASH_KEY, $this->values['pp_password']));
        }else{
            $this->values['pp_password'] = '';
        }

        if($this->values['removeFile']){
            $this->deleteFile();
        }else {
            $this->uploadFile();
        }

        unset($this->values['removeFile']);
    }

    public function onAfterLoadValues() {
        if(!Empty($this->values['pp_password'])){
            $pwd = unserialize($this->values['pp_password']);
            $this->values['pp_password'] = deCryptString(SMTP_HASH_KEY, $pwd);
        }
    }

    public function onAfterInit() {
        if($this->keyFields['pp_id']) {
            $this->setSubtitle($this->values['pp_name']);
        }

        if(Empty($this->values['pp_private_key'])){
            $this->removeControl('pp_private_key');
            $this->removeControl('removeFile');
        }
    }

    private function uploadFile(){
        if (!empty($_FILES[$this->name]['name']['upload_file']) && empty($_FILES[$this->name]['error']['upload_file'])) {
            $savePath = DIR_PRIVATE_KEYS . $this->owner->shopId . '/';
            $this->deleteFile();

            $pathParts = pathinfo($_FILES[$this->name]['name']['upload_file']);
            $this->values['pp_private_key'] = uuid::v4() . '.' . $pathParts['extension'];

            if(!is_dir($savePath)){
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
            }

            move_uploaded_file($_FILES[$this->name]['tmp_name']['upload_file'], $savePath . $this->values['pp_private_key']);
        } else {
            unset($this->values['pp_private_key']);
        }
    }

    private function deleteFile(){
        $savePath = DIR_PRIVATE_KEYS . $this->owner->shopId . '/';

        if(!Empty($this->values['pp_private_key']) && file_exists($savePath . $this->values['pp_private_key'])) {
            unlink($savePath . $this->values['pp_private_key']);
        }

        $this->values['pp_private_key'] = '';
    }
}
