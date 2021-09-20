<?php
class editVariantForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['pv_id', 'pv_prod_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_VARIANT';
		$this->dbTable = 'product_variants';

        $pricing = (new groupFieldset('pricing', ''))->addElements(
            (new inputText('pv_name', 'LBL_NAME'))
                ->setRequired(),

            (new inputSelect('pv_pimg_id', 'LBL_PRODUCT_IMAGE'))
                ->makeSelectPicker(false)
                ->setOptions($this->owner->lists->reset()->setEmptyItem('LBL_NONE')->getProductImages($this->keyFields['pv_prod_id'])),

            (new groupRow('row1'))->addElements(
                (new inputText('pv_price', 'LBL_PRICE', 0))
                    ->setColSize('col-6')
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend($this->owner->currencySign),
                (new inputText('pv_price_discount', 'LBL_DISCOUNT_PRICE', 0))
                    ->setColSize('col-6')
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend($this->owner->currencySign)
            ),
            (new groupRow('row2'))->addElements(
                (new inputSelect('pv_pack_unit', 'LBL_PACKAGE_UNIT'))
                    ->addClass('change-label')
                    ->setColSize('col-6')
                    ->setOptions($this->owner->lists->reset()->getUnits()),
                (new inputText('pv_pack_quantity', 'LBL_PACKAGE_QUANTITY', 1))
                    ->setColSize('col-6')
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend('LBL_PACKAGE_QUANTITY_UNIT')
            ),
            (new groupRow('row3'))->addElements(
                (new inputText('pv_stock', 'LBL_PRODUCT_STOCK', 0))
                    ->setHelpText('LBL_STOCK_HELP_TEXT')
                    ->setColSize('col-4')
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend('LBL_PCS'),
                (new inputText('pv_weight', 'LBL_PRODUCT_WEIGHT', 0))
                    ->setColSize('col-4')
                    ->onlyNumbers()
                    ->addClass('text-right'),
                (new inputSelect('pv_weight_unit', false))
                    ->setColSize('col-4')
                    ->addEmptyLabel()
                    ->setOptions($this->owner->lists->reset()->getWeights())
            ),
            (new groupRow('row4'))->addElements(
                (new inputText('pv_min_sale', 'LBL_PRODUCT_MIN_SALE', 1))
                    ->setColSize('col-6')
                    ->onlyNumbers()
                    ->setAppend('LBL_PCS')
                    ->addClass('has-label')
                    ->addClass('text-right'),
                (new inputText('pv_max_sale', 'LBL_PRODUCT_MAX_SALE', 0))
                    ->setHelpText('LBL_MAX_SALE_HELP_TEXT')
                    ->setColSize('col-6')
                    ->onlyNumbers()
                    ->setAppend('LBL_PCS')
                    ->addClass('has-label')
                    ->addClass('text-right')
            )
        );

        $this->addControls($pricing);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onAfterInit() {
        $units = $this->owner->lists->getUnits();
        $this->getControl('pv_min_sale')->setAppend($units[$this->values['pv_pack_unit']]);
        $this->getControl('pv_max_sale')->setAppend($units[$this->values['pv_pack_unit']]);
    }

    public function onBeforeSave() {
        $this->values['pv_currency'] = $this->owner->currency;

        if(Empty($this->values['pv_price'])) $this->values['pv_price'] = 0;
        if(Empty($this->values['pv_price_discount'])) $this->values['pv_price_discount'] = 0;
        if(Empty($this->values['pv_stock'])) $this->values['pv_stock'] = 0;
        if(Empty($this->values['pv_weight'])) $this->values['pv_weight'] = 0;
        if(Empty($this->values['pv_weight_unit'])) $this->values['pv_weight_unit'] = 0;

        if(Empty($this->values['pv_allow_oversell'])) $this->values['pv_allow_oversell'] = 0;
        if(Empty($this->values['pv_physical'])) $this->values['pv_physical'] = 0;
    }

}
