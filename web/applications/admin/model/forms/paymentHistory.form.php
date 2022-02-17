<?php
class paymentHistoryForm extends formBuilder {
    public function setupKeyFields() {
        $this->setKeyFields(['cart_id']);
    }

    public function setup() {
        $this->title = 'LBL_VIEW_PAYMENT_HISTORY';

        $this->addControls(
            (new subTable('history'))
                ->addClass('table-responsive')
                ->add($this->loadSubTable('paymentHistoryList'))
        );

        $this->customModalButtons = true;
        $this->addButtons(
            new buttonModalClose('btn-close', 'BTN_CLOSE', 'btn btn-light float-right')
        );
	}
}
