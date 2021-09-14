<?php
class lists extends ancestor {
    private $list = [];
    private $options = [];
    private $json = false;
    private $emptyListLabel = false;
    private $emptyListValue = 0;

    public function getTitles(){
        $list = array_combine($GLOBALS['PERSONAL_TITLES'], $GLOBALS['PERSONAL_TITLES']);
        return $this->setList($list)->getList();
    }

    public function getCurrencies($withSign = true){
        $this->setList([
            'HUF' => ($withSign ? 'Ft' : 'HUF'),
            'EUR' => ($withSign ? '€' : 'EUR'),
            'USD' => ($withSign ? '$' : 'USD'),
        ]);

        return $this->getList();
    }

    public function getVat(){
        $this->setList([
            '0' => '0%',
            '5' => '5%',
            '18' => '18%',
            '27' => '27%',
            'AAM' => 'AAM',
            'TAM' => 'TAM',
        ]);

        return $this->getList();
    }

    public function getColors(){
        $this->setList(enumColors::toArray());

        return $this->getList();
    }

    public function getCarTypes(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'car_types',
                [
                    'ct_id AS list_key',
                    'ct_make AS list_group',
                    //'ct_make AS list_subtext',
                    'ct_make AS list_tokens',
                    'ct_model AS list_value',
                    'CONCAT(ct_make, " ", ct_model) AS list_title',
                    'CONCAT(ct_make, " - ", ct_model) AS list_order'
                ],
                [],
                [],
                [],
                [
                    'list_value'
                ]
            )
        );

        return $this->getList();
    }

    public function getCars($params = false){
        $where = [];

        $join = [
            'car_types' => [
                'on' => [
                    'ct_id' => 'f_ct_id'
                ]
            ]
        ];

        $groupBy = false;

        if($params['ug_id']){
            $where['us_ug_id'] = $params['ug_id'];

            $join['user_fleet'] = [
                'on' => [
                    'uf_f_id' => 'f_id',
                    'uf_revoked' => 0
                ]
            ];

            $join['users'] = [
                'on' => [
                    'us_id' => 'uf_us_id',
                ]
            ];

            $groupBy = 'f_id';
        }

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'fleet',
                [
                    'f_id AS list_key',
                    'f_licenceplate AS list_value',
                    'CONCAT(ct_make, " - ", ct_model) AS list_group',
                    'CONCAT(ct_make, " ", ct_model, " ", f_licenceplate) AS list_tokens',
                    'CONCAT(ct_make, " - ", ct_model, " ", f_licenceplate) AS list_order'
                ],
                $where,
                $join,
                $groupBy,
                [
                    'list_order'
                ]
            )
        );
        return $this->getList();
    }

    public function getLeasableCars(){
        $this->sqlQuery(
             $this->owner->db->genSQLSelect(
                'fleet',
                [
                    'f_id AS list_key',
                    'f_licenceplate AS list_value',
                    'CONCAT(ct_make, " - ", ct_model) AS list_group',
                    'CONCAT(ct_make, " ", ct_model, " ", f_licenceplate) AS list_tokens',
                    'CONCAT(ct_make, " - ", ct_model, " ", f_licenceplate) AS list_order'
                ],
                [
                    'lc_id' => [
                        'is' => 'NULL'
                    ]
                ],
                [
                    'car_types' => [
                        'on' => [
                            'ct_id' => 'f_ct_id'
                        ]
                    ],
                    'leasing_cars' => [
                        'on' => [
                            'lc_f_id' => 'f_id'
                        ]
                    ]
                ],
                [],
                [
                    'list_order'
                ]
            )
        );
        return $this->getList();
    }

    public function getAssignableCarsForContracts($ucfid = false){
        $excludeCars = [];
        $allow = 0;

        if($ucfid){
            $row = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'user_contract_fleet',
                    [
                        'ucf_f_id'
                    ],
                    [
                        'ucf_id' => (int)$ucfid
                    ]
                )
            );
            if($row){
                $allow = $row['ucf_f_id'];
            }
        }

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'user_contract_fleet',
                [
                    'ucf_f_id'
                ],
                [],
                [],
                'ucf_f_id'
            )
        );
        if($result){
            foreach ($result AS $row){
                if(!in_array($row['ucf_f_id'], $excludeCars) && $row['ucf_f_id'] != $allow) {
                    $excludeCars[] = $row['ucf_f_id'];
                }
            }
        }

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'fleet',
                [
                    'f_id AS list_key',
                    'CONCAT(ct_make, " ", ct_model, " (", f_licenceplate, ")") AS list_value',
                    'ct_make AS list_group',
                    'CONCAT(ct_make, " ", ct_model, " ", f_licenceplate) AS list_tokens',
                    'CONCAT(ct_make, " - ", ct_model, " ", f_licenceplate) AS list_order'
                ],
                'f_use_mode = 0' . (!Empty($excludeCars) ? ' AND f_id NOT IN (' . implode(',', $excludeCars) . ')' : ''),
                [
                    'car_types' => [
                        'on' => [
                            'ct_id' => 'f_ct_id'
                        ]
                    ],
                    'user_contract_fleet' => [
                        'on' => [
                            'ucf_f_id' => 'f_id',
                        ]
                    ]
                ],
                'f_id',
                [
                    'list_order'
                ]
            )
        );
        return $this->getList();
    }

    public function getAssignableCars($usid = false){
        $availableCars = [];

        if($usid){
            $row = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'users',
                    [
                        'us_ug_id'
                    ],
                    [
                        'us_id' => (int)$usid,
                    ]
                )
            );
            $userGroup = (int)$row['us_ug_id'];

            /**
             * Cars in contract
             */
            $result = $this->owner->db->getRows(
                $this->owner->db->genSQLSelect(
                    'user_contract_fleet',
                    [
                        'ucf_f_id',
                    ],
                    [
                        'uc_ug_id' => $userGroup
                    ],
                    [
                        'user_contracts' => [
                            'on' => [
                                'ucf_uc_id' => 'uc_id'
                            ]
                        ]
                    ]
                )
            );
            if($result){
                foreach ($result AS $row){
                    $availableCars[] = $row['ucf_f_id'];
                }
            }

            /**
             * Add group cars
             */
            $result = $this->owner->db->getRows(
                $this->owner->db->genSQLSelect(
                    'fleet',
                    [
                        'uf_id',
                        'uf_revoked',
                        'f_id',
                    ],
                    [
                        'f_use_mode' => 2
                    ],
                    [
                        'user_fleet' => [
                            'on' => [
                                'uf_f_id' => 'f_id',
                                'uf_us_id' => $usid
                            ]
                        ]
                    ]
                )
            );
            if($result){
                foreach ($result AS $row){
                    if(!$row['uf_id'] || ($row['uf_id'] && $row['uf_revoked'])){
                        $availableCars[] = $row['f_id'];
                    }
                }
            }
        }

        $this->sqlQuery(
             $this->owner->db->genSQLSelect(
                'fleet',
                [
                    'f_id AS list_key',
                    'CONCAT(ct_make, " ", ct_model, " (", f_licenceplate, ")") AS list_value',
                    'ct_make AS list_group',
                    'CONCAT(ct_make, " ", ct_model, " ", f_licenceplate) AS list_tokens',
                    'CONCAT(ct_make, " - ", ct_model, " ", f_licenceplate) AS list_order'
                ],
                '(uf_id IS NULL OR f_use_mode = 2)' . (!Empty($availableCars) ? ' AND f_id IN (' . implode(',', $availableCars) . ')' : ''),
                [
                    'car_types' => [
                        'on' => [
                            'ct_id' => 'f_ct_id'
                        ]
                    ],
                    'user_fleet' => [
                        'on' => [
                            'uf_f_id' => 'f_id',
                            'uf_revoked!' => 1,
                        ]
                    ]
                ],
                'f_id',
                [
                    'list_order'
                ]
            )
        );
        return $this->getList();
    }

    public function getPartnerTypes(){
        $this->setList([
            enumPartnerTypes::Lease()->getValue() => 'LBL_PARTNER_LEASE',
            enumPartnerTypes::Service()->getValue()  => 'LBL_PARTNER_SERVICE',
            enumPartnerTypes::TireService()->getValue()  => 'LBL_PARTNER_TIRE_SERVICE',
            enumPartnerTypes::FuelSupplier()->getValue()  => 'LBL_PARTNER_FUEL_SUPPLIER',
            enumPartnerTypes::Insurance()->getValue()  => 'LBL_PARTNER_INSURANCE',
            enumPartnerTypes::HighWayPermission()->getValue()  => 'LBL_PARTNER_HIGHWAY_PERMISSION_SUPPLIER',
            enumPartnerTypes::Other()->getValue()  => 'LBL_PARTNER_OTHER',
        ]);

        return $this->getList();
    }

    public function getPartners(enumPartnerTypes $types = null){
        $where = [];
        $select = [];

        if($types){
            if(!is_array($types)){
                $types = [$types];
            }
            $where = [
                'p_type' => [
                    'in' => $types
                ]
            ];
        }

        if(!$types || count($types) > 1){
            $select['group'] = 'p_type AS list_group';
            $this->setGroupOptions($this->getPartnerTypes());
        }

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'partners',
                [
                    'p_id AS list_key',
                    'p_name AS list_value'
                ] + $select,
                $where,
                [],
                [],
                [
                    'p_name'
                ]
            )
        );

        return $this->getList();
    }

    public function getTireTypes($id = false){
        $this->setList([
            1 => 'LBL_TIRE_SUMMER',
            2 => 'LBL_TIRE_WINTER',
            3 => 'LBL_TIRE_4SEASON',
        ]);

        return $this->getList($id);
    }

    public function getTires($freeOnly = true){
        $where = [];
        $this->setGroupOptions($this->getTireTypes());
        $this->list = [];

        if($freeOnly){
            $where = '(ftc_current IS NULL OR ftc_current = 0)';
        }

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'tires',
                [
                    't_id AS list_key',
                    'CONCAT(t_manufacturer, " ", t_brand) AS list_value',
                    'CONCAT(t_manufacturer, " ", t_brand, " (", t_size, ")") AS list_title',
                    't_size AS list_subtext',
                    't_type AS list_group'
                ],
                $where,
                [
                    'fleet_tire_changes' => [
                        'on' => [
                            't_id' => 'ftc_t_id',
                            'ftc_current' => 1,
                        ]
                    ]
                ],
                [
                    't_id'
                ],
                [
                    't_type, t_manufacturer, t_brand'
                ]
            )
        );

        return $this->getList();
    }

    public function getCarFuelTypes(){
        $this->setList([
            1 => 'LBL_FUEL_GASOLINE',
            2 => 'LBL_FUEL_GASOLINE_GAS',
            3 => 'LBL_FUEL_GASOLINE_HYBRID',
            4 => 'LBL_FUEL_DIESEL',
            5 => 'LBL_FUEL_DIESEL_HYBRID',
            6 => 'LBL_FUEL_GAS',
            7 => 'LBL_FUEL_ELECTRIC',
        ]);

        return $this->getList();
    }

    public function getFuelTypesById($id){
        $list = [
            1 => [
                0 => [
                    'type' => 'LBL_FUEL_GASOLINE',
                    'unit' => 'liter'
                ]
            ],
            2 => [
                0 => [
                    'type' => 'LBL_FUEL_GASOLINE',
                    'unit' => 'liter'
                ],
                1 => [
                    'type' => 'LBL_FUEL_GAS',
                    'unit' => 'liter'
                ]
            ],
            3 => [
                0 => [
                    'type' => 'LBL_FUEL_GASOLINE',
                    'unit' => 'liter'
                ]
            ],
            4 => [
                0 => [
                    'type' => 'LBL_FUEL_DIESEL',
                    'unit' => 'liter'
                ]
            ],
            5 => [
                0 => [
                    'type' => 'LBL_FUEL_DIESEL',
                    'unit' => 'liter'
                ]
            ],
            6 => [
                0 => [
                    'type' => 'LBL_FUEL_GAS',
                    'unit' => 'liter'
                ]
            ],
            7 => [
                0 => [
                    'type' => 'LBL_FUEL_ELECTRICITY',
                    'unit' => 'kW'
                ]
            ],
        ];

        $this->setList($list[$id]);

        return $this->getList();
    }

    public function getFuelContractTypes(){
        $this->setList([
            1 => 'LBL_FUELCONTRACT_GASSTATION',
            2 => 'LBL_FUELCONTRACT_LIST',
        ]);

        return $this->getList();
    }

    public function getFuelCards(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'fuel_cards',
                [
                    'fc_id AS list_key',
                    'CONCAT(fc_type, " (", fc_number, ") - ", fc_licenceplate) AS list_value',
                    'p_name AS list_group',
                    'CONCAT(p_name, " ", fc_type, " ", fc_number, " ", fc_licenceplate) AS list_tokens',
                    'CONCAT(p_name, " - ", fc_type, " ", fc_number) AS list_order'
                ],
                [],
                [
                    'partners' => [
                        'on' => [
                            'p_id' => 'fc_p_id'
                        ]
                    ]
                ],
                [],
                [
                    'list_order'
                ]
            )
        );
        return $this->getList();
    }

    public function getAssignableFuelCards($ownerId = false, $garage = false){
        $where = [];
        $excludeCards = [];

        if($ownerId){
            $result = $this->owner->db->getRows(
                $this->owner->db->genSQLSelect(
                    'fuel_card_owners',
                    [
                        'fco_fc_id'
                    ],
                    [
                        'fco_us_id' => (int)$ownerId,
                        'fco_revoked' => 0
                    ]
                )
            );
            if($result){
                foreach ($result AS $row){
                    if(!in_array($row['fco_fc_id'], $excludeCards)) {
                        $excludeCards[] = $row['fco_fc_id'];
                    }
                }

                $where[] = 'fc_id NOT IN (' . implode(',', $excludeCards) . ')';
            }
        }

        if($garage){
            $where[] = 'fc_garage = 1';
        }

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'fuel_cards',
                [
                    'fc_id AS list_key',
                    'CONCAT(fc_type, " (", fc_number, ") - ", fc_licenceplate) AS list_value',
                    'p_name AS list_group',
                    'CONCAT(p_name, " ", fc_type, " ", fc_number, " ", fc_licenceplate) AS list_tokens',
                    'CONCAT(p_name, " - ", fc_type, " ", fc_number) AS list_order'
                ],
                (!Empty($where) ? implode(' AND ', $where) : ''),
                [
                    'partners' => [
                        'on' => [
                            'p_id' => 'fc_p_id'
                        ]
                    ],
                    'fuel_card_owners' => [
                        'on' => [
                            'fco_fc_id' => 'fc_id',
                            'fco_revoked!' => 1,
                        ]
                    ]
                ],
                [],
                [
                    'list_order'
                ]
            )
        );
        return $this->getList();
    }

    public function getGearTypes(){
        $this->setList([
            1 => 'LBL_GEAR_MANUAL',
            2 => 'LBL_GEAR_AUTOMATIC',
        ]);

        return $this->getList();
    }

    public function getOwnershipTypes(){
        $this->setList([
            1 => 'LBL_OWN',
            2 => 'LBL_LEASED',
            3 => 'LBL_SOLD',
        ]);

        return $this->getList();
    }

    public function getRecurrenceTypes(){
        $this->setList([
            0 => 'LBL_RECURRENCE_NEVER',
            1 => 'LBL_RECURRENCE_DAILY',
            2 => 'LBL_RECURRENCE_WEEKLY',
            3 => 'LBL_RECURRENCE_MONTHLY',
            4 => 'LBL_RECURRENCE_QUARTERLY',
            5 => 'LBL_RECURRENCE_HALF_YEARLY',
            6 => 'LBL_RECURRENCE_YEARLY',
            7 => 'LBL_RECURRENCE_2_YEARLY',
            8 => 'LBL_RECURRENCE_4_YEARLY',
        ]);

        return $this->getList();
    }

    public function getContractTypes(){
        $this->setList([
            0 => 'LBL_CONTRACT_NORMAL',
            1 => 'LBL_CONTRACT_FLAT_RATE',
        ]);

        return $this->getList();
    }

    public function getPermissionTypes(){
        $this->setList([
            'Országos' => [
                1 => 'Heti (10 napos)',
                2 => 'Havi',
                3 => 'Éves',
            ],
            'Megyei' => [
                101 => 'Baranya',
                102 => 'Borsod-Abaúj-Zemplén',
                103 => 'Bács-Kiskun',
                104 => 'Csongrád',
                105 => 'Fejér',
                106 => 'Győr-Moson-Sopron',
                107 => 'Heves',
                108 => 'Hajdú-Bihar',
                109 => 'Komárom-Esztergom',
                110 => 'Pest',
                111 => 'Somogy',
                112 => 'Szabolcs-Szatmár-Bereg',
                113 => 'Tolna',
                114 => 'Vas',
                115 => 'Veszprém',
                116 => 'Zala',
            ]
        ]);

        return $this->getList();
    }

    public function getDocumentTypes(){
        $this->setList([
            'contract' => 'LBL_DOCTYPE_CONTRACT',
            'other' => 'LBL_DOCTYPE_OTHER',
        ]);

        return $this->getList();
    }

    public function getPaymentTypes(){
        $this->setList([
            1 => 'LBL_PAYMENT_TYPE_CASH',
            2 => 'LBL_PAYMENT_TYPE_BANK_TRANSFER',
            3 => 'LBL_PAYMENT_TYPE_CREDIT_CARD',
        ]);

        return $this->getList();
    }

    public function getUserGroups(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'user_groups',
                [
                    'ug_id AS list_key',
                    'ug_name AS list_value',
                ],
                [
                    'ug_enabled' => 1,
                    'ug_deleted' => 0,
                ],
                [],
                false
                [
                    'list_value'
                ]
            )
        );
        return $this->getList();
    }

    public function getUsers(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'user_fleet',
                [
                    'us_id AS list_key',
                    'CONCAT(us_lastname, " ", us_firstname) AS list_value',
                    'ug_name AS list_group',
                    'CONCAT(ug_name, " ", us_lastname, " ", us_firstname) AS list_tokens'
                ],
                [
                    'uf_revoked' => 0
                ],
                [
                    'users' => [
                        'on' => [
                            'us_id' => 'uf_us_id',
                            'uf_revoked' => 0,
                        ]
                    ],
                    'user_groups' => [
                        'on' => [
                            'ug_id' => 'us_ug_id'
                        ]
                    ],
                ],
                [
                    'us_id'
                ],
                [
                    'list_value'
                ]
            )
        );
        return $this->getList();
    }

    public function getEventsForInvoice($params){
        $interval = 5;
        $where = '';
        $this->list = [];

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'property_types',
                [
                    'prop_id',
                    'prop_name',
                ],
                [
                    'prop_has_event' => 1,
                    'prop_cost' => 1
                ]
            )
        );

        if($result){
            foreach($result AS $row){
                $this->list[] = [
                    'groupId' => 1,
                    'groupName' => $this->owner->translate->getTranslation('LBL_NEW_EVENT'),
                    'id' => '0|' . $row['prop_id'],
                    'text' => $row['prop_name']
                ];
            }
        }

        if(!$params['date']){
            $params['date'] = date('Y-m-d');
        }


        $where  = 'e_f_id = ' . $params['f_id'] . ' AND ';
        $where .= 'prop_cost = 1 AND (';

        $where .= '(e_date > "' . date('Y-m-d', strtotime($params['date'] . ' -' . $interval . 'day')) . '" AND ';
        $where .= 'e_date < "' . date('Y-m-d', strtotime($params['date'] . ' +' . $interval . 'day')) . '")';


        if($params['e_id']){
            $where .= ' OR e_id = ' . $params['e_id'];
        }

        $where .= ')';

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'events',
                [
                    'e_id',
                    'e_date',
                    'e_cost',
                    'e_currency',
                    'prop_name',
                    'prop_icon',
                ],
                $where,
                [
                    'property_types' => [
                        'on' => [
                            'prop_id' => 'e_prop_id'
                        ]
                    ]
                ]
            )
        );

        if($result){
            foreach($result AS $row){
                $this->list[] = [
                    'groupId' => 2,
                    'groupName' => $this->owner->translate->getTranslation('LBL_EXISTING_EVENTS'),
                    'id' => $row['e_id'],
                    'text' => '[' . $row['e_date'] . '] ' . $row['prop_name'] . ': ' . $this->owner->lib->formatPrice($row['e_cost'], $row['e_currency']),
                    'icon' => $row['prop_icon']
                ];
            }
        }

        return $this->getList();
    }

    public function setJson($json = true){
        $this->json = $json;
        return $this;
    }

    public function setEmptyItem($label = false, $value = 0){
        $this->emptyListLabel = $label;
        $this->emptyListValue = $value;
        return $this;
    }

    private function sqlQuery($sql){
        $this->list = [];
        $db = $this->owner->db;
        $res = $db->getRows($sql);
        if (!empty($res)) {
            $i = ($this->emptyListLabel ? 1 : 0);

            foreach($res as $row) {
                if($this->json){
                    $data = [
                        'id' => $row['list_key'],
                        'text' => $row['list_value'],
                    ];

                    if(!Empty($row['list_group'])){
                        $data['groupId'] = $row['list_group_id'];
                        $data['groupName'] = $row['list_group'];
                    }
                    if(!Empty($row['list_subtext'])){
                        $data['data']['subtext'] = $row['list_subtext'];
                    }
                    if(!Empty($row['list_tokens'])){
                        $data['data']['tokens'] = $row['list_tokens'];
                    }
                    if(!Empty($row['list_icon'])){
                        $data['data']['icon'] = $row['list_icon'];
                    }
                    $this->addItem($i, $data);
                }else {
                    $attributes = false;
                    if($row['list_subtext'] || $row['list_tokens'] || $row['list_icon'] || $row['list_title']){
                        $attributes = [];

                        if($row['list_subtext']) {
                            $attributes['data-subtext'] = $row['list_subtext'];
                        }
                        if($row['list_tokens']) {
                            $attributes['data-tokens'] = $row['list_tokens'];
                        }
                        if($row['list_icon']) {
                            $attributes['data-icon'] = $row['list_icon'];
                        }
                        if($row['list_title']) {
                            $attributes['title'] = $row['list_title'];
                        }
                    }

                    $this->addItem($row['list_key'], $row['list_value'], ($row['list_group'] ?: false), $attributes);
                }
                $i++;
            }
        }

        return $this;
    }

    private function addItem($key, $value, $group = false, $attributes = []){
        if($group) {
            if(isset($this->options[$group])){
                $group = $this->options[$group];
            }

            if($attributes) {
                $this->list[$group][$key]['name'] = $value;
                $this->list[$group][$key]['data'] = $attributes;
            }else {
                $this->list[$group][$key] = $value;
            }
        }else{
            if($attributes){
                $this->list[$key]['name'] = $value;
                $this->list[$key]['data'] = $attributes;

            }else {
                $this->list[$key] = $value;
            }
        }
        return $this;
    }

    private function translateItems(){
        if($this->list) {
            foreach ($this->list as $key => $val) {
                if (empty($val)) continue;
                if (is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        $this->list[$key][$key2] = $this->owner->translate->getTranslation($val2);
                    }
                } else {
                    $this->list[$key] = $this->owner->translate->getTranslation($val);
                }
            }
        }
        return $this;
    }

    public function reset(){
        $this->list = [];
        $this->json = false;
        $this->emptyListLabel = false;
        $this->emptyListValue = 0;

        return $this;
    }

    private function setGroupOptions($options){
        $this->options = $options;
        return $this;
    }

    private function setList(array $list){
        $this->list = $list;

        return $this;
    }

    private function getList($id = false){
        if($id){
            return $this->list[$id];
        }else {
            $this->addEmptyListItem();
            return $this->list;
        }
    }

    private function addEmptyListItem(){
        if($this->emptyListLabel){
            if($this->json) {
                $firstItem = [
                    0 => [
                        'id' => $this->emptyListValue,
                        'text' => $this->owner->translate->getTranslation($this->emptyListLabel),
                    ]
                ];
            }else{
                $firstItem = [
                    $this->emptyListValue => $this->owner->translate->getTranslation($this->emptyListLabel)
                ];
            }

            $this->list = $firstItem + $this->list;
        }
    }
}