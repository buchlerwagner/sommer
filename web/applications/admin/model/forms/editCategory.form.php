<?php
class editCategoryForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['cat_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_CATEGORY';
		$this->dbTable = 'product_categories';
        $this->upload = true;

        $group = (new groupFieldset('gen'))->addElements(
            (new inputText('cat_title', 'LBL_TITLE'))
                ->setRequired(),
            (new inputText('cat_url', 'LBL_URL'))
                ->setPrepend('https://' . HOST_CLIENTS . '/' . $GLOBALS['PAGE_NAMES'][$this->owner->language]['products']['name'] . '/'),
            (new groupRow('row1'))->addElements(
                (new inputText('cat_order', 'LBL_POSITION'))
                    ->setColSize('col-6 col-lg-2')
                    ->addClass('text-right')
                    ->onlyNumbers()
            )
        );

        $editor = (new groupFieldset('editor'))->addElements(
            new inputEditor('cat_description', 'LBL_DESCRIPTION')
        );

		$general = (new sectionBox('general', 'LBL_CATEGORY_DETAILS', 'fa fa-pencil'))
                        ->addClass('col-12 col-lg-6')
                        ->addElements($group, $editor);

        $this->addSections($general);

        if($this->keyFields['cat_id']){
            $seo = (new sectionBox('seo', 'LBL_SEO', 'fab fa-google'))
                ->addClass('col-12 col-lg-6')
                ->addElements(
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
                        ->notDBField(),
                    (new previewImage('cat_page_img'))
                        //->setSize(200, 200)
                        ->setResponsive(true)
                        ->setPath(FOLDER_UPLOAD . $this->owner->shopId . '/products/' . $this->keyFields['cat_id'] . '/'),
                    (new inputCheckbox('removeImg', 'LBL_REMOVE_IMAGE', 0))
                        ->notDBField()
                );

            $this->addSections($seo);
        }

        $this->hideSidebar();

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {
        if (!empty($this->values['cat_url'])) {
            $res = $this->owner->db->getFirstRow(
                "SELECT cat_id FROM " . DB_NAME_WEB . ".product_categories WHERE cat_shop_id = " . $this->owner->shopId . " AND cat_url LIKE \"" . $this->owner->db->escapeString($this->values['cat_url']) . "\" AND cat_id != '" . $this->keyFields['cat_id'] . "'"
            );
            if (!empty($res)) {
                $this->addError('ERR_10015', self::FORM_ERROR, ['cat_url']);
            }
        }
    }

    public function onBeforeSave() {
        $this->values['cat_shop_id'] = $this->owner->shopId;

        if(Empty($this->values['cat_url'])) {
            $this->values['cat_url'] = safeURL($this->values['cat_title']);
        }else{
            $this->values['cat_url'] = safeURL($this->values['cat_url']);
        }
        $this->values['cat_url'] = strtolower($this->values['cat_url']);

        if($this->values['removeImg']){
            $this->deleteImage();
        }else {
            $this->uploadFile();
        }
    }

    public function onAfterSave($statement) {
        $this->owner->mem->delete(CACHE_CATEGORIES);
    }

    public function onAfterInit() {
        $this->setSubtitle($this->values['cat_title']);
    }

    public function onAfterLoadValues() {
        if(Empty($this->values['cat_page_img'])){
            $this->removeControl('removeImg');
        }
    }

    private function uploadFile(){
        if (!empty($_FILES[$this->name]['name']['upload_file']) && empty($_FILES[$this->name]['error']['upload_file'])) {
            $savePath = DIR_UPLOAD . $this->owner->shopId . '/products/' . $this->keyFields['cat_id'] . '/';
            $this->deleteImage();

            $pathParts = pathinfo($_FILES[$this->name]['name']['upload_file']);
            $this->values['cat_page_img'] = uuid::v4() . '.' . $pathParts['extension'];

            if(!is_dir($savePath)){
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
            }

            move_uploaded_file($_FILES[$this->name]['tmp_name']['upload_file'], $savePath . $this->values['cat_page_img']);
        } else {
            unset($this->values['cat_page_img']);
        }
    }

    private function deleteImage(){
        $savePath = DIR_UPLOAD . $this->owner->shopId . '/products/' . $this->keyFields['cat_id'] . '/';

        if(!Empty($this->values['cat_page_img']) && file_exists($savePath . $this->values['cat_page_img'])) {
            unlink($savePath . $this->values['cat_page_img']);
        }

        $this->values['cat_page_img'] = '';
    }
}
