<?php
/**
 * @var $this router
 */

$this->output = OUTPUT_RAW;

$this->data = [];
$hash = $_REQUEST['hash'];
$type = $_REQUEST['type'];
$id = (int) $_REQUEST['id'];

$fileName = '/file.php?m=inline&type=' . $type . '&id=' . $id . '&hash=' . $hash;
$fileDownload = '/file.php?m=download&type=' . $type . '&id=' . $id . '&hash=' . $hash;
$fileType = pathinfo($hash, PATHINFO_EXTENSION);

$data = [
    'title'   => false,
    'fileName' => $fileName,
    'downloadUrl' => $fileDownload,
    'fileType' => $fileType,
    'content' => 'preview',
    'buttons' => [
        0 => (new buttonHref('btn-download', 'BTN_DOWNLOAD', 'btn btn-warning float-left'))
                ->setIcon('fa fa-cloud-download-alt')
                ->setTarget('_blank')
                ->setUrl($fileDownload),
        1 => new buttonModalClose('btn-close', 'BTN_CLOSE')
    ],
];

$this->data = $this->view->renderContent('modal', $data);
