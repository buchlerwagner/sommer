<?php
class contactForm extends formBuilder {
    public $item;

    public function setupKeyFields() {

    }

    public function setup() {
        $this->addControls(
            (new inputText('name', 'LBL_NAME'))
                ->setRequired(),
            (new inputText('email', 'LBL_EMAIL'))
                ->setRequired(),
            (new inputText('phone', 'LBL_PHONE'))
                ->onlyNumbers('+'),
            (new inputTextarea('message', 'LBL_MESSAGE'))
                ->setRequired(),
            (new inputCheckbox('agree_privacy', 'LBL_I_AGREE_PRIVACY_POLICY', 0))
                ->setRequired(),

            (new inputHidden('itemId'))
        );

        $this->useCaptcha('contact');

        $this->addButtons(
            new buttonSave()
        );
    }

    public function onAfterInit() {
        if (!isset($_REQUEST[$this->name])) {
            if(!Empty($_REQUEST['item'])){
                $this->values['itemId'] = (int) $_REQUEST['item'];
            }

            if($this->owner->user->isLoggedIn()) {
                if ($user = $this->owner->user->getUser()) {
                    $this->values['name'] = $user['name'];
                    $this->values['email'] = $user['email'];
                    $this->values['phone'] = $user['phone'];
                }
            }
        }

        $this->getItem();
    }

    public function onValidate() {
        if (empty($this->values['name'])) {
            $this->addError('ERR_1000', self::FORM_ERROR, ['name']);
        }
        if (empty($this->values['phone'])) {
            $this->addError('ERR_1000', self::FORM_ERROR, ['phone']);
        }
        if (empty($this->values['email'])) {
            $this->addError('ERR_1000', self::FORM_ERROR, ['email']);
        }elseif(!checkEmail($this->values['email'])){
            $this->addError('ERR_1000', self::FORM_ERROR, ['email']);
        }
        if (empty($this->values['message'])) {
            $this->addError('ERR_1000', self::FORM_ERROR, ['message']);
        }

        if (empty($this->values['agree_privacy'])) {
            $this->addError('ERR_2004', self::FORM_ERROR, ['agree_privacy']);
        }
    }

    public function saveValues() {
        $itemHtml = '';
        $this->getItem();

        $this->owner->email->prepareEmail(
            'contact-reply',
            [
                'email' => $this->values['email'],
                'name' => $this->values['name'],
            ],
            [
                'name' => $this->values['name'],
                'email' => $this->values['email'],
            ]
        );

        if($this->values['itemId']) {
            $data = [
                'itemName' => $this->item['name'],
                'itemImage' => $this->owner->domain . $this->item['thumbnail'],
                'itemPublicUrl' => $this->owner->domain . $this->item['url'],
                'itemAdminUrl' => rtrim($this->owner->hostConfig['publicSite'], '/') . '/webshop/products/edit|products/' . $this->item['id'] . '/',
            ];
            $itemHtml = $this->owner->view->renderContent('mail-request-item', $data, false, false);
        }

        $this->owner->email->prepareEmail(
            'contact',
            [
                'email' => $this->owner->settings['incomingEmail'],
            ],
            [
                'id' => generateRandomString(8),
                'name' => $this->values['name'],
                'email' => $this->values['email'],
                'phone' => $this->values['phone'],
                'message' => nl2br($this->values['message']),
                'item' => $itemHtml,
            ],
            false,
            [],
            [],
            [],
            $this->values['email']
        );

        $this->values['prod_id'] = (int) $this->values['itemId'];
        if(!$this->values['prod_id']) $this->values['prod_id'] = 0;
        unset($this->values['token'], $this->values['agree_terms'], $this->values['itemId']);

        if($user = $this->owner->user->isLoggedIn()) {
            $this->values['us_id'] = $user['id'];
        }
        $this->values['shop_id'] = $this->owner->shopId;
        $this->values['timestamp'] = 'NOW()';
        $this->values['recaptcha'] = json_encode($this->getRecaptchaResponse());

        $data = [];
        unset($this->values['agree_privacy']);

        foreach ($this->values as $key => $value) {
            $data['r_' . $key] = $value;
        }

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLInsert(
                'requests',
                $data
            )
        );

        $this->owner->pageRedirect($this->owner->getPageName('contact') . '?success');
    }

    public function getItem(){
        if($this->values['itemId']){
            /**
             * @var $product product
             */
            $product = $this->owner->addByClassName('product');
            $this->item = $product->init($this->values['itemId'])->getProduct();
        }
    }

}