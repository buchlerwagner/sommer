<?php
class addProductForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['prod_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PRODUCT';
		$this->dbTable = 'products';

        $group = (new groupFieldset('general'))->addElements(
            (new inputSelect('prod_cat_id', 'LBL_CATEGORY'))
                ->makeSelectPicker()
                ->setOptions($this->owner->lists->getCategories())
                ->setRequired(),
            (new inputText('prod_name', 'LBL_PRODUCT_TITLE'))
                ->setRequired()
        );

        $this->addControls($group);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['prod_shop_id'] = $this->owner->shopId;
        $this->values['prod_visible'] = 0;
        $this->values['prod_available'] = 0;
        $this->values['prod_key'] = uuid::v4();
        $this->values['prod_code'] = '';
        $this->values['prod_created'] = 'NOW()';

        if(Empty($this->values['prod_url'])) {
            $this->values['prod_url'] = safeURL($this->values['prod_name']);
        }else{
            $this->values['prod_url'] = safeURL($this->values['prod_url']);
        }

        $this->values['prod_url'] = strtolower($this->values['prod_url']);
    }

    public function onAfterSave($statement) {
        if($statement == 'insert' && $this->keyFields['prod_id']) {
            $this->returnData['frm']['functions']['callback'] = 'pageRedirect';
            $this->returnData['frm']['functions']['arguments'] = '/webshop/products/edit|products/' . $this->keyFields['prod_id'] . '/';
        }
    }
}
