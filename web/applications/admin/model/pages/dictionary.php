<?php
/**
 * @var $this router
 */
$post = $this->getSession('dictionary-options');

if(!$post) {
	// default values
	$post['context'] = $this->context;
	$post['filter'] = 'all';
	$post['sort'] = 'key';
	$post['set_original'] = 0;
	$post['langfrom'] = DEFAULT_LANGUAGE;
	$post['langto'] = DEFAULT_LANGUAGE;
}

$post['page'] = 1;
$this->data['labels'] = $this->translate->getAllLabels(
	$post['langfrom'],
	$post['langto'],
	$this->context,
	$post['page'],
	false,
	[
		'flag'  => $post['filter'],
		'query' => $post['label']
	],
	$post['sort']
);

$this->data['lists']['filter'] = [
	'all' => 'LBL_ALL',
	'new' => 'LBL_NEW',
	'not-translated' => 'LBL_NOT_TRANSLATED'
];
$this->data['lists']['sort'] = [
	'key'   => 'LBL_SORTBY_KEY',
	'label' => 'LBL_SORTBY_LABEL'
];

$this->data['post'] = $post;
$this->view->addJs('dictionary.min.js');
$this->view->addJs('autosize/autosize.min.js');