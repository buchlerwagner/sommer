<?php
/**
 * @var $this router
 */

if ($this->user->isLoggedIn()) {
	$this->pageRedirect('/');
}

$loginForm = $this->loadForm('login');
$forgotPasswordForm = $this->loadForm('forgotPassword');

if($forgotPasswordForm->errors || isset($_REQUEST['success'])){
	if(isset($_REQUEST['success'])){
		$this->data['success'] = true;
	}else{
		$this->data['success'] = false;
	}
	$this->data['login'] = false;
	$this->data['forgotpassword'] = true;
}else{
	$this->data['login'] = true;
	$this->data['forgotpassword'] = false;
	$this->data['success'] = false;
}

if(isset($_REQUEST['forgot-password'])){
    $this->data['login'] = false;
    $this->data['forgotpassword'] = true;
}

$this->view->addInlineJs("    
	$('a[data-toggle=\"tab\"]')
		.on('click', function() {
			$('a[data-toggle=\"tab\"]').removeClass('active')
		});
");
