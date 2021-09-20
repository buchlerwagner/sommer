<?php
class addPageForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['c_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PAGE';
		$this->dbTable = 'contents';

        $group = (new groupFieldset('card-general'))->addElements(
            (new inputSelect('c_parent_id', 'LBL_PARENT'))
                ->makeSelectPicker(true, 10)
                ->setOptions($this->owner->lists->setEmptyItem('LBL_NONE')->getTopPages()),
            (new inputText('c_title', 'LBL_PAGE_TITLE'))
                ->setRequired(),
            (new inputText('c_page_url', 'LBL_PAGE_URL'))
                ->setPrepend('https://' . HOST_CLIENTS . '/'),
            (new groupRow('row1'))->addElements(
                (new inputSwitch('c_show_in_header', 'LBL_SHOW_IN_HEADER', 1))
                    ->setGroupClass('mb-0')
                    ->setColSize('col-12'),
                (new inputSwitch('c_show_in_footer', 'LBL_SHOW_IN_FOOTER', 0))
                    ->setColSize('col-12')
            )
        );

        $this->addControls($group);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {
        if (!empty($this->values['c_page_url'])) {
            $this->values['c_page_url'] = strtolower(safeURL($this->values['c_page_url']));

            $res = $this->owner->db->getFirstRow(
                "SELECT c_id FROM " . DB_NAME_WEB . ".contents WHERE c_shop_id = " . $this->owner->shopId . " AND c_page_url LIKE \"" . $this->owner->db->escapeString($this->values['c_page_url']) . "\""
            );
            if (!empty($res)) {
                $this->addError('ERR_10015', self::FORM_ERROR, ['c_page_url']);
            }
        }
    }

    public function onBeforeSave() {
        $this->values['c_shop_id'] = $this->owner->shopId;
        $this->values['c_published'] = 0;
        $this->values['c_timestamp'] = 'NOW()';
        $this->values['c_order'] = $this->getMaxOrder();

        if(Empty($this->values['c_page_url'])) {
            $this->values['c_page_url'] = safeURL($this->values['c_title']);
        }else{
            $this->values['c_page_url'] = safeURL($this->values['c_page_url']);
        }

        $this->values['c_page_url'] = strtolower($this->values['c_page_url']);
        $res = $this->owner->db->getFirstRow(
            "SELECT c_id FROM " . DB_NAME_WEB . ".contents WHERE c_shop_id = " . $this->owner->shopId . " AND c_page_url LIKE \"" . $this->owner->db->escapeString($this->values['c_page_url']) . "\""
        );
        if (!empty($res)) {
            $this->values['c_page_url'] .= '-' . rand(10000, 99999);
        }

        $this->values['c_page_title'] = $this->values['c_title'];

        if(Empty($this->values['c_show_in_header'])) $this->values['c_show_in_header'] = 0;
        if(Empty($this->values['c_show_in_footer'])) $this->values['c_show_in_footer'] = 0;
    }

    private function getMaxOrder(){
        $order = 0;
        $row = $this->owner->db->getFirstRow(
          $this->owner->db->genSQLSelect(
              $this->dbTable,
              [
                  'MAX(c_order) AS maxOrder'
              ],
              [
                  'c_shop_id' => $this->owner->shopId,
                  'c_parent_id' => (int)$this->values['c_parent_id']
              ]
          )
        );
        if($row){
            $order = (int)$row['maxOrder'];
        }

        return ++$order;
    }

    public function onAfterSave($statement) {
        if($statement == 'insert' && $this->keyFields['c_id']) {
            $this->returnData['frm']['functions']['callback'] = 'pageRedirect';
            $this->returnData['frm']['functions']['arguments'] = '/settings/content/pages/edit|pages/' . $this->keyFields['c_id'] . '/';
        }
    }
}
