<?php
class bulkEditProductsForm extends formBuilder {
    private $productIds = [];

    public function setupKeyFields() {
        $this->setKeyFields([]);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PRODUCTS';

        $this->productIds = $this->owner->getSession('products-selections');

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function saveValues() {
        if($this->productIds){
            $data = [];

            /*
            $checkboxes = [];
            foreach($this->values AS $field => $value){
                if(!Empty($value['change'])){
                    if(in_array($field, $checkboxes)) {
                        $data[$field] = (int) $value['value'];
                    }else {
                        $data[$field] = $value['value'];
                    }
                }
            }
            */

            if($data) {
                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLUpdate(
                        'products',
                        $data,
                        [
                            'prod_id' => [
                                'in' => $this->productIds
                            ],
                            'prod_shop_id' => $this->owner->shopId
                        ]
                    )
                );
            }

            $this->owner->setSession('products-selections', false);
        }
    }
}
