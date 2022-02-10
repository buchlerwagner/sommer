<?php
class addFunctionForm extends formBuilder {
    private $key;

    public function setupKeyFields() {
    }

	public function setup() {
		$this->title = 'LBL_ADD_FUNCTION';
		$this->dbTable = 'access_functions';
		$this->reloadPage = true;
		$this->rights = 'useraccesslevel';
        $this->key = $this->parameters['keyvalues'][0];

        $this->addControls(
            (new inputText('af_name', 'LBL_FUNCTION_NAME'))
                ->setRequired(),
            (new inputText('af_key', 'LBL_FUNCTION_KEY'))
                ->setRequired()
                ->setPrepend($this->key . '-')
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function saveValues() {
        $this->values['af_page'] = $this->key;
        $this->values['af_key'] = $this->key . '-' . safeURL($this->values['af_key']);

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLInsert(
                "access_functions",
                $this->values
            )
        );
    }
}
