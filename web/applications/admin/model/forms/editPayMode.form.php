<?php
class editPayModeForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['pm_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PAYMODE';
		$this->dbTable = 'payment_modes';

        $group = (new groupFieldset('general'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputSelect('pm_type', 'LBL_TYPE', 0))
                    ->setColSize('col-12')
                    ->changeState(PAYMENT_TYPE_CARD, enumChangeAction::Show(), '#pm_pp_id-formgroup')
                    ->changeDefaultState(enumChangeAction::Hide(), '#pm_pp_id-formgroup')
                    ->setOptions($this->owner->lists->reset()->getPaymentTypes())
                    ->setRequired(),
                (new inputSelect('pm_pp_id', 'LBL_PAYMENT_PROVIDER', 0))
                    ->setColSize('col-12')
                    ->setOptions($this->owner->lists->reset()->setEmptyItem('LBL_SELECT')->getPaymentProviders()),
                (new inputText('pm_name', 'LBL_TITLE'))
                    ->setColSize('col-12')
                    ->setRequired()
            ),
            (new groupRow('row2'))->addElements(
                (new inputText('pm_price', 'LBL_FEE', 0))
                    ->setColSize('col-6 col-lg-3')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setAppend($this->owner->currencySign),
                (new inputSelect('pm_vat', 'LBL_VAT_KEY', 27))
                    ->setColSize('col-6 col-lg-3')
                    ->setOptions($this->owner->lists->reset()->getVat()),
                (new inputText('pm_limit_max', 'LBL_LIMIT_MAX', 0))
                    ->setColSize('col-6 col-lg-3')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setHelpText('LBL_PAYMODE_LIMIT_INFO')
                    ->setAppend($this->owner->currencySign . '-ig')
            ),
            (new groupRow('row3'))->addElements(
                (new inputText('pm_order', 'LBL_POSITION', $this->getMaxOrder()))
                    ->setColSize('col-12 col-lg-3')
                    ->addClass('text-right')
                    ->onlyNumbers()
            ),
            (new groupRow('row4'))->addElements(
                (new inputTextarea('pm_text', 'LBL_DESCRIPTION'))
                    ->setColSize('col-12')
                    ->setRows(4),
                (new inputTextarea('pm_email_text', 'LBL_EMAIL_TEXT'))
                    ->setColSize('col-12')
                    ->setRows(4)
            ),

            (new groupRow('row5'))->addElements(
                (new inputFile('upload_file', 'LBL_LOGO'))
                    ->setColSize('col-12')
                    ->addData('max-file-size', 1024)
                    ->addData('theme', 'fas')
                    ->addData('show-upload', 'false')
                    ->addData('show-caption', 'true')
                    ->addData('show-remove', 'false')
                    ->addData('show-cancel', 'false')
                    ->addData('show-close', 'false')
                    ->addData('allowed-file-extensions', '["jpg", "png", "jpeg", "gif", "svg"]')
                    ->addData('show-preview', 'false')
                    ->notDBField(),

                (new previewImage('pm_logo'))
                    ->setSize(300, 50)
                    ->setPath($this->owner->shopId . '/'),

                (new inputCheckbox('removeImg', 'LBL_REMOVE_LOGO', 0))
                    ->setColSize('col-12')
                    ->notDBField()
            )

            //(new inputSwitch('pm_default', 'LBL_DEFAULT_PAYMENT_MODE', 0))
        );

        $this->addControls($group);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {
        if($this->values['pm_type'] == PAYMENT_TYPE_CARD && Empty($this->values['pm_pp_id'])){
            $this->addError('ERR_1000', self::FORM_ERROR, ['pm_pp_id']);
        }
    }

    public function onAfterInit() {
        if(Empty($this->values['pm_logo'])){
            $this->removeControl('removeImg');
        }
    }

    public function onBeforeSave() {
        $this->values['pm_shop_id'] = $this->owner->shopId;
        if($this->values['pm_type'] != PAYMENT_TYPE_CARD){
            $this->values['pm_pp_id'] = 0;
        }

        if($this->values['removeImg']){
            $this->deleteImage();
        }else {
            $this->uploadFile();
        }

        unset($this->values['removeImg']);

        /*
        if(Empty($this->values['pm_default'])) {
            $this->values['pm_default'] = 0;
        }else{
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    $this->dbTable,
                    [
                        'pm_default' => 0
                    ],
                    [
                        'pm_shop_id' => $this->owner->shopId,
                    ]
                )
            );
        }
        */
    }

    private function getMaxOrder(){
        $order = 0;
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                $this->dbTable,
                [
                    'MAX(pm_order) AS maxOrder'
                ],
                [
                    'pm_shop_id' => $this->owner->shopId
                ]
            )
        );
        if($row){
            $order = (int)$row['maxOrder'];
        }

        return ++$order;
    }

    private function uploadFile(){
        if (!empty($_FILES[$this->name]['name']['upload_file']) && empty($_FILES[$this->name]['error']['upload_file'])) {
            $savePath = DIR_UPLOAD . $this->owner->shopId . '/';
            $this->deleteImage();

            $pathParts = pathinfo($_FILES[$this->name]['name']['upload_file']);
            $this->values['pm_logo'] = uuid::v4() . '.' . $pathParts['extension'];

            if(!is_dir($savePath)){
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
            }

            move_uploaded_file($_FILES[$this->name]['tmp_name']['upload_file'], $savePath . $this->values['pm_logo']);
        } else {
            unset($this->values['pm_logo']);
        }
    }

    private function deleteImage(){
        $savePath = DIR_UPLOAD . $this->owner->shopId . '/';

        if(!Empty($this->values['pm_logo']) && file_exists($savePath . $this->values['pm_logo'])) {
            unlink($savePath . $this->values['pm_logo']);
        }

        $this->values['pm_logo'] = '';
    }
}
