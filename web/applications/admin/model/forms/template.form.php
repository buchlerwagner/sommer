<?php
class templateForm extends form {

	public function setup() {
		$this->dbTable = DB_NAME_WEB . ".templates";

		if (empty($this->parameters['keyfields'])) {
			$this->keyFields['mt_id'] = 0;
		}

		if(isset($this->parameters['keyvalues'][0]) AND !Empty($this->parameters['keyvalues'][0])) {
			$this->keyFields['mt_id'] = $this->parameters['keyvalues'][0];
		}

		$this->title = 'LBL_EDIT_TEMPLATE';

		$this->controls = [
			0 => [
				'fieldset' => false,
				'group' => [
					0 => [
						'id'       => 'mt_type',
						'type'     => 'hidden',
						'name'     => 'mt_type',
					],
					1 => [
						'id'       => 'mt_subject',
						'type'     => 'text',
						'name'     => 'mt_subject',
						'label'    => 'LBL_TITLE',
						'default'  => '',
						'col'      => 12,
						'constraints' => [
							'required' => true
						]
					],
					2 => [
						'id'          => 'mt_body',
						'type'        => 'htmleditor',
						'editor'      => 'summernote',
						'name'        => 'mt_body',
						'label'       => 'LBL_BODY',
						'col'         => 12,
						'constraints' => [
							'required' => true
						]
					],
					3 => [
						'id'          => 'mt_keywords',
						'type'        => 'hidden',
						'name'        => 'mt_keywords',
					],
					4 => [
						'id'          => 'mt_description',
						'type'        => 'hidden',
						'name'        => 'mt_description',
					],
				]
			]
		];

		$this->buttons = [
			'save' => [
				'id'    => 'save',
				'type'  => 'submit',
				'class' => 'primary btn-progress',
				'name'  => 'save',
				'label' => 'BTN_SAVE'
			],
			'cancel' => [
				'id'    => 'cancel',
				'type'  => 'link',
				'class' => 'btn btn-light ml-2',
				'name'  => 'cancel',
				'label' => 'BTN_CANCEL',
				'href'  => '/settings/content/templates/'
			]
		];
	}

	public function onAfterLoadValues() {
		if($this->values['mt_keywords']){
			$keywords = explode('|', $this->values['mt_keywords']);
			$this->controls[0]['group'][3]['html'] = '<div class="col-12">' . $this->owner->translate->getTranslation('LBL_AVAILABLE_VARIABLES') . '<br>';
			foreach($keywords AS $key){
				$this->controls[0]['group'][3]['html'] .= '<span class="badge badge-primary mr-1 btn-insert-text cursor-pointer">{{ ' . $key . ' }}</span>';
			}
			$this->controls[0]['group'][3]['html'] .= '</div>';
		}
	}

	public function onAfterInit() {
		$this->subTitle = $this->values['mt_key'];
	}

	public function onBeforeSave() {
		unset($this->values['mt_keywords']);
	}

}
