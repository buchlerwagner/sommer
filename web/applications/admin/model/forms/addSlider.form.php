<?php
class addSliderForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['s_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_SLIDER';
		$this->dbTable = 'sliders';

        $group = (new groupFieldset('card-general'))->addElements(
            (new inputText('s_title', 'LBL_HEADLINE'))->setRequired(),
            (new groupRow('row1'))->addElements(
                (new inputText('s_order', 'LBL_POSITION', $this->getMaxOrder()))
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
        $this->values['s_shop_id'] = $this->owner->shopId;
        $this->values['s_visible'] = 0;
    }

    private function getMaxOrder(){
        $order = 0;
        $row = $this->owner->db->getFirstRow(
          $this->owner->db->genSQLSelect(
              $this->dbTable,
              [
                  'MAX(s_order) AS maxOrder'
              ],
              [
                  's_shop_id' => $this->owner->shopId
              ]
          )
        );
        if($row){
            $order = (int)$row['maxOrder'];
        }

        return ++$order;
    }

    public function onAfterSave($statement) {
        if($statement == 'insert' && $this->keyFields['prod_id']) {
            $this->returnData['frm']['functions']['callback'] = 'pageRedirect';
            $this->returnData['frm']['functions']['arguments'] = '/settings/content/sliders/edit|sliders/' . $this->keyFields['s_id'] . '/';
        }
    }

}
