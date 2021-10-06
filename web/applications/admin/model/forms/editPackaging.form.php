<?php
class editPackagingForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['pkg_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PACKAGING';
		$this->dbTable = 'packagings';

        $group = (new groupFieldset('general'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputText('pkg_name', 'LBL_NAME'))
                    ->setColSize('col-12')
                    ->setRequired()
            ),
            (new groupRow('row2'))->addElements(
                (new inputText('pkg_price', 'LBL_FEE', 0))
                    ->setColSize('col-12 col-lg-5')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setAppend($this->owner->currencySign . ' / ' . $this->owner->translate->getTranslation('LBL_PCS'))
            )
        );

        $this->addControls($group);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['pkg_shop_id'] = $this->owner->shopId;
    }
}
