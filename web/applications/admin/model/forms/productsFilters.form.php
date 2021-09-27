<?php
class productsFiltersForm extends filterForm {
    public function setupKeyFields() {
    }

    public function setup() {
		parent::setup();

        $this->customRights = ACCESS_RIGHT_WRITE;
        $this->parentTable = 'products';

        $this->addControls(
            (new groupRow('row'))->addElements(
                (new inputText('freeText', 'LBL_FREE_TEXT'))
                    ->setColSize('col-12 col-sm-3'),
                (new inputSelect('prod_cat_id', 'LBL_CATEGORY'))
                    ->makeSelectPicker()
                    ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getCategories(true))
                    ->setColSize('col-12 col-sm-3')
            )
        );
    }

}
