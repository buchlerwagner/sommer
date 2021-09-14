<?php
/**
 * @var $this router
 * @var $form form
 */

$this->output = OUTPUT_JSON;
$this->data = [];

$params = [];
$formName = $this->params[1];
$keyValues = explode('|', $this->params[2]);
$tableName = $this->params[3];

if(isset($_REQUEST['params'])) $params = $_REQUEST['params'];

$form = false;
if ($this->loadModel('forms', $formName . '.form')) {
	$params['keyvalues'] = $keyValues;
	$params['action'] = $_SERVER['REQUEST_URI'];
	$params['viewOnly'] = isset($_REQUEST['view']);

	$form = $this->addByClassName($formName . 'Form', $formName, [$params]);
	$form->init();
}

if (!empty($form)) {
	switch($form->state) {
		case FORM_STATE_LOADED:
			$data = [
				'title' => $form->title,
				'subtitle' => $form->subTitle,
				'readonly' => $form->readonly,
				'content' => $form::ModalTemplate,
				'accept_action' => ($form->readonly ? false : "$('#" . $form->name . "-form').submit();"),
				'captions' => [
					'save' => ($form->captions['save'] ?: 'BTN_OK'),
					'cancel' => ($form->captions['cancel'] ?: 'BTN_CANCEL'),
				]
			];

			if ($form->customModalButtons) {
				$data['buttons'] = $form->buttons;
				unset($data['accept_action']);
			}

			$form->title = false;
			$form->buttons = [];
			$data['form'] = $form;
			$this->output = OUTPUT_RAW;
			$this->data = $this->view->renderContent('modal', $data);
			break;
		case FORM_STATE_INVALID:
			$form->buttons = [];
            $this->data = [
				'#ajax-modal .modal-body' => [
				    'html' => $this->view->renderContent($form::ModalTemplate, ['form' => $form])
                ]
			];
			break;
		case FORM_STATE_SAVED:
			$this->data = [];
			if($form->reloadPage) {
                $this->data['#ajax-modal']['closeModal'] = true;
                $this->data['frm']['functions']['callback'] = 'modalFormPageRefresh';
                $this->data['frm']['functions']['arguments'] = $formName;
			}elseif($form->returnData) {
			    $this->data = $form->returnData;
			    $this->data['#ajax-modal']['closeModal'] = true;
			}else{
			    // remove first key as it is not a foreign key but the record ID itself which is not needed for the table for reloading!
			    if(count($keyValues) > 1){
                    array_shift($keyValues);
                }

                $this->data['tables']['reloadTable'] = [$tableName, implode('|', $keyValues), true];
            }

			break;
		default:
			echo $form->state;
			break;
	}
}
