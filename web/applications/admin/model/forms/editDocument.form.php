<?php
class editDocumentForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['doc_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_DOCUMENT';
		$this->dbTable = 'documents';
        $this->upload = true;

        $this->addExtraField('doc_filename', false);
        $this->addExtraField('doc_hash', false);

        $this->addControls(
            (new inputSelect('doc_dt_id', 'LBL_DOCUMENT_TYPE'))
                ->setRequired()
                ->setOptions($this->owner->lists->setEmptyItem('LBL_SELECT')->getDocumentTypes()),
            (new inputCheckGroup('doc_mail_types', 'LBL_ATTACH_TO_MAILS'))
                ->setOptions($this->owner->lists->reset()->getTemplateTypes()),
            (new groupRow('row2'))->addElements(
                (new inputSwitch('doc_optional', 'LBL_OPTIONALLY_SELECTABLE'))
                    ->setGroupClass('mb-0 mt-2')
                    ->setColSize('col-12')
                    ->changeDefaultState(enumChangeAction::Readonly(), '#doc_select_text')
                    ->changeState(1, enumChangeAction::Editable(), '#doc_select_text'),
                (new inputText('doc_select_text', 'LBL_TEXT_SHOW_ON_SELECT'))
                    ->setColSize('col-12')
                    ->setReadonly()
            ),
            (new inputFile('upload_file', 'LBL_DOCUMENT'))
                ->addData('max-file-size', 10240)
                ->addData('theme', 'fas')
                ->addData('show-upload', 'false')
                ->addData('show-caption', 'true')
                ->addData('show-remove', 'false')
                ->addData('show-cancel', 'false')
                ->addData('show-close', 'false')
                ->addData('allowed-file-extensions', '["pdf", "txt", "doc", "docx"]')
                ->addData('show-preview', 'false')
                ->notDBField(),
            (new previewLink('file-preview', enumFileTypes::Document()))
                ->notDBField()
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {
        if(!Empty($this->values['doc_optional']) && Empty($this->values['doc_select_text'])) {
            $this->addError('ERR_1000', self::FORM_ERROR, ['doc_select_text']);
        }
    }

    public function onBeforeSave() {
        $this->values['doc_shop_id'] = $this->owner->shopId;

        if(Empty($this->values['doc_optional'])) $this->values['doc_optional'] = 0;

        $this->uploadFile();
        unset($this->values['file-preview']);
    }

    public function onAfterLoadValues() {
        if($this->values['doc_hash']){
            $this->getControl('file-preview')
                ->setFileData(
                    $this->keyFields['doc_id'],
                    $this->values['doc_hash'],
                    $this->values['doc_filename']
                );
        }else{
            $this->removeControl('file-preview');
        }
    }

    private function uploadFile(){
        if (!empty($_FILES[$this->name]['name']['upload_file']) && empty($_FILES[$this->name]['error']['upload_file'])) {
            $savePath = DIR_UPLOAD . $this->owner->shopId . '/documents/';
            $this->deleteFile();

            $pathParts = pathinfo($_FILES[$this->name]['name']['upload_file']);
            $this->values['doc_hash'] = uuid::v4() . '.' . $pathParts['extension'];
            $this->values['doc_filename'] = $_FILES[$this->name]['name']['upload_file'];

            if(!is_dir($savePath)){
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
            }

            move_uploaded_file($_FILES[$this->name]['tmp_name']['upload_file'], $savePath . $this->values['doc_hash']);
        } else {
            unset($this->values['doc_hash']);
        }
    }

    private function deleteFile(){
        $savePath = DIR_UPLOAD . $this->owner->shopId . '/documents/';

        if(!Empty($this->values['doc_hash']) && file_exists($savePath . $this->values['doc_hash'])) {
            unlink($savePath . $this->values['doc_hash']);
        }

        $this->values['doc_hash'] = '';
    }
}
