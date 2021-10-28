<?php
class editProductForm extends formBuilder {
    private $categoryId;
    private $categoryTitle;
    private $categoryURL;

    /**
     * @var $product product
     */
    private $product;

    public function setupKeyFields() {
        $this->setKeyFields(['prod_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PRODUCT';
		$this->dbTable = 'products';
		$this->formWidth = 'col-12 col-lg-8';

        $url = $this->owner->hostConfig['publicSite'] . $GLOBALS['PAGE_NAMES'][$this->owner->language]['products']['name'] . '/' . $this->categoryURL . '/' . $this->keyFields['prod_id'] . '-';

        $general = (new groupFieldset('general'))->addElements(
            (new inputSelect('prod_cat_id', 'LBL_CATEGORY'))
                ->makeSelectPicker()
                ->setOptions($this->owner->lists->reset()->getCategories())
                ->setRequired(),
            (new inputText('prod_name', 'LBL_PRODUCT_TITLE'))
                ->setRequired(),
            (new inputText('prod_brand_name', 'LBL_PRODUCT_BRAND_NAME')),
            (new groupRow('row7'))->addElements(
                (new inputText('prod_code', 'LBL_PRODUCT_ARTICLE_NUMBER'))
                    ->setColSize('col-6 col-lg-4')
            ),
            (new link('preview', 'LBL_PREVIEW_PRODUCT', $url ))
                ->setGroupClass('mb-0')
                ->setTarget('_blank')
                ->setIcon('fas fa-eye')
        );

        $variations = (new groupFieldset('variations', ''))->addElements(
            (new groupRow('row6'))->addElements(
                (new inputSwitch('prod_variants', 'LBL_PRODUCT_HAS_VARIANTS', 0))
                    ->changeState(1, enumChangeAction::Hide(), '#pricing')
                    ->changeState(1, enumChangeAction::Show(), '#product-variants')
                    ->changeState(0, enumChangeAction::Show(), '#pricing')
                    ->changeState(0, enumChangeAction::Hide(), '#product-variants')
                    ->changeDefaultState(enumChangeAction::Show(), '#pricing')
                    ->setColSize('col-12')
            ),
            (new groupRow('product-variants'))
                ->addClass('d-none')
                ->addElements(
                    (new subTable('variants'))
                        ->addClass('table-responsive')
                        ->add($this->loadSubTable('productVariants')
                        )
                )
        );

        $pricing = (new groupFieldset('pricing', ''))->addElements(
            (new groupRow('row2'))->addElements(
                (new inputText('prod_price', 'LBL_PRICE', 0))
                    ->setColSize('col-6 col-lg-3')
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend($this->owner->currencySign),
                (new inputText('prod_price_discount', 'LBL_DISCOUNT_PRICE', 0))
                    ->setColSize('col-6 col-lg-3')
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend($this->owner->currencySign)
            ),
            (new groupRow('row3'))->addElements(
                (new inputText('prod_pack_quantity', 'LBL_PACKAGE_QUANTITY', 1))
                    ->setColSize('col-6 col-lg-2')
                    ->setGroupClass('pr-0')
                    ->onlyNumbers()
                    ->addClass('text-right'),
                (new inputSelect('prod_pack_pcs_unit'))
                    ->addEmptyLabel()
                    ->setGroupClass('pl-0')
                    ->setColSize('col-6 col-lg-2')
                    ->setOptions($this->owner->lists->reset()->getUnits()),

                (new groupHtml('txt', '<div class="mt-4 pt-2 d-none d-lg-block">/</div>')),

                (new inputSelect('prod_pack_unit', 'LBL_PACKAGE_UNIT'))
                    ->addClass('change-label')
                    ->setColSize('col-12 col-lg-2')
                    ->setOptions($this->owner->lists->reset()->getUnits())
            ),
            (new groupRow('row4'))->addElements(
                (new inputText('prod_stock', 'LBL_PRODUCT_STOCK', 0))
                    ->setColSize('col-4 col-lg-2')
                    ->setHelpText('LBL_STOCK_HELP_TEXT')
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend('LBL_PCS'),
                (new inputText('prod_weight', 'LBL_PRODUCT_WEIGHT', 0))
                    ->setColSize('col-4 col-lg-2')
                    ->onlyNumbers()
                    ->addClass('text-right'),
                (new inputSelect('prod_weight_unit', false))
                    ->setColSize('col-4 col-lg-2')
                    ->addEmptyLabel()
                    ->setAppend('LBL_PACKAGE_QUANTITY_UNIT')
                    ->setOptions($this->owner->lists->reset()->getWeights())
            ),
            (new groupRow('row5'))->addElements(
                (new inputText('prod_min_sale', 'LBL_PRODUCT_MIN_SALE', 1))
                    ->setColSize('col-12 col-lg-3')
                    ->onlyNumbers()
                    ->setAppend('LBL_PCS')
                    ->addClass('has-label')
                    ->addClass('text-right'),
                (new inputText('prod_max_sale', 'LBL_PRODUCT_MAX_SALE', 0))
                    ->setColSize('col-12 col-lg-3')
                    ->setHelpText('LBL_MAX_SALE_HELP_TEXT')
                    ->onlyNumbers()
                    ->setAppend('LBL_PCS')
                    ->addClass('has-label')
                    ->addClass('text-right')
            ),
            (new groupRow('row8'))->addElements(
                (new inputSelect('prod_pkg_id', 'LBL_PACKAGE_FEE', 0))
                    ->setColSize('col-12 col-lg-6')
                    ->setOptions($this->owner->lists->reset()->setEmptyItem('LBL_NONE')->getPackagingOptions())
            )
        );


        $tabGeneral = (new sectionBox('general', 'LBL_PRODUCT_DETAILS', 'far fa-birthday-cake'))
            ->addClass('col-12 col-lg-8')
            ->addElements($general);

        $tabPricing = (new sectionBox('pricing', 'LBL_PRODUCT_PRICING', 'far fa-money-bill-alt'))
            ->addClass('col-12 col-lg-8')
            ->addElements($variations, $pricing);


        $tabDescription = (new sectionBox('description', 'LBL_DESCRIPTIONS', 'far fa-book-open'))
            ->addClass('col-12 col-lg-8')
            ->addElements(
                (new inputTextarea('prod_intro', 'LBL_PRODUCT_INTRO'))
                    ->setRows(3),
                new inputEditor('prod_description', 'LBL_PRODUCT_DESCRIPTION')
            );

        $tabProperties = (new sectionBox('properties', 'LBL_PRODUCT_PROPERTIES', 'far fa-tags'))
            ->addClass('col-12 col-lg-8')
            ->addElements(
                (new inputSwitch('prod_visible', 'LBL_VISIBLE', 0))
                    ->setGroupClass('mb-0')
                    ->setColor(enumColors::Primary()),
                (new inputSwitch('prod_available', 'LBL_PRODUCT_AVAILABLE', 0))
                    ->setGroupClass('mb-0')
                    ->setColor(enumColors::Warning()),
                (new inputSwitch('prod_highlight', 'LBL_PRODUCT_HIGHLIGHT', 0))
                    ->setColor(enumColors::Success()),

                (new inputCheckGroup('prod_properties', 'LBL_PRODUCT_TAGS'))
                    ->setColor(enumColors::Primary())
                    ->setOptions($this->owner->lists->getProperties())
                    ->notDBField()
            );

        $tabImages = (new sectionBox('images', 'LBL_PRODUCT_IMAGES', 'far fa-images'))
            ->addClass('col-12 col-lg-8')
            ->addElements(
                (new inputFileUploader('fileUpload'))
                    ->setAllowedExtensions(['jpg', 'jpeg', 'png'])
                    ->setAjaxEndPoint('/ajax/uploader/product/' . $this->keyFields['prod_id'])
                    ->addMore(true)
                    ->setTheme('thumbnails')
                    ->setLimit(FILEUPLOAD_MAX_FILES)
                    ->setFileMaxSize(FILEUPLOAD_MAX_FILESIZE)
                    ->hasView(true, 'LBL_VIEW')
                    ->hasDelete(true, 'LBL_DELETE')
                    ->hasEdit(true, 'LBL_EDIT')
                    ->hasSort(true, 'LBL_SORT')
                    ->preloadFiles($this->product->getImages())
            );

        $tabSeo = (new sectionBox('seo', 'LBL_SEO', 'fab fa-google'))
            ->addClass('col-12 col-lg-8')
            ->addElements(
                (new inputText('prod_page_title', 'LBL_PAGE_TITLE')),
                (new inputTextarea('prod_page_description', 'LBL_PAGE_DESCRIPTION')),
                (new inputText('prod_url', 'LBL_PAGE_URL'))
                    ->setPrepend($url)
        );

        $this->addSections($tabGeneral, $tabPricing, $tabDescription, $tabProperties, $tabImages, $tabSeo);
        $this->hideSidebar();

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSetup(){
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'products',
                [
                    'cat_id',
                    'cat_title',
                    'cat_url',
                ],
                [
                    'prod_id' => $this->keyFields['prod_id']
                ],
                [
                    'product_categories' => [
                        'on' => [
                            'cat_id' => 'prod_cat_id'
                        ]
                    ]
                ]
            )
        );
        if($row){
            $this->categoryId = $row['cat_id'];
            $this->categoryTitle = $row['cat_title'];
            $this->categoryURL = $row['cat_url'];
        }

        $this->product = $this->owner->addByClassName('product');
        $this->product->init($this->keyFields['prod_id']);
    }

    public function onAfterLoadValues() {
        $this->values['prod_properties'] = array_keys($this->product->getProperties());

        $units = $this->owner->lists->getUnits();
        $this->getControl('prod_min_sale')->setAppend($units[$this->values['prod_pack_unit']]);
        $this->getControl('prod_max_sale')->setAppend($units[$this->values['prod_pack_unit']]);

        $url = $this->owner->hostConfig['publicSite'] . $GLOBALS['PAGE_NAMES'][$this->owner->language]['products']['name'] . '/' . $this->categoryURL . '/' . $this->keyFields['prod_id'] . '-' . $this->values['prod_url'] . '?show=all';
        $this->getControl('preview')->setUrl($url);
    }

    public function onAfterInit() {
        $this->setSubtitle($this->categoryTitle . ' / ' . $this->values['prod_name']);
    }

    public function onBeforeSave() {
        $this->values['prod_shop_id'] = $this->owner->shopId;

        if(Empty($this->values['prod_url'])) {
            $this->values['prod_url'] = safeURL($this->values['prod_name']);
        }else{
            $this->values['prod_url'] = safeURL($this->values['prod_url']);
        }

        if(!Empty($this->values['prod_properties'])){
            $this->product->setProperties($this->values['prod_properties']);
        }

        unset($this->values['prod_properties']);

        if($this->values['prod_variants']) {
            $this->getLowestVariantPrice();

            $this->values['prod_stock'] = 0;
            $this->values['prod_weight'] = 0;
        }else{
            $this->values['prod_variants'] = 0;
        }

        if(Empty($this->values['prod_price'])) $this->values['prod_price'] = 0;
        if(Empty($this->values['prod_stock'])) $this->values['prod_stock'] = 0;
        if(Empty($this->values['prod_price_discount'])) $this->values['prod_price_discount'] = 0;
        if(Empty($this->values['prod_weight'])) $this->values['prod_weight'] = 0;
        if(Empty($this->values['prod_pack_quantity'])) $this->values['prod_pack_quantity'] = 0;
        if(Empty($this->values['prod_pack_unit'])) $this->values['prod_pack_unit'] = 0;
        if(Empty($this->values['prod_visible'])) $this->values['prod_visible'] = 0;
        if(Empty($this->values['prod_available'])) $this->values['prod_available'] = 0;

        if(Empty($this->values['prod_page_title'])){
            $this->values['prod_page_title'] = $this->values['prod_name'];
        }
    }

    private function getLowestVariantPrice(){
        $this->values['prod_price'] = 0;
        $this->values['prod_price_discount'] = 0;

        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'product_variants',
                [
                    'MIN(pv_price) AS price'
                ],
                [
                    'pv_prod_id' => $this->keyFields['prod_id']
                ]
            )
        );
        if($row){
            $this->values['prod_price'] = $row['price'];
        }

        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'product_variants',
                [
                    'MIN(pv_price_discount) AS price'
                ],
                [
                    'pv_prod_id' => $this->keyFields['prod_id'],
                    'pv_price_discount' => [
                        'greater' => 0
                    ],
                ]
            )
        );
        if($row){
            $this->values['prod_price_discount'] = $row['price'];
        }
    }
}
