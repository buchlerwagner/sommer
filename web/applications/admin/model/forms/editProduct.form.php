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

        $general = (new groupFieldset('general'))->addElements(
            (new inputSelect('prod_cat_id', 'LBL_CATEGORY'))
                ->makeSelectPicker()
                ->setOptions($this->owner->lists->getCategories())
                ->setRequired(),
            (new inputText('prod_name', 'LBL_PRODUCT_TITLE'))
                ->setRequired(),
            (new inputText('prod_brand_name', 'LBL_PRODUCT_BRAND_NAME'))
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
                (new inputSelect('prod_pack_unit', 'LBL_PACKAGE_UNIT'))
                    ->setColSize('col-12 col-lg-3')
                    ->setOptions($this->owner->lists->reset()->getUnits()),
                (new inputText('prod_pack_quantity', 'LBL_PACKAGE_QUANTITY', 1))
                    ->setColSize('col-12 col-lg-3')
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend('LBL_PACKAGE_QUANTITY_UNIT')
            ),
            (new groupRow('row4'))->addElements(
                (new inputText('prod_stock', 'LBL_PRODUCT_STOCK', 0))
                    ->setColSize('col-4 col-lg-3')
                    ->setHelpText('LBL_STOCK_HELP_TEXT')
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend('LBL_PCS'),
                (new inputText('prod_weight', 'LBL_PRODUCT_WEIGHT', 0))
                    ->setColSize('col-4 col-lg-1')
                    ->onlyNumbers()
                    ->addClass('text-right'),
                (new inputSelect('prod_weight_unit', false))
                    ->setColSize('col-4 col-lg-2')
                    ->addEmptyLabel()
                    ->setOptions($this->owner->lists->reset()->getWeights())

            )
        );

        $variations = (new groupFieldset('variations', ''))->addElements(
            (new groupRow('row4'))->addElements(
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

        $tabGeneral = (new sectionBox('general', 'LBL_PRODUCT_DETAILS', 'far fa-birthday-cake'))
            ->addClass('col-12 col-lg-8')
            ->addElements($general);

        $tabPricing = (new sectionBox('pricing', 'LBL_PRODUCT_PRICING', 'far fa-money-bill-alt'))
            ->addClass('col-12 col-lg-8')
            ->addElements($pricing, $variations);


        $tabDescription = (new sectionBox('description', 'LBL_PRODUCT_DESCRIPTION', 'far fa-book-open'))
            ->addClass('col-12 col-lg-8')
            ->addElements(
                new inputEditor('prod_description')
            );

        $tabProperties = (new sectionBox('properties', 'LBL_PRODUCT_PROPERTIES', 'far fa-tags'))
            ->addClass('col-12 col-lg-8')
            ->addElements(
                (new inputCheckbox('prod_visible', 'LBL_VISIBLE', 0))
                    ->setColor(enumColors::Primary()),
                (new inputCheckbox('prod_available', 'LBL_PRODUCT_AVAILABLE', 0))
                    ->setColor(enumColors::Primary()),

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
                    ->setPrepend('https://' . HOST_CLIENTS . '/termekek/' . $this->categoryURL . '/' . $this->keyFields['prod_id'] . '-')
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

        $this->values['prod_url'] = strtolower($this->values['prod_url']);

        if(!Empty($this->values['prod_properties'])){
            $this->product->setProperties($this->values['prod_properties']);
        }

        unset($this->values['prod_properties']);

        if($this->values['prod_variants']) {
            $this->values['prod_price'] = 0;
            $this->values['prod_stock'] = 0;
            $this->values['prod_price_discount'] = 0;
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
}
