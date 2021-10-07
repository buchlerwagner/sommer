<?php
class previewImage extends formElement {
    const Type = 'previewImage';
    const PreviewUrl = '/ajax/preview/?';

    private $path;
    private $src = false;
    private $preview = true;
    private $imgSize = [];
    private $cropMode = false;
    private $responsive = false;

    public function __construct($id, $class = ''){
        parent::__construct($id, false, null, $class);
    }

    public function init() {
        $this->imgSize = [
            'width' => false,
            'height' => false,
        ];
    }

    public function getType():string{
        return $this::Type;
    }

    public function setPath($path){
        $this->path = rtrim($path, '/') . '/';
        return $this;
    }

    public function getPath(){
        return $this->path;
    }

    public function setSrc($src){
        $this->src = $src;
        return $this;
    }

    public function getSrc(){
        return $this->src;
    }

    public function setResponsive($responsive){
        $this->responsive = $responsive;
        return $this;
    }

    public function isResponsive(){
        return $this->responsive;
    }

    public function setCropMode(enumCrop $cropMode){
        $this->cropMode = $cropMode;
        return $this;
    }

    public function getCropMode(){
        return $this->cropMode;
    }

    public function setPreview($preview = true){
        $this->preview = $preview;
        return $this;
    }

    public function hasPreview(){
        return $this->preview;
    }

    public function setSize($width = false, $height = false){
        $this->imgSize = [
            'width' => $width,
            'height' => $height
        ];
        return $this;
    }

    public function getSize(){
        return $this->imgSize;
    }

    public function getPreviewUrl(){
        return self::PreviewUrl . 'src=';
    }

}