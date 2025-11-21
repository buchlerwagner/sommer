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
                ->setPrepend($this->owner->hostConfig['publicSite'] . $GLOBALS['PAGE_NAMES'][$this->owner->language]['products']['name'] . '/'),
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

        $stopSale = (new sectionBox('stop-sale', 'LBL_SALE_LIMITATIONS', 'fas fa-hand-paper'))
            ->addClass('col-12 col-lg-6')
            ->addElements(
                (new inputSwitch('cat_only_in_stores', 'LBL_ONLY_IN_STORES', 0))
                    ->setColor(enumColors::Primary()),

                (new inputSwitch('cat_express', 'LBL_EXPRESS_PRODUCTS', 0))
                    ->setColor(enumColors::Primary()),

                (new inputSwitch('cat_limit_sale', 'LBL_CATEGORY_LIMIT_SALE', 0))
                    ->changeState(0, enumChangeAction::Readonly(), '#cat_limit_sale_text, #cat_date_start, #cat_date_end')
                    ->changeState(1, enumChangeAction::Editable(), '#cat_limit_sale_text, #cat_date_start, #cat_date_end')
                    ->changeState(1, enumChangeAction::Disable(), '#cat_stop_sale')
                    ->changeState(1, enumChangeAction::Enable(), '.takeover-days')
                    ->changeState(0, enumChangeAction::Enable(), '#cat_stop_sale')
                    ->changeState(0, enumChangeAction::Disable(), '.takeover-days')
                    ->setColor(enumColors::Warning()),
                (new inputText('cat_limit_sale_text', 'LBL_LIMIT_SALE_TEXT')),
                (new groupRow('row2'))->addElements(
                    (new inputDate('cat_date_start', 'LBL_DATE_START'))
                        ->setColSize('col-6 col-lg-4')
                        ->setIcon('fas fa-calendar-alt'),
                    (new inputDate('cat_date_end', 'LBL_DATE_END'))
                        ->setColSize('col-6 col-lg-4')
                        ->setIcon('fas fa-calendar-alt')
                ),
                (new groupRow('row3'))->addElements(
                    (new inputCheckGroup('cat_takeover_days', 'LBL_TAKEOVER_DAYS'))
                        ->addClass('takeover-days')
                        ->setOptions($this->owner->lists->getDaysOfWeek())
                        ->setColSize('col-6 col-lg-4')
                ),
                (new groupRow('row4'))->addElements(
                    (new inputText('cat_included_dates', 'LBL_ADDITIONAL_DATES'))
                        ->setColSize('col-12')
                ),
                (new inputSwitch('cat_stop_sale', 'LBL_CATEGORY_STOP_SALE', 0))
                    ->setColor(enumColors::Danger())
                    ->changeState(1, enumChangeAction::Disable(), '#cat_limit_sale')
                    ->changeState(0, enumChangeAction::Enable(), '#cat_limit_sale')
            );

        $this->addSections($general, $stopSale);


        $smartCategory = (new sectionBox('smart-group', 'LBL_SMART_CATEGORY', 'fa fa-layer-group'))
            ->addClass('col-12 col-lg-6')
            ->addElements(
                (new inputSwitch('cat_smart', 'LBL_COLLECT_BY_TAGS'))
                    ->changeState(1, enumChangeAction::Show(), '#smart')
                    ->changeDefaultState(enumChangeAction::Hide(), '#smart'),
                (new groupFieldset('smart'))->addElements(
                    (new inputCheckGroup('cat_tags', 'LBL_TAGS'))
                        ->setColor(enumColors::Primary())
                        ->setOptions($this->owner->lists->getProperties())
                )
            );

        $this->addSections($smartCategory);

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

        if($this->values['cat_limit_sale']){
            if(Empty($this->values['cat_limit_sale_text'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['cat_limit_sale_text']);
            }

            if(Empty($this->values['cat_takeover_days'])) {
                if (empty($this->values['cat_date_start'])) {
                    $this->addError('ERR_1000', self::FORM_ERROR, ['cat_date_start']);
                }
                if (empty($this->values['cat_date_end'])) {
                    $this->addError('ERR_1000', self::FORM_ERROR, ['cat_date_end']);
                }

                if ($this->values['cat_date_end'] < $this->values['cat_date_start']) {
                    $this->addError('ERR_1000', self::FORM_ERROR, ['cat_date_end']);
                }
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

        if(Empty($this->values['cat_only_in_stores'])) {
            $this->values['cat_only_in_stores'] = 0;
        }else{
            $this->values['cat_limit_sale'] = 0;
            $this->values['cat_stop_sale'] = 0;
        }

        if(Empty($this->values['cat_smart'])){
            $this->values['cat_smart'] = 0;
            $this->values['cat_tags'] = '';
        }

        if(Empty($this->values['cat_limit_sale'])) {
            $this->values['cat_limit_sale'] = 0;
            $this->values['cat_takeover_days'] = '';
        }else{
            $this->values['cat_stop_sale'] = 0;
        }

        if(Empty($this->values['cat_stop_sale'])) {
            $this->values['cat_stop_sale'] = 0;
        }else{
            $this->values['cat_limit_sale'] = 0;
        }

        if(Empty($this->values['cat_express'])) $this->values['cat_express'] = 0;

        if(Empty($this->values['cat_date_start'])) $this->values['cat_date_start'] = null;
        if(Empty($this->values['cat_date_end'])) $this->values['cat_date_end'] = null;

        if(!Empty($this->values['cat_included_dates'])){
            $dates = str_replace([';', '|', ' '], ',', $this->values['cat_included_dates']);
            $dates = explode(',', $dates);
            $this->values['cat_included_dates'] = [];
            foreach ($dates as $date) {
                $date = standardDate(trim($date));
                if(!Empty($date) && !in_array($date, $this->values['cat_included_dates']) && $date >= date('Y-m-d')) {
                    $this->values['cat_included_dates'][] = $date;
                }
            }

            $this->values['cat_included_dates'] = json_encode($this->values['cat_included_dates']);
        }

        if($this->values['removeImg']){
            $this->deleteImage();
        }else {
            $this->uploadFile();
        }

        unset($this->values['removeImg']);
    }

    public function onAfterSave($statement) {
        $this->owner->mem->delete(CACHE_CATEGORIES . $this->owner->shopId);
    }

    public function onAfterInit() {
        $this->setSubtitle($this->values['cat_title']);

        if(Empty($this->values['cat_page_img'])){
            $this->removeControl('removeImg');
        }

        if(!Empty($this->values['cat_included_dates'])) {
            $this->values['cat_included_dates'] = json_decode($this->values['cat_included_dates'], true);
            $this->values['cat_included_dates'] = implode(', ', $this->values['cat_included_dates']);
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
