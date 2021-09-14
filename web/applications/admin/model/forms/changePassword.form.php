<?php
class changePasswordForm extends form {
	public function setup() {
	    $this->boxed = false;

		$this->controls = [
			0 => [
				'fieldset' => false,
				'group'    => [
					0 => [
						'id'      => 'old_password',
						'type'    => 'password',
						'name'    => 'old_password',
						'label'   => 'LBL_CURRENT_PASSWORD',
						'col'     => 12,
						'class'   => 'col-6',
						'default' => '',
						'constraints' => [
							'required' => true
						]
					],
					1 => [
						'id'      => 'password1',
						'type'    => 'password',
						'name'    => 'password1',
						'label'   => 'LBL_NEW_PASSWORD',
						'col'     => 12,
						'class'   => 'col-6',
						'default' => '',
						'constraints' => [
							'required' => true
						]
					],
					2 => [
						'id'      => 'password2',
						'type'    => 'password',
						'name'    => 'password2',
						'label'   => 'LBL_CONFIRM_PASSWORD',
						'col'     => 12,
						'class'   => 'col-6',
						'default' => '',
						'constraints' => [
							'required' => true,
							'equalto'  => "#password1"
						]
					],
				]
			]
		];

		$this->buttons = [
			0 => [
				'id'    => 'setpassword',
				'type'  => 'submit',
				'class' => 'primary',
				'name'  => 'setpassword',
				'label' => 'BTN_SAVE'
			]
		];
	}

	public function setpassword() {
		$this->owner->clearMessages();

		if (empty($this->values['old_password'])) $this->values['old_password'] = '';

		if (!empty($this->values['password1']) && !empty($this->values['password2'])) {
			if ($this->values['password1'] != $this->values['password2']) {
				$this->addError(10012, 'Passwords are not matching!', 2, ['password2']);
			} else {
				if($this->owner->user->validatePassword($this->values['old_password'])){
					$this->owner->user->setPassword($this->values['password1']);
					$this->reset();

					$this->owner->addMessage(router::MESSAGE_SUCCESS, false, 'LBL_PASSWORD_CHANGED');
					$this->owner->pageRedirect('/my-profile/?tab=security');
					return;
				}else{
					$this->addError(10003, 'Bad password.', 2, ['old_password']);
				}
			}
		}
	}

}
