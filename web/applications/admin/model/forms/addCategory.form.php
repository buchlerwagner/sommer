<?php
class addCategoryForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['cat_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_CATEGORY';
		$this->dbTable = 'product_categories';

        $group = (new groupFieldset('card-general'))->addElements(
            (new inputText('cat_title', 'LBL_TITLE'))->setRequired(),
            (new inputText('cat_url', 'LBL_URL'))
                ->setPrepend($this->owner->hostConfig['publicSite'] . $GLOBALS['PAGE_NAMES'][$this->owner->language]['products']['name'] . '/'),
            (new groupRow('row1'))->addElements(
                (new inputText('cat_order', 'LBL_POSITION', $this->getMaxOrder()))
                    ->setColSize('col-12 col-lg-3')
                    ->addClass('text-right')
                    ->onlyNumbers()
            ),
            (new inputSwitch('cat_smart', 'LBL_COLLECT_BY_TAGS'))
                ->changeState(1, enumChangeAction::Show(), '#smart')
                ->changeDefaultState(enumChangeAction::Hide(), '#smart'),
            (new groupFieldset('smart'))->addElements(
                (new inputCheckGroup('cat_tags', 'LBL_TAGS'))
                    ->setColor(enumColors::Primary())
                    ->setOptions($this->owner->lists->getProperties())
            )
        );

        $this->addControls($group);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {
        if (!empty($this->values['cat_url'])) {
            $this->values['cat_url'] = strtolower(safeURL($this->values['cat_url']));

            $res = $this->owner->db->getFirstRow(
                "SELECT cat_id FROM " . DB_NAME_WEB . ".product_categories WHERE cat_shop_id = " . $this->owner->shopId . " AND cat_url LIKE \"" . $this->owner->db->escapeString($this->values['cat_url']) . "\""
            );
            if (!empty($res)) {
                $this->addError('ERR_10015', self::FORM_ERROR, ['cat_url']);
            }
        }
    }

    public function onBeforeSave() {
        $this->values['cat_shop_id'] = $this->owner->shopId;
        $this->values['cat_visible'] = 0;

        if(Empty($this->values['cat_url'])) {
            $this->values['cat_url'] = safeURL($this->values['cat_title']);
        }else{
            $this->values['cat_url'] = safeURL($this->values['cat_url']);
        }

        $res = $this->owner->db->getFirstRow(
            "SELECT cat_id FROM " . DB_NAME_WEB . ".product_categories WHERE cat_shop_id = " . $this->owner->shopId . " AND cat_url LIKE \"" . $this->owner->db->escapeString($this->values['cat_url']) . "\""
        );
        if (!empty($res)) {
            $this->values['cat_url'] .= '-' . rand(10000, 99999);
        }
    }

    private function getMaxOrder(){
        $order = 0;
        $row = $this->owner->db->getFirstRow(
          $this->owner->db->genSQLSelect(
              $this->dbTable,
              [
                  'MAX(cat_order) AS maxOrder'
              ],
              [
                  'cat_shop_id' => $this->owner->shopId
              ]
          )
        );
        if($row){
            $order = (int)$row['maxOrder'];
        }

        return ++$order;
    }
}
