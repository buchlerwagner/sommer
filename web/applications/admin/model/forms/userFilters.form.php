<?php
class userFiltersForm extends filterForm {
    public function setupKeyFields() {
    }

    public function setup() {
		parent::setup();

        $this->customRights = ACCESS_RIGHT_WRITE;
        $this->parentTable = 'users';

        $this->addControls(
            (new groupRow('row'))->addElements(
                (new inputText('userName', 'LBL_NAME'))->setColSize('col-12 col-sm-6'),
                (new inputSelect('us_ug_id', 'LBL_GROUP'))->setColSize('col-12 col-sm-6')->setOptions($this->getGroups())
            )
        );

    }

    private function getGroups(){
	    $out = [
	        0 => $this->owner->translate->getTranslation('LBL_ANY')
        ];
	    $where = false;

        if($groups = $this->owner->user->getUserGroups()){
            $where['ug_id']['in'] = $groups;
        }

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'users',
                [
                    'ug_id',
                    'ug_name'
                ],
                $where,
                [
                    'user_groups' => [
                        'on' => [
                            'us_ug_id' => 'ug_id'
                        ]
                    ]
                ],
                false,
                'ug_name'
            )
        );
        if($result){
            foreach($result AS $row){
                $out[$row['ug_id']] = $row['ug_name'];
            }
        }

        return $out;
    }
}
