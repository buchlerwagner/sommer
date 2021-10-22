<?php
/**
 * @var $this router
 */

$this->output = OUTPUT_JSON;
$this->data = [];
if($this->user->hasPageAccess('dictionary')) {
	$action = $this->params[1];
	switch ($action) {
		case 'loadpage':
			$data['post'] = $this->getSession('dictionary-options');
			$data['post']['page'] = $_REQUEST['page'];

			$this->setSession('dictionary-options', $data['post']);

			$data['labels'] = $this->translate->getAllLabels(
				$data['post']['langfrom'],
				$data['post']['langto'],
				$this->context,
				$data['post']['page'],
				false,
				[
					'flag' => $data['post']['filter'],
					'query' => $data['post']['label']
				],
				$data['post']['sort']
			);

			$this->output = OUTPUT_RAW;
			$this->data = $this->view->renderContent('dictionary-items', $data);

			break;

		case 'loadcontent':
			$data['post'] = $_REQUEST;
			$data['post']['page'] = 1;
			$this->setSession('dictionary-options', $data['post']);
			//$data['post']['supervisor'] = ($this->info['user']['access_level'] == 'supervisor' ? true : false);

			$data['labels'] = $this->translate->getAllLabels(
				$data['post']['langfrom'],
				$data['post']['langto'],
				$this->context,
				$data['post']['page'],
				true,
				[
					'flag' => $data['post']['filter'],
					'query' => trim($data['post']['label'])
				],
				$data['post']['sort']
			);

			$this->data['#labels-list'] = $this->view->renderContent('dictionary-items', $data);

			$this->data['#label-info-orig'] = $this->translate->getTranslation('LBL_ORIGINAL_LABELS', $data['labels']['stats']['orig']['translated'], $data['labels']['stats']['total']);
			$this->data['progressbars']['orig']['value'] = $data['labels']['stats']['orig']['status'];
			$this->data['progressbars']['orig']['text'] = $this->data['progressbars']['orig']['value'] . '%';

			/*
			$this->data['#label-info-custom'] = $this->translate->getTranslation('LBL_CUSTOM_LABELS', $data['labels']['stats']['custom']['translated'], $data['labels']['stats']['total']);
			$this->data['progressbars']['custom']['value'] = $data['labels']['stats']['custom']['status'];
			$this->data['progressbars']['custom']['text'] = $this->data['progressbars']['custom']['value'].'%';
			*/
			$this->data['totalpages'] = (int)$data['labels']['stats']['totalpages'];

			break;
		case 'savecontent':
			if (!Empty($_POST['label']) AND $_POST['langto']) {
				$value = urldecode($_POST['value']);
				//$value = str_replace("\n", '<br>', $value);
				$this->translate->setContext($_POST['context']);

				$this->translate->saveTranslation(
					$_POST['langto'],
					$_POST['label'],
					$value,
					$this->context
				);
			}

			// calculate progress status
			$data = $this->translate->countLabels(
				$_POST['langfrom'],
				$_POST['langto'],
				$this->context
			);

			$this->data['date'] = $this->lib->formatDate(date('Y-m-d H:i:s'), 5);
			$this->data['progress']['orig']['info'] = $this->translate->getTranslation('LBL_ORIGINAL_LABELS', $data['orig']['translated'], $data['total']);
			$this->data['progress']['orig']['value'] = $data['orig']['status'];
			$this->data['progress']['orig']['text'] = $this->data['progress']['orig']['value'] . '%';

			/*
			$this->data['progress']['custom']['info'] = $this->translate->getTranslation('LBL_CUSTOM_LABELS', $data['custom']['translated'], $data['total']);
			$this->data['progress']['custom']['value'] = $data['custom']['status'];
			$this->data['progress']['custom']['text'] = $this->data['progress']['custom']['value'].'%';
			*/

			break;

		case 'delete-key':
			$this->data['success'] = 0;
			$this->translate->markLabelForDelete($_REQUEST['key']);
			$this->data['success'] = 1;

			// calculate progress status
			$data = $this->translate->countLabels(
				$_REQUEST['langfrom'],
				$_REQUEST['langto'],
				$this->context
			);

			$this->data['progress']['orig']['info'] = $this->translate->getTranslation('LBL_ORIGINAL_LABELS', $data['orig']['translated'], $data['total']);
			$this->data['progress']['orig']['value'] = $data['orig']['status'];
			$this->data['progress']['orig']['text'] = $this->data['progress']['orig']['value'] . '%';

			/*
			$this->data['progress']['custom']['info'] = $this->translate->getTranslation('LBL_CUSTOM_LABELS', $data['custom']['translated'], $data['total']);
			$this->data['progress']['custom']['value'] = $data['custom']['status'];
			$this->data['progress']['custom']['text'] = $this->data['progress']['custom']['value'].'%';
			*/

			break;

		case 'sync':
            if(SERVER_ID == 'development') {
                $data = [
                    'title' => 'Sync labels <i id="sync-progress" class="fa fa-spinner fa-spin d-none"></i>',
                    'content' => 'sync-labels',
                    'buttons' => [
                        0 => (new buttonHref('btn-startsync', 'BTN_SYNC', 'btn btn-primary'))
                            ->setUrl('javascript:;')
                            ->setIcon('fa fa-sync-alt'),
                        1 => new buttonModalClose('btn-close', 'BTN_CLOSE')
                    ],
                ];

                $this->output = OUTPUT_RAW;
                $this->data = $this->view->renderContent('modal', $data);
            }
			break;

		case 'do-sync':
            if(SERVER_ID == 'development') {
                /**
                 * @var $sync syncDictionary
                 */
                $sync = $this->addByClassName('syncDictionary');
                $data = $sync->syncLabels($_REQUEST['lang']);

                $this->data['#master-new'] = $data['master']['new'];
                $this->data['#master-del'] = $data['master']['deleted'];
                $this->data['#dev-new'] = $data['dev']['new'];
                $this->data['#dev-del'] = $data['dev']['deleted'];
            }

			break;
	}
}
