<?php
class editPageForm extends formBuilder {
    private $parentTitle;
    private $parentUrl;

    public function setupKeyFields() {
        $this->setKeyFields(['c_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PAGE';
		$this->dbTable = 'contents';
        $this->upload = true;

		$general = (new sectionBox('general', 'LBL_PAGE_DETAILS', 'fal fa-file'))
                        ->addClass('col-12 col-lg-8')
                        ->addElements(
                            (new inputSelect('c_parent_id', 'LBL_PARENT_MENU'))
                                ->makeSelectPicker(true, 10)
                                ->setOptions($this->owner->lists->setEmptyItem('LBL_MAIN_MENU')->getTopPages($this->keyFields['c_id'])),
                            (new inputText('c_title', 'LBL_HEADLINE'))
                                ->setRequired(),
                            (new inputText('c_subtitle', 'LBL_PAGE_SUBTITLE'))
                        );

        $editor = (new sectionBox('editor', 'LBL_EDITOR', 'fal fa-pencil'))
            ->addClass('col-12 col-lg-8')
            ->addElements(
                //(new inputEditor('c_headline', 'LBL_HEADLINE')),
                (new inputEditor('c_content', 'LBL_CONTENT'))
                    ->hasGallery()
            );

        $properties = (new sectionBox('properties', 'LBL_PROPERTIES', 'fal fa-check-double'))
            ->addClass('col-12 col-lg-8')
            ->addElements(
                (new inputSwitch('c_published', 'LBL_PUBLISHED'))
                    ->setColor(enumColors::Danger())
                    ->setGroupClass('mb-0'),
                (new inputSwitch('c_empty_menu', 'LBL_EMPTY_MENU'))
                    ->setHelpText('LBL_HELP_EMPTY_MENU', true)
                    ->setColor(enumColors::Warning())
                    ->setGroupClass('mb-0'),
                (new inputSwitch('c_show_in_header', 'LBL_SHOW_IN_HEADER'))
                    ->setGroupClass('mb-0'),
                (new inputSwitch('c_show_in_footer', 'LBL_SHOW_IN_FOOTER')),
                (new groupRow('row1'))->addElements(
                    (new inputText('c_order', 'LBL_POSITION'))
                        ->setColSize('col-6 col-lg-3')
                        ->addClass('text-right')
                        ->onlyNumbers(),
                    (new inputSelect('c_widget', 'LBL_SPECIAL_PAGE_CONTENT', 'null'))
                        ->setOptions($this->owner->lists->reset()->getContentPageWidgets())
                        ->setColSize('col-6 col-lg-3')
                )
            );

        $this->addSections($general, $editor, $properties);

        if($this->keyFields['c_id']){
            $seo = (new sectionBox('seo', 'LBL_SEO', 'fab fa-google'))
                ->addClass('col-12 col-lg-8')
                ->addElements(
                    (new inputText('c_page_title', 'LBL_PAGE_TITLE')),
                    (new inputTextarea('c_page_description', 'LBL_PAGE_DESCRIPTION')),
                    (new inputText('c_page_url', 'LBL_PAGE_URL'))
                        ->setPrepend('https://' . HOST_CLIENTS . '/'),
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
                    (new previewImage('c_page_img'))
                        //->setSize(200, 200)
                        ->setResponsive(true)
                        ->setPath(FOLDER_UPLOAD . $this->owner->shopId . '/pages/' . $this->keyFields['c_id'] . '/'),
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
        if (!empty($this->values['c_page_url'])) {
            $res = $this->owner->db->getFirstRow(
                "SELECT c_id FROM " . DB_NAME_WEB . ".contents WHERE c_shop_id = " . $this->owner->shopId . " AND c_page_url LIKE \"" . $this->owner->db->escapeString($this->values['c_page_url']) . "\" AND c_id != '" . $this->keyFields['c_id'] . "'"
            );
            if (!empty($res)) {
                $this->addError('ERR_10015', self::FORM_ERROR, ['c_page_url']);
            }
        }
    }

    public function onBeforeSave() {
        $this->values['c_shop_id'] = $this->owner->shopId;

        if(Empty($this->values['c_page_url'])) {
            $this->values['c_page_url'] = safeURL($this->values['c_title']);
        }else{
            $this->values['c_page_url'] = safeURL($this->values['c_page_url']);
        }

        if(Empty($this->values['c_order'])) $this->values['c_order'] = 0;
        if(Empty($this->values['c_empty_menu'])) $this->values['c_empty_menu'] = 0;
        if(Empty($this->values['c_show_in_header'])) $this->values['c_show_in_header'] = 0;
        if(Empty($this->values['c_show_in_footer'])) $this->values['c_show_in_footer'] = 0;
        if(Empty($this->values['c_published'])) $this->values['c_published'] = 0;
        if($this->values['c_widget'] == 'null' || Empty($this->values['c_widget'])) {
            $this->values['c_widget'] = null;
        }else{
            $this->values['c_order'] = -1;
        }

        if($this->values['removeImg']){
            $this->deleteImage();
        }else {
            $this->uploadFile();
        }
    }

    public function onAfterInit() {
        $this->getParent();
        $this->setSubtitle(($this->parentTitle ? $this->parentTitle . ' / ' : '') . $this->values['c_title']);
    }

    public function onAfterLoadValues() {
        if(Empty($this->values['c_page_img'])){
            $this->removeControl('removeImg');
        }

        if($this->values['c_parent_id']){
            $this->removeControl('c_show_in_header');
            $this->removeControl('c_show_in_footer');
            $this->removeControl('c_empty_menu');
            $this->removeControl('c_widget');
        }elseif(!Empty($this->values['c_widget'])){
            $this->removeControl('c_show_in_header');
            $this->removeControl('c_show_in_footer');
            $this->removeControl('c_empty_menu');
            $this->removeControl('c_order');
        }
    }

    public function onAfterSave($statement) {
        if($this->values['c_widget']){
            $this->owner->mem->delete(CACHE_PAGES . $this->owner->shopId . $this->owner->language . $this->values['c_widget']);
        }else {
            $this->owner->mem->delete(CACHE_PAGES . $this->owner->shopId . $this->owner->language);
        }
    }

    private function uploadFile(){
        if (!empty($_FILES[$this->name]['name']['upload_file']) && empty($_FILES[$this->name]['error']['upload_file'])) {
            $savePath = DIR_UPLOAD . $this->owner->shopId  . '/pages/' . $this->keyFields['c_id'] . '/';
            $this->deleteImage();

            $pathParts = pathinfo($_FILES[$this->name]['name']['upload_file']);
            $this->values['c_page_img'] = uuid::v4() . '.' . $pathParts['extension'];

            if(!is_dir($savePath)){
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
            }

            move_uploaded_file($_FILES[$this->name]['tmp_name']['upload_file'], $savePath . $this->values['c_page_img']);
        } else {
            unset($this->values['c_page_img']);
        }
    }

    private function deleteImage(){
        $savePath = DIR_UPLOAD . $this->owner->shopId . '/pages/' . $this->keyFields['c_id'] . '/';

        if(!Empty($this->values['c_page_img']) && file_exists($savePath . $this->values['c_page_img'])) {
            unlink($savePath . $this->values['c_page_img']);
        }

        $this->values['c_page_img'] = '';
    }

    private function getParent(){
        if($this->values['c_parent_id']){
            $res = $this->owner->db->getFirstRow(
                "SELECT c_title, c_page_url FROM " . DB_NAME_WEB . ".contents WHERE c_shop_id = " . $this->owner->shopId . " AND c_id = '" . $this->values['c_parent_id'] . "'"
            );
            if (!empty($res)) {
                $this->parentTitle = $res['c_title'];
                $this->parentURL = $res['c_page_url'];

                $this->getControl('c_page_url')
                    ->setPrepend('https://' . HOST_CLIENTS . '/' . $this->parentURL . '/');
            }
        }
    }
}
