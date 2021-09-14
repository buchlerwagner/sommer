<?php
class myProfileForm extends form {
	public function setup() {
		$this->dbTable = DB_NAME_WEB . '.users';
		$this->keyFields['us_id'] = $this->owner->user->id;
		$this->owner->view->setPageTitle('LBL_EDIT_PROFILE');
		$this->displayErrors = true;
        $this->boxed = false;

        $this->extraFields = [
            'us_hash' => [
                'field' => 'us_hash',
                'exclude' => true,
            ],
            'us_img' => [
                'field'   => 'us_img',
                'exclude' => true,
            ],
        ];

		$this->controls = [
            0 => [
                'fieldset' => false,
                'col' => 6,
                'group' => [
                    0 => [
                        'id'      => 'us_title',
                        'type'    => 'select',
                        'name'    => 'us_title',
                        'label'   => 'LBL_PERSON_TITLE',
                        'class'   => 'col-4',
                        'options' => $this->owner->lib->getList('titles'),
                        'default' => 'MR',
                        'col'     => 12,
                    ],
                    1 => [
                        'id'      => 'us_lastname',
                        'type'    => 'text',
                        'name'    => 'us_lastname',
                        'label'   => 'LBL_LASTNAME',
                        'class'   => 'col-12',
                        'default' => '',
                        'col'     => 12,
                        'constraints' => [
                            'required' => true
                        ]
                    ],
                    2 => [
                        'id'      => 'us_firstname',
                        'type'    => 'text',
                        'name'    => 'us_firstname',
                        'label'   => 'LBL_FIRSTNAME',
                        'class'   => 'col-12',
                        'default' => '',
                        'col'     => 12,
                        'constraints' => [
                            'required' => true
                        ]
                    ],
                    /*
                    3 => [
                        'id'      => 'us_birth_place',
                        'type'    => 'text',
                        'name'    => 'us_birth_place',
                        'label'   => 'LBL_BIRTH_PLACE',
                        'class'   => 'col-12',
                        'default' => '',
                        'col'     => 6,
                        'constraints' => [
                            'required' => true
                        ]
                    ],
                    4 => [
                        'id'      => 'us_birth_date',
                        'type'    => 'date',
                        'name'    => 'us_birth_date',
                        'label'   => 'LBL_BIRTH_DATE',
                        'class'   => 'col-12',
                        'default' => '',
                        'col'     => 6,
                        'icon'        => 'far fa-calendar-alt',
                        'data'    => [
                            'dateformat' => $this->locale['dateformat'],
                            'calendars' => 1,
                            'change-month' => 'true',
                            'change-year' => 'true',
                            'max-date' => date('y-m-d'),
                            'language' => $this->owner->language,
                        ],
                        'constraints' => [
                            'required' => true
                        ]
                    ],
                    5 => [
                        'id'      => 'us_mother_name',
                        'type'    => 'text',
                        'name'    => 'us_mother_name',
                        'label'   => 'LBL_MOTHER_NAME',
                        'class'   => 'col-12',
                        'default' => '',
                        'col'     => 12,
                        'constraints' => [
                            'required' => true
                        ]
                    ],
                    */
                ]
            ],
            1 => [
                'fieldset' => false,
                'col' => 6,
                'group' => [
                    0 => [
                        'include' => 'user-profile-img',
                        'col' => '12 d-flex justify-content-center',
                        'data' => [
                            'src' => '',
                        ]
                    ]
                ]
            ],
            2 => [
                'fieldset' => 'LBL_CONTACT',
                'col' => 12,
                'group' => [
                    0 => [
                        'id'      => 'us_email',
                        'type'    => 'text',
                        'name'    => 'us_email',
                        'label'   => 'LBL_EMAIL',
                        'class'   => 'col-6',
                        'default' => '',
                        'col'     => 12,
                        'constraints' => [
                            'required' => true
                        ]
                    ],
                    1 => [
                        'id'      => 'us_phone',
                        'type'    => 'text',
                        'name'    => 'us_phone',
                        'label'   => 'LBL_PHONE',
                        'class'   => 'col-6',
                        'default' => '',
                        'col'     => 12,
                        'constraints' => [
                            'required' => true
                        ]
                    ],
                ]
            ],
            3 => [
                'fieldset' => 'LBL_ADDRESS',
                'col' => 12,
                'group' => [
                    0 => [
                        'id'      => 'us_country',
                        'type'    => 'select',
                        'name'    => 'us_country',
                        'label'   => 'LBL_COUNTRY',
                        'class'   => 'col-6',
                        'default' => 'HU',
                        'options' => $this->owner->lib->getList('countries'),
                        'col'     => 12,
                    ],
                    1 => [
                        'id'      => 'us_zip',
                        'type'    => 'text',
                        'name'    => 'us_zip',
                        'label'   => 'LBL_ZIP',
                        'class'   => 'col-12',
                        'default' => '',
                        'col'     => 2,
                    ],
                    2 => [
                        'id'      => 'us_city',
                        'type'    => 'text',
                        'name'    => 'us_city',
                        'label'   => 'LBL_CITY',
                        'class'   => 'col-12',
                        'default' => '',
                        'col'     => 4,
                    ],
                    3 => [
                        'id'      => 'us_address',
                        'type'    => 'text',
                        'name'    => 'us_address',
                        'label'   => 'LBL_ADDRESS',
                        'class'   => 'col-6',
                        'default' => '',
                        'col'     => 12,
                    ],
                ]
            ],
            4 => [
                'fieldset' => 'LBL_TIMEZONE',
                'col' => 12,
                'group' => [
                    0 => [
                        'id'      => 'us_timezone',
                        'type'    => 'select',
                        'name'    => 'us_timezone',
                        //'label'   => 'LBL_TIMEZONE',
                        'options' => $this->owner->lib->getList('timezone'),
                        'default' => $GLOBALS['HOSTS'][$this->owner->host]['timezone'],
                        'col'     => 12,
                        'class'   => 'selectpicker show-tick col-12 col-lg-6',
                        'data' => [
                            'size' => 10,
                            'live-search' => 'true'
                        ],
                        'constraints' => [
                            'required' => true
                        ]
                    ],
                ]
            ],
		];

		$this->buttons = [
			'save' => [
				'id'    => 'save',
				'type'  => 'submit',
				'class' => 'primary btn-progress',
				'name'  => 'save',
				'label' => 'BTN_SAVE'
			],
		];
	}

	public function onAfterInit() {
        if(Empty($this->values['us_img'])){
            $imgSrc = '/images/' . strtolower($this->values['us_title']) . '.svg';
        }else{
            $imgSrc = FOLDER_UPLOAD . '/profiles/' . $this->values['us_img'];
        }

        $this->controls[1]['group'][0]['data']['src'] = $imgSrc;
        $this->controls[1]['group'][0]['data']['filename'] = $this->values['us_img'];
    }

	public function onAfterLoadValues() {
        if(!$this->values['us_birth_date']) $this->values['us_birth_date'] = '';
	}

	public function onBeforeSave() {
	    if(!$this->values['us_birth_date']){
            $this->values['us_birth_date'] = null;
        }

        unset($this->values['us_img']);
    }

    public function onAfterSave($statement) {
        $this->owner->pageRedirect('/my-profile/');
    }
}
