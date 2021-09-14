<?php
class previewLink extends formElement {
    const Type = 'preview';
    const PreviewUrl = '/ajax/preview/?';

    private $type;

    private $fileId;
    private $fileName;
    private $fileHash;

    public function __construct($id, enumFileTypes $fileType, $label = '', $class = ''){
        parent::__construct($id, $label, null, $class);
        $this->type = $fileType;
    }

    public function init() {
        $this->notDBField();
    }

    public function getType():string{
        return $this::Type;
    }

    public function getFileType(){
        return $this->type;
    }

    public function getFileId(){
        return $this->fileId;
    }

    public function getFileHash(){
        return $this->fileHash;
    }

    public function getFileName(){
        return ($this->fileName ?: $this->getLabel());
    }

    public function setFileData($fileId, $fileHash, $fileName = ''){
        $this->fileId = $fileId;
        $this->fileHash = $fileHash;
        $this->fileName = $fileName;
        return $this;
    }

    public function getPreviewUrl(){
        return self::PreviewUrl . 'type=' . $this->getFileType() . '&id=' . $this->getFileId() . '&hash=' . $this->getFileHash();
    }
}