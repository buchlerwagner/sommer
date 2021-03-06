<?php
class inputEditor extends formElement {
    const Type = 'editor';

    protected function init() {
        $this->addClass('htmleditor');

        $this->addCss('summernote', 'summernote/summernote-bs4.css');
        $this->addJs('summernote', 'summernote/summernote-bs4.js');
        $this->addJs('summernote-cleaner', 'summernote/summernote-cleaner.js');
    }

    public function hasGallery(){
        $this->addJs('summernote-gallery', 'summernote/summernote-gallery-extension.js');
        $this->addCss('fileuploader', 'fileuploader/jquery.fileuploader.min.css');
        $this->addCss('fileuploader-thumbnail', 'fileuploader/fileuploader-theme-thumbnails.css');
        $this->addCss('fileuploade-galleryr', 'fileuploader/fileuploader-theme-gallery.css');
        $this->addJs('fileuploader', 'fileuploader/jquery.fileuploader.min.js');

        return $this;
    }

    public function getType():string {
        return $this::Type;
    }

}