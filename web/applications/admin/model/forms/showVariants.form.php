<?php
class showVariantsForm extends formBuilder {
    private $item;

    public function setupKeyFields() {
        $this->setKeyFields(['productId', 'cartId']);
    }

    public function setup() {
        $this->boxed = true;

        $this->addControls(
           (new inputRadioList('variantId'))
            ->setOptions($this->getVariants())
        );

        $this->customModalButtons = true;
        $this->addButtons(
            new buttonModalClose('btn-close', 'BTN_CANCEL'),
            new buttonModalSave('btn-save', 'BTN_ADD_TO_CART')
        );
	}

    public function saveValues() {
        if(Empty($this->values['variantId'])){
            $this->state = FORM_STATE_INVALID;
            $this->addError('ERR_SELECT_PRODUCT', self::FORM_ERROR, []);
        }else{
            $this->owner->cartHandler->init($this->keyFields['cartId'], false);
            $this->owner->cartHandler->addProduct($this->keyFields['productId'], $this->values['variantId'], 1);
        }
    }

    private function getVariants(){
        $out = [];

        /**
         * @var $product product
         */
        $product = $this->owner->addByClassName('product');
        $this->item = $product->init($this->keyFields['productId'])->getProduct();

        if($this->item['variants']){
            foreach($this->item['variants'] AS $variant){
                $out[$variant['id']] = [
                    'text' => $variant['name'] . '<div class="text-muted float-right mt-3">' . $this->owner->lib->formatPrice($variant['price']['value'], $variant['price']['currency']) . $variant['price']['unit'] . '</div>',
                    'width' => 50,
                    'image' => $this->findImage($variant['imgId']),
                ];
            }
        }

        return $out;
    }

    private function findImage($id){
        $img = false;

        if($id && $this->item['images']){
            foreach($this->item['images'] AS $image){
                if($image['data']['id'] == $id){
                    if($image['data']['thumbnail']){
                        $img = $image['data']['thumbnail'];
                    }else {
                        $img = $image['file'];
                    }
                    break;
                }
            }
        }

        return $img;
    }
}
