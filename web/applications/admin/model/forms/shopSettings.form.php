<?php
class shopSettingsForm extends formBuilder {

    public function setupKeyFields() {
    }

    public function setup() {
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
                    ->setName('settings/stopSaleText')
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
                    ->setName('settings/incomingEmail')
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

        $this->addSections($mainOptions, $contact, $menu, $openingHours, $analytics, $social);

        $this->hideSidebar();

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function saveValues() {
        if(Empty($this->values['settings']['stopSale'])) $this->values['settings']['stopSale'] = 0;

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
   }
}
