<?php
class editSliderForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['s_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_SLIDER';
		$this->dbTable = 'sliders';
        $this->upload = true;

        $fontSizes = array_combine(range(100,400), array_map(function($a){ return $a . '%'; }, range(100, 400)));

        $group = (new groupFieldset('gen'))->addElements(
            (new inputText('s_title', 'LBL_HEADLINE'))
                ->setRequired(),

            (new inputSwitch('s_hide_title', 'LBL_HIDE_HEADLINE')),

            (new inputTextarea('s_text', 'LBL_TEXT'))
                ->setRows(4),
            (new inputText('s_link', 'LBL_LINK')),
            (new groupRow('row1'))->addElements(
                (new inputText('s_order', 'LBL_POSITION'))
                    ->setColSize('col-4 col-lg-2')
                    ->addClass('text-right')
                    ->onlyNumbers(),

                (new inputSelect('s_title_size', 'LBL_TITLE_FONT_SIZE', 250))
                    ->setColSize('col-4 col-lg-2')
                    ->setOptions($fontSizes),

                (new inputSelect('s_text_size', 'LBL_TEXT_FONT_SIZE', 100))
                    ->setColSize('col-4 col-lg-2')
                    ->setOptions($fontSizes)
            ),

            (new inputFile('upload_file', 'LBL_IMAGE'))
                ->addData('max-file-size', 10240)
                ->addData('theme', 'fas')
                ->addData('show-upload', 'false')
                ->addData('show-caption', 'true')
                ->addData('show-remove', 'false')
                ->addData('show-cancel', 'false')
                ->addData('show-close', 'false')
                ->addData('allowed-file-extensions', '["jpg", "jpeg", "png"]')
                ->addData('show-preview', 'false')
                ->notDBField(),
            (new previewImage('s_image'))
                //->setSize(200, 200)
                ->setResponsive(true)
                ->setPath(FOLDER_UPLOAD . $this->owner->shopId . '/sliders/'),
            (new inputCheckbox('removeImg', 'LBL_REMOVE_IMAGE', 0))
                ->notDBField()
        );

		$general = (new sectionBox('general', 'LBL_CATEGORY_DETAILS', 'fa fa-pencil'))
                        ->addClass('col-12 col-lg-6')
                        ->addElements($group);

        $this->addSections($general);
        $this->hideSidebar();

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['s_shop_id'] = $this->owner->shopId;
        if(Empty($this->values['s_hide_title'])) $this->values['s_hide_title'] = 0;

        if($this->values['removeImg']){
            $this->deleteImage();
        }else {
            $this->uploadFile();
        }

        unset($this->values['removeImg']);
    }

    public function onAfterInit() {
        $this->setSubtitle($this->values['s_title']);
    }

    public function onAfterLoadValues() {
        if(Empty($this->values['s_image'])){
            $this->removeControl('removeImg');
        }
    }

    public function onAfterSave($statement) {
        $this->owner->mem->delete(CACHE_SLIDERS . $this->owner->shopId);
    }

    private function uploadFile(){
        if (!empty($_FILES[$this->name]['name']['upload_file']) && empty($_FILES[$this->name]['error']['upload_file'])) {
            $savePath = DIR_UPLOAD . $this->owner->shopId . '/sliders/';
            $this->deleteImage();

            $pathParts = pathinfo($_FILES[$this->name]['name']['upload_file']);
            $this->values['s_image'] = uuid::v4() . '.' . $pathParts['extension'];

            if(!is_dir($savePath)){
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
            }

            move_uploaded_file($_FILES[$this->name]['tmp_name']['upload_file'], $savePath . $this->values['s_image']);
        } else {
            unset($this->values['s_image']);
        }
    }

    private function deleteImage(){
        $savePath = DIR_UPLOAD . $this->owner->shopId . '/sliders/';

        if(!Empty($this->values['s_image']) && file_exists($savePath . $this->values['s_image'])) {
            unlink($savePath . $this->values['s_image']);
        }

        $this->values['s_image'] = '';
    }
}
