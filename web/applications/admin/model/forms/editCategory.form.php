<?php
class editCategoryForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['cat_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_CATEGORY';
		$this->dbTable = 'product_categories';
        $this->upload = true;

        $this->addExtraField('cat_page_img', false);

        $group = (new groupFieldset('general'))->addElements(
            (new inputText('cat_title', 'LBL_TITLE'))
                ->setRequired(),
            (new inputText('cat_url', 'LBL_URL'))
                ->setPrepend('https://' . HOST_CLIENTS . '/termekek/'),
            (new groupRow('row1'))->addElements(
                (new inputText('cat_order', 'LBL_POSITION'))
                    ->setColSize('col-12 col-lg-1')
                    ->addClass('text-right')
                    ->onlyNumbers()
            )
        );

        $editor = (new groupFieldset('editor'))->addElements(
            new inputEditor('cat_description', 'LBL_DESCRIPTION')
        );

		$general = (new sectionTab('card-general', 'LBL_CATEGORY_DETAILS', 'fa fa-pencil', true))
                        ->addElements($group, $editor);
        $this->addTabs($general);

        if($this->keyFields['cat_id']){
            $seo = (new sectionTab('card-seo', 'LBL_SEO', 'fab fa-google'))->addElements(
                (new inputText('cat_page_title', 'LBL_PAGE_TITLE')),
                (new inputTextarea('cat_page_description', 'LBL_PAGE_DESCRIPTION')),
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
                    ->notDBField()
            );

            $this->addTabs($seo);
        }

        $this->hideSidebar();

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        if(Empty($this->values['cat_url'])) {
            $this->values['cat_url'] = safeURL($this->values['cat_title']);
        }else{
            $this->values['cat_url'] = safeURL($this->values['cat_url']);
        }
        $this->values['cat_url'] = strtolower($this->values['cat_url']);

        $this->uploadFile();
    }

    public function onAfterInit() {
        $this->setSubtitle($this->values['cat_title']);
    }

    public function onAfterLoadValues() {
        if($this->values['cat_page_img']){
            $this->getControl('card-seo')->addElements(
                (new inputText('cat_page_img', 'LBL_PAGE_TITLE'))
            );
        }
    }

    private function uploadFile(){
        if (!empty($_FILES[$this->name]['name']['upload_file']) && empty($_FILES[$this->name]['error']['upload_file'])) {
            $savePath = DIR_UPLOAD_IMG . 'products/' . $this->keyFields['cat_id'] . '/';

            if(!Empty($this->values['cat_page_img']) && file_exists($savePath . $this->values['cat_page_img'])) {
                unlink($savePath . $this->values['cat_page_img']);
            }

            $pathParts = pathinfo($_FILES[$this->name]['name']['upload_file']);
            $this->values['cat_page_img'] = 'cat-' . uuid::v4() . '.' . $pathParts['extension'];

            if(!is_dir($savePath)){
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
            }

            move_uploaded_file($_FILES[$this->name]['tmp_name']['upload_file'], $savePath . $this->values['cat_page_img']);
        } else {
            unset($this->values['cat_page_img']);
        }
    }
}
