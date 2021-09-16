<?php
/**
 * @var $this router
 */

$this->output = OUTPUT_JSON;

$uploader = null;
$uploaderClass = strtolower(trim($this->params[1]));
$id = (int) $this->params[2];
$action = trim($this->params[3]);

if(!Empty($uploaderClass) && class_exists($uploaderClass . 'FileUploader')){

    /**
     * @var $uploader uploader
     */
    $uploader = $this->addByClassName($uploaderClass . 'FileUploader');

    switch($action){
        case 'upload':
            $this->data = $uploader->upload($id, 'fileUpload');
            break;

        case 'delete':
            if (!Empty($_REQUEST['id'])) {
                $uploader->delete($id, (int)$_REQUEST['id']);
            }
            break;

        case 'sort':
            if (!Empty($_REQUEST['list'])) {
                $list = json_decode($_REQUEST['list'], true);
                if($list){
                    $uploader->sort($id, $list);
                }
            }
            break;
    }
}
