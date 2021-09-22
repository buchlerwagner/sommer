<?php
class newPasswordForm extends form {
    public $postUrl;

	public function setup() {
		$this->displayErrors = true;

		$this->controls = [
			0 => [
				'id'      => 'password1',
				'type'    => 'password',
				'name'    => 'password',
				'label'   => 'LBL_NEW_PASSWORD',
				'default' => '',
				'constraints' => [
					'required' => true
				]
			],
			1 => [
				'id'      => 'password2',
				'type'    => 'password',
				'name'    => 'confirm_password',
				'label'   => 'LBL_CONFIRM_PASSWORD',
				'default' => '',
				'constraints' => [
					'required' => true
				]
			],
			2 => [
				'id'      => 'token',
				'type'    => 'hidden',
				'name'    => 'token',
			]
		];

		$this->buttons = [
			0 => [
				'id'    => 'setpwd',
				'type'  => 'submit',
				'class' => 'primary',
				'name'  => 'setpwd',
				'label' => 'BTN_SET_NEWPWD'
			]
		];

        $this->postUrl = $GLOBALS['PAGE_NAMES'][$this->owner->language]['set-new-password']['name'];
    }

	public function onAfterLoadValues() {
		if($this->state == 'loaded') {
			$token = $this->owner->user->checkToken(urldecode($_REQUEST['token']));

			if (!isset($_REQUEST['token']) || !$token['valid']) {
				$this->isValid = false;
				$this->addError(10011, 'Missing or invalid token', 2, []);

				unset($this->buttons[0]);
			} else {
				$this->values['token'] = $token['token'];
			}
		}
	}

	public function onValidate() {
		parent::onValidate();

		if(Empty($this->values['password']) || Empty($this->values['confirm_password'])) {
			$this->addError(1000, 'Mandatory values are missing', 2, ['password1', 'password2']);
		}else {
			if ($this->values['password'] != $this->values['confirm_password']) {
				$this->addError(10012, 'Passwords are not matching!', 2, ['password2']);
			}

			if (!$this->values['token']) {
				$this->isValid = false;
				$this->addError(10011, 'Missing or invalid token', 2, []);
			}
		}
	}

	public function setpwd() {
		$token = $this->owner->user->checkToken($this->values['token'], true);
		if($token['valid']) {
			$this->owner->user->setId($token['userid'])->setPassword($this->values['password'])->clearSession();
            $this->owner->pageRedirect('/' . $this->postUrl . '/?success');
		}else{
			$this->isValid = false;
			$this->addError(10011, 'Missing or invalid token', 2, []);
		}
	}

}
