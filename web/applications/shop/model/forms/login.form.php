<?php
class loginForm extends form {

	public function setup() {
		$this->controls = [
			0 => [
				'id'          => 'email',
				'type'        => 'email',
				'name'        => 'email',
				'label'       => 'LBL_EMAIL',
				'default'     => '',
			],
			1 => [
				'id'          => 'password',
				'type'        => 'password',
				'name'        => 'password',
				'label'       => 'LBL_PASSWORD',
				'default'     => '',
			],
			/*
			2 => [
				'id'       => 'remember',
				'type'     => 'checkbox',
				'name'     => 'remember',
				'caption'  => 'LBL_REMEMBER',
				'valueon'  => 1,
				'valueoff' => 0,
				'default'  => 1
			],
			*/
			100 => [
				'id'          => 'redirect',
				'type'        => 'hidden',
				'name'        => 'redirect',
				'default'  	  => (isset($_GET['path']) ? $_GET['path'] : '')
			],
		];

		$this->buttons = [
			1 => [
				'id'    => 'signin',
				'type'  => 'submit',
				'class' => 'primary',
				'name'  => 'signin',
				'label' => 'BTN_LOGIN'
			]
		];
	}

	public function onValidate() {
		if (!empty($this->values['email']) && !checkEmail($this->values['email'])) {
			$this->addError(10002, 'Wrong email format', 2, ['email']);
		}
	}

	public function signin() {
		if (!empty($this->values['email']) && !empty($this->values['password'])) {
            $login = $this->owner->user->login($this->values['email'], $this->values['password']);

			if (!empty($login)) {
				$redirect = trim($this->values['redirect']);

				if(!Empty($redirect)) {
					$redirect = '/' . ltrim($redirect, '/');
				}else {
					$redirect = $this->owner->root;
				}

                $this->owner->cartHandler->claimCart();

				//$this->reset();
				$this->owner->pageRedirect($redirect);

			} else {
				$this->addError(10000, 'Bad username or password', 2, ['email', 'password']);
			}
		}else{
			$this->addError(10001, 'Email and/or password is missing', 2, ['email', 'password']);
		}
	}

}
