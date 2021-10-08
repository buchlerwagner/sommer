<?php
class shopSettingsForm extends formBuilder {
    const LOGO_TYPES = [
        'main'      => 'logo-main.svg',
        'alt'       => 'logo-alt.svg',
        'white'     => 'logo-white.svg',
        'mail'      => 'logo-mail.png',
        'favicon'   => 'favicon.png',
    ];

    public function setupKeyFields() {
    }

    public function setup() {
        $this->upload = true;

        $mainOptions = (new sectionBox('main', 'LBL_WEBSHOP_GENERAL_SETTINGS', 'far fa-cogs'))
            ->addElements(
                (new inputText('shopName', 'LBL_SHOP_NAME'))
                    ->setIcon('far fa-store')
                    ->setName('settings/shopName'),
                (new inputSwitch('stopSale', 'LBL_STOP_SALE', 0))
                    ->setColor(enumColors::Danger())
                    ->changeState(0, enumChangeAction::Readonly(), '#stopSaleText')
                    ->changeState(1, enumChangeAction::Editable(), '#stopSaleText')
                    ->setName('settings/stopSale'),
                (new inputText('stopSaleText', 'LBL_STOP_SALE_TEXT'))
                    ->setName('settings/stopSaleText'),

                (new groupRow('row0'))->addElements(
                    (new inputSelect('itemsOnPage', 'LBL_ITEMS_PON_PAGE', 12))
                        ->setColSize('col-4 col-lg-4')
                        ->setName('settings/itemsOnPage')
                        ->setOptions($this->owner->lists->reset()->numberRange(1, 100))
                )
            );

        $menu = (new sectionBox('menu', 'LBL_MENU_SETTINGS', 'far fa-bars'))
            ->addElements(
                (new inputText('menuBreakPoint', 'LBL_BREAK_POINT'))
                    ->setColSize('col-4 col-lg-3')
                    ->setIcon('far fa-page-break')
                    ->onlyNumbers()
                    ->setMaxLength(1)
                    ->addClass('text-right')
                    ->setName('settings/menuBreakPoint')
            );

        $contact = (new sectionBox('contact', 'LBL_CONTACT_DATA', 'far fa-envelope'))
            ->addElements(
                (new inputText('emailSenderName', 'LBL_SENDER_NAME'))
                    ->setPlaceholder('LBL_SHOP_NAME')
                    ->setIcon('far fa-address-card')
                    ->setName('settings/emailSenderName'),
                (new inputText('outgoingEmail', 'LBL_OUTGOING_EMAIL_ADDRESS'))
                    ->setPlaceholder('noreply@company.com')
                    ->setIcon('far fa-inbox-out')
                    ->setName('settings/outgoingEmail'),
                (new inputText('incomingEmail', 'LBL_INCOMING_EMAIL_ADDRESS'))
                    ->setPlaceholder('orders@company.com')
                    ->setIcon('far fa-inbox-in')
                    ->setName('settings/incomingEmail'),
                (new inputText('address', 'LBL_ADDRESS'))
                    ->setIcon('far fa-map-marker-alt')
                    ->setName('settings/address'),
                (new inputText('addressAlt', 'LBL_ADDRESS_ALT'))
                    ->setIcon('far fa-map-marker-alt')
                    ->setName('settings/addressAlt'),
                (new inputText('phone', 'LBL_PHONE'))
                    ->setIcon('far fa-phone-alt')
                    ->setName('settings/phone'),
                (new inputText('phoneAlt', 'LBL_PHONE'))
                    ->setIcon('far fa-phone-alt')
                    ->setName('settings/phoneAlt')
            );

        $openingHours = new sectionBox('opening-hours', 'LBL_OPENING_HOURS', 'far fa-clock');

        for($i=1; $i<=7; $i++){
            $openingHours->addElements(
                (new groupRow('row' . $i))->addElements(
                    (new groupHtml('day' . $i, '<div class="col-3 col-lg-2 pt-2 text-right">{{ _("LBL_DAY_' . $i . '")  }}</div>')),
                    (new inputText('oo-day' . $i . '-from'))
                        ->setColSize('col-4 col-lg-3')
                        //->onlyNumbers(':')
                        ->setMaxLength(5)
                        ->addClass('text-right')
                        ->setAppend('tól')
                        ->setName('settings/oo-day' . $i . '-from'),
                    (new groupHtml('dash' . $i, '<div class="col-1 pt-2 text-center">-</div>')),
                    (new inputText('oo-day' . $i . '-to'))
                        ->setColSize('col-4 col-lg-3')
                        //->onlyNumbers(':')
                        ->setMaxLength(5)
                        ->addClass('text-right')
                        ->setAppend('ig')
                        ->setName('settings/oo-day' . $i . '-to')
                )
            );
        }

        $openingHours->addElements(
            (new inputTextarea('opening-hours-info', 'LBL_OPENING_HOURS_INFO'))
                ->setRows(2)
                ->setIcon('far fa-comment-alt-edit')
                ->setName('settings/opening-hours-info')
        );

        $analytics = (new sectionBox('trackers', 'LBL_ANALYTICS', 'far fa-analytics'))
            ->addElements(
                (new inputText('googleAnalytics', 'LBL_GOOGLE_ANALYTICS_ID'))
                    ->setIcon('fab fa-google')
                    ->setName('settings/googleAnalytics'),
                (new inputText('googleMapsAPI', 'LBL_GOOGLE_MAPS_API_KEY'))
                    ->setIcon('far fa-map-marker-alt')
                    ->setName('settings/googleMapsAPI'),

                (new inputSwitch('captcha', 'LBL_GOOGLE_CAPTCHA', 0))
                    ->setColor(enumColors::Warning())
                    ->setGroupClass('mb-0')
                    ->changeState(0, enumChangeAction::Readonly(), '#googleSiteKey, #googleSecret')
                    ->changeState(1, enumChangeAction::Editable(), '#googleSiteKey, #googleSecret')
                    ->setName('settings/captcha'),
                (new inputText('googleSiteKey', 'LBL_GOOGLE_SITE_KEY'))
                    ->setIcon('far fa-key')
                    ->setName('settings/googleSiteKey'),
                (new inputText('googleSecret', 'LBL_GOOGLE_SECRET'))
                    ->setIcon('far fa-key')
                    ->setName('settings/googleSecret'),

                (new inputText('facebookAppId', 'LBL_FACEBOOK_APP_ID'))
                    ->setIcon('fab fa-facebook')
                    ->setName('settings/facebookAppId')
            );

        $social = (new sectionBox('social', 'LBL_SOCIAL_MEDIA', 'far fa-share-alt'))
            ->addElements(
                (new inputText('facebook', 'LBL_FACEBOOK_PAGE'))
                    ->setIcon('fab fa-facebook')
                    ->setName('settings/facebook'),
                (new inputText('twitter', 'LBL_TWITTER_PAGE'))
                    ->setIcon('fab fa-twitter')
                    ->setName('settings/twitter'),
                (new inputText('pinterest', 'LBL_PINTEREST_PAGE'))
                    ->setIcon('fab fa-pinterest')
                    ->setName('settings/pinterest'),
                (new inputText('instagram', 'LBL_INSTAGRAM_PAGE'))
                    ->setIcon('fab fa-instagram')
                    ->setName('settings/instagram'),
                (new inputText('youtube', 'LBL_YOUTUBE_PAGE'))
                    ->setIcon('fab fa-youtube')
                    ->setName('settings/youtube')
            );

        $cookieBar = (new sectionBox('cookiebar', 'LBL_GDPR', 'far fa-cookie'))
            ->addElements(
                (new inputSwitch('cookieBar', 'LBL_COOKIE_COMPLIANCE', 0))
                    ->changeState(0, enumChangeAction::Readonly(), '#cookieBarText, #cookieBarAcceptButton')
                    ->changeState(1, enumChangeAction::Editable(), '#cookieBarText, #cookieBarAcceptButton')
                    ->setName('settings/cookieBar'),

                (new inputText('cookieBarText', 'LBL_COOKIE_COMPLIANCE_TEXT'))
                    ->setName('settings/cookieBarText'),
                (new inputText('cookieBarAcceptButton', 'LBL_ACCEPT_BUTTON_TEXT'))
                    ->setName('settings/cookieBarAcceptButton')
            );

        $logo = new sectionBox('logo', 'LBL_LOGO', 'far fa-sign');

        $i = 0;
        foreach(self::LOGO_TYPES AS $type => $fileName){
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);

            $logo->addElements(
                (new inputFile('logo/' . $type, 'LBL_LOGO_' . strtoupper($type)))
                    ->setHelpText($this->owner->translate->getTranslation('LBL_ALLOWED_FILETYPES', $ext))
                    ->addData('max-file-size', 10240)
                    ->addData('theme', 'fas')
                    ->addData('show-upload', 'false')
                    ->addData('show-caption', 'true')
                    ->addData('show-remove', 'false')
                    ->addData('show-cancel', 'false')
                    ->addData('show-close', 'false')
                    ->addData('allowed-file-extensions', '["' . $ext . '"]')
                    ->addData('show-preview', 'false')
                    ->notDBField(),
                (new previewImage('img_logo_' . $type))
                    ->setGroupClass(($type == 'white' ? 'bg-dark' : ''))
                    ->setSize(200)
                    ->setResponsive(true)
                    ->setPath(FOLDER_UPLOAD . $this->owner->shopId . '/'),
                (new inputCheckbox('remove_' . $type, 'LBL_REMOVE_IMAGE', 0))
                    ->notDBField(),
                new groupHtml('div-' . $i++, '<hr>')
            );
        }

        $this->addSections($mainOptions, $contact, $menu, $openingHours, $analytics, $cookieBar, $logo, $social);

        $this->hideSidebar();

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {
        if(!Empty($this->values['settings']['stopSale'])) {
            if(Empty($this->values['settings']['stopSaleText'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['stopSaleText']);
            }
        }

        if(!Empty($this->values['settings']['cookieBar'])) {
            if(Empty($this->values['settings']['cookieBarText'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['cookieBarText']);
            }
            if(Empty($this->values['settings']['cookieBarAcceptButton'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['cookieBarAcceptButton']);
            }
        }
    }

    public function saveValues() {
        $this->uploadFiles();

        if(Empty($this->values['settings']['stopSale'])) $this->values['settings']['stopSale'] = 0;
        if(Empty($this->values['settings']['cookieBar'])) $this->values['settings']['cookieBar'] = 0;
        if(Empty($this->values['settings']['captcha'])) $this->values['settings']['captcha'] = 0;

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLInsert(
                'webshop_settings',
                [
                    'ws_shop_id' => $this->owner->shopId,
                    'ws_settings' => json_encode($this->values['settings'])
                ],
                [
                    'ws_shop_id'
                ]
            )
        );

        $this->owner->mem->delete(CACHE_SETTINGS . $this->owner->shopId);

        $this->owner->addMessage(router::MESSAGE_SUCCESS, '', 'LBL_DATA_SAVED_SUCCESSFULLY');
        $this->owner->pageRedirect('/webshop/settings/');
    }

    public function onAfterInit() {
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'webshop_settings',
                [
                    'ws_settings'
                ],
                [
                    'ws_shop_id' => $this->owner->shopId,
                ]
            )
        );
        if($row){
            $this->values['settings'] = json_decode($row['ws_settings'], true);
        }

        $path = DIR_UPLOAD . $this->owner->shopId . '/';
        foreach(self::LOGO_TYPES AS $type => $fileName){
            if(file_exists($path . $fileName)){
                $this->values['img_logo_' . $type] = $fileName;
            }
        }
   }

    private function uploadFiles(){
        $savePath = DIR_UPLOAD . $this->owner->shopId . '/';
        if(!is_dir($savePath)){
            @mkdir($savePath, 0777, true);
            @chmod($savePath, 0777);
        }

        foreach(self::LOGO_TYPES AS $type => $fileName){
            if(!Empty($this->values['remove_' . $type])) {
                if (file_exists($savePath . $fileName)) {
                    @unlink($savePath . $fileName);
                }
            }
            unset($this->values['remove_' . $type]);
            unset($this->values['img_logo_' . $type]);

            if (!empty($_FILES[$this->name]['name']['logo'][$type]) && empty($_FILES[$this->name]['error']['logo'][[$type]])) {
                move_uploaded_file($_FILES[$this->name]['tmp_name']['logo'][$type], $savePath . $fileName);
            }
        }
    }
}
