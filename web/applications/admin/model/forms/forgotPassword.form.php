<?php
class forgotPasswordForm extends form {

	public function setup() {
		$this->controls = [
			0 => [
				'id'      => 'email',
				'type'    => 'email',
				'name'    => 'email',
				'label'   => 'LBL_EMAIL',
				'default' => '',
				'constraints' => [
					'required' => true
				]
			]
		];

		$this->buttons = [
			0 => [
				'id'    => 'sendpwd',
				'type'  => 'submit',
				'class' => 'primary',
				'name'  => 'sendpwd',
				'label' => 'BTN_OK'
			]
		];
	}

	public function onValidate() {
		if(Empty($this->values['email'])){
			$this->addError(10008, "We don't recognised the given email address.", 2, ['email']);
		}elseif(!checkEmail($this->values['email'])){
			$this->addError(10002, 'Wrong email format', 2, ['email']);
		}
	}

	public function sendpwd() {
		$user = $this->owner->user->validateUserByEmail($this->values['email']);
		if($user['valid']){
			$data = [
				'id' => $user['userid'],
				'link' => $this->owner->user->setId($user['userid'])->getPasswordChangeLink(),
			];
			$this->owner->email->prepareEmail('request-new-password', $user['userid'], $data);
			$this->owner->pageRedirect('/login/?success');
		}else{
			$this->addError(10008, "We don't recognised the given email address.", 2, ['email']);
		}
	}

}
