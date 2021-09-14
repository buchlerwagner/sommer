<?php
class inputEditor extends formElement {
    const Type = 'editor';

    protected function init() {
        $this->addClass('htmleditor');

        $this->addCss('summernote', 'summernote/summernote-bs4.css');
        $this->addJs('summernote', 'summernote/summernote-bs4.js');
        $this->addJs('summernote-cleaner', 'summernote/summernote-cleaner.js');
        $this->addJs('summernote-gallery', 'summernote/summernote-gallery-extension.js');
    }

    public function getType():string {
        return $this::Type;
    }

}