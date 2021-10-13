<?php
/**
 * @var $this router
 */

$this->output = OUTPUT_JSON;

$uploader = null;
$uploaderClass = strtolower(trim($this->params[1]));
$id = (int) $this->params[2];
if(!$id) $id = 0;
$action = trim($this->params[3]);

if(!Empty($uploaderClass) && class_exists($uploaderClass . 'FileUploader')){
    /**
     * @var $uploader uploader
     */
    $uploader = $this->addByClassName($uploaderClass . 'FileUploader');

    switch($action){
        case 'load':
            $this->data = $uploader->load($id);
            break;

        case 'upload':
            $this->data = $uploader->upload($id, 'fileUpload', (int)$_REQUEST['id']);
            break;

        case 'delete':
            if (!Empty($_REQUEST['id'])) {
                $uploader->delete($id, (int)$_REQUEST['id']);
            }
            break;

        case 'rename':
            if (!Empty($_REQUEST['id'])) {
                $this->data = $uploader->rename($id, (int)$_REQUEST['id'], $_REQUEST['title']);
            }
            break;

        case 'edit':
            if (!Empty($_REQUEST['id'])) {
                $editor = json_decode($_POST['_editor'], true);
                if($editor) {
                    $uploader->edit($id, (int)$_REQUEST['id'], $editor);
                }
            }
            break;

        case 'mark':
            if (!Empty($_REQUEST['id'])) {
                $this->data = $uploader->mark($id, (int)$_REQUEST['id']);
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
