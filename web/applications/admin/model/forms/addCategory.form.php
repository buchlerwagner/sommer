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
                ->setPrepend('https://' . HOST_CLIENTS . '/termekek/'),
            (new groupRow('row1'))->addElements(
                (new inputText('cat_order', 'LBL_POSITION', $this->getMaxOrder()))
                    ->setColSize('col-12 col-lg-3')
                    ->addClass('text-right')
                    ->onlyNumbers()
            )
        );

        $this->addControls($group);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['cat_visible'] = 0;

        if(Empty($this->values['cat_url'])) {
            $this->values['cat_url'] = safeURL($this->values['cat_title']);
        }else{
            $this->values['cat_url'] = safeURL($this->values['cat_url']);
        }

        $this->values['cat_url'] = strtolower($this->values['cat_url']);
    }

    private function getMaxOrder(){
        $order = 0;
        $row = $this->owner->db->getFirstRow(
          $this->owner->db->genSQLSelect(
              'product_categories',
              [
                  'MAX(cat_order) AS maxOrder'
              ]
          )
        );
        if($row){
            $order = (int)$row['maxOrder'];
        }

        return ++$order;
    }
}
