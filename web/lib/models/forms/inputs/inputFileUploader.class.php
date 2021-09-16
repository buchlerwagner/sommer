<?php
class inputFileUploader extends formElement {
    const Type = 'fileUploader';

    private $ajaxURL = false;
    private $files = [];
    private $theme = 'thumbnails';
    private $labels = [
        'errors' => ''
    ];

    private $allowedExtensions = [];
    private $disallowedExtensions = [];
    private $changeInput = ' ';
    private $apiEnabled = true;
    private $addMore = true;
    private $inputNameBrackets = false;
    private $actionEdit = false;
    private $actionView = false;
    private $actionDelete = false;
    private $actionSort = false;
    private $actionDownload = false;

    protected function init() {
        $this->addCss('fileuploader', 'fileuploader/jquery.fileuploader.min.css');
        $this->addCss('fileuploader-thumbnail', 'fileuploader/fileuploader-theme-thumbnails.css');
        $this->addCss('fileuploade-galleryr', 'fileuploader/fileuploader-theme-gallery.css');
        $this->addJs('fileuploader', 'fileuploader/jquery.fileuploader.min.js');

        $this->notDBField();
        $this->addClass('fileUploader');
    }

    public function getType():string {
        return $this::Type;
    }

    public function preloadFiles(array $files){
        $this->files = $files;
        $this->addData('fileuploader-files', json_encode($files));
        return $this;
    }

    public function setTheme($theme){
        $this->addData('fileuploader-theme', $theme);
        $this->theme = $theme;
        return $this;
    }

    public function setLimit($limit){
        $this->addData('fileuploader-limit', $limit);
        return $this;
    }

    public function setMaxSize($maxSize){
        $this->addData('fileuploader-maxSize', $maxSize);
        return $this;
    }

    public function setFileMaxSize($maxSize){
        $this->addData('fileuploader-fileMaxSize', $maxSize);
        return $this;
    }

    public function setAllowedExtensions(array $extensions){
        $this->allowedExtensions = $extensions;
        $this->addData('fileuploader-extensions', implode(', ', $extensions));
        return $this;
    }

    public function setDisallowedExtensions(array $extensions){
        $this->disallowedExtensions = $extensions;
        return $this;
    }

    public function setAjaxEndPoint($url){
        $this->ajaxURL = rtrim($url, '/') . '/';
        return $this;
    }

    public function addMore($isEnabled){
        $this->addMore = $isEnabled;
        return $this;
    }

    public function hasEdit($isEnabled = true, $label = false){
        $this->actionEdit = $isEnabled;
        $this->labels['edit'] = $label;
        return $this;
    }

    public function hasView($isEnabled = true, $label = false){
        $this->actionView = $isEnabled;
        $this->labels['view'] = $label;
        return $this;
    }

    public function hasDelete($isEnabled = true, $label = false){
        $this->actionDelete = $isEnabled;
        $this->labels['remove'] = $label;
        return $this;
    }

    public function hasSort($isEnabled = true, $label = false){
        $this->actionSort = $isEnabled;
        $this->labels['sort'] = $label;
        return $this;
    }

    public function hasDownload($isEnabled = true, $label = false){
        $this->actionDownload = $isEnabled;
        $this->labels['download'] = $label;
        return $this;
    }

    public function setInlineJs() {
        $data = [
            'theme' =>  $this->theme,
            'captions' =>  $this->labels,
            //'limit' =>  $this->limit,
            //'maxSize' =>  $this->maxSize,
            //'fileMaxSize' =>  $this->fileMaxSize,
            'extensions' =>  $this->allowedExtensions,
            'disallowedExtensions' =>  $this->disallowedExtensions,
            'changeInput' =>  $this->changeInput,
            'enableApi' =>  $this->apiEnabled,
            'addMore' =>  $this->addMore,
            'inputNameBrackets' =>  $this->inputNameBrackets,
        ];

        if($this->theme == 'thumbnails'){
            if($this->actionEdit){
                $data['editor'] = true;
            }
            
            $data['thumbnails']['box'] =
                '<div class="fileuploader-items">' .
                    '<ul class="fileuploader-items-list">' .
                    '<li class="fileuploader-thumbnails-input no-sort"><div class="fileuploader-thumbnails-input-inner"><i>+</i></div></li>' .
                    '</ul>' .
                '</div>';

            $data['thumbnails']['item'] =
                '<li class="fileuploader-item" data-id="${data.id}">' .
                    '<div class="fileuploader-item-inner">' .
                        //'<div class="type-holder">${extension}</div>' .
                        '<div class="actions-holder">' .
                            ($this->actionView ? '<button type="button" class="fileuploader-action fileuploader-action-popup2" title="${captions.sort}"><i class="fas fa-expand-arrows-alt"></i></button>' : '' ) .
                            ($this->actionDelete ? '<button type="button" class="fileuploader-action fileuploader-action-remove" title="${captions.remove}"><i class="fas fa-trash-alt"></i></button>' : '') .
                        '</div>' .
                        '<div class="thumbnail-holder' . ($this->actionSort ? ' fileuploader-action-sort' : '') . '">' .
                            '${image}' .
                        '<span class="fileuploader-action-popup"></span>' .
                        '</div>' .
                        '<div class="content-holder"><span>${size2}</span></div>' .
                        '<div class="progress-holder">${progressBar}</div>' .
                    '</div>' .
                '</li>';

            $data['thumbnails']['item2'] =
                '<li class="fileuploader-item" data-id="${data.id}">' .
                    '<div class="fileuploader-item-inner">' .
                        //'<div class="type-holder">${extension}</div>' .
                        '<div class="actions-holder">' .
                            ($this->actionDownload ? '<a href="${file}" class="fileuploader-action fileuploader-action-download" title="${captions.download}" download><i class="fas fa-cloud-download-alt"></i></a>' : '' ) .
                            ($this->actionView ? '<button type="button" class="fileuploader-action fileuploader-action-popup2" title="${captions.sort}"><i class="fas fa-expand-arrows-alt"></i></button>' : '' ) .
                            ($this->actionDelete ? '<button type="button" class="fileuploader-action fileuploader-action-remove" title="${captions.remove}"><i class="fas fa-trash-alt"></i></button>' : '' ) .
                        '</div>' .
                        '<div class="thumbnail-holder' . ($this->actionSort ? ' fileuploader-action-sort' : '') . '">' .
                            '${image}' .
                            '<span class="fileuploader-action-popup"></span>' .
                        '</div>' .
                        '<div class="content-holder"><span>${size2}</span></div>' .
                        '<div class="progress-holder">${progressBar}</div>' .
                    '</div>' .
                '</li>';

            //$data['startImageRenderer'] = true;
            $data['thumbnails']['canvasImage'] = false;
            $data['thumbnails']['_selectors'] = [
                'list'       => '.fileuploader-items-list',
                'item'       => '.fileuploader-item',
                'start'      => '.fileuploader-action-start',
                'retry'      => '.fileuploader-action-retry',
                'popup_open' => '.fileuploader-action-popup2',
                'remove'     => '.fileuploader-action-remove'
            ];
            if($this->actionSort){
                $data['thumbnails']['_selectors']['sorter'] = '.fileuploader-action-sort';
            }

            $data['thumbnails']['onItemShow'] = "function(item, listEl, parentEl, newInputEl, inputEl) {
                var plusInput = listEl.find('.fileuploader-thumbnails-input'),
                api = $.fileuploader.getInstance(inputEl.get(0));

                plusInput.insertAfter(item.html)[api.getOptions().limit && api.getChoosedFiles().length >= api.getOptions().limit ? 'hide' : 'show']();

                if(item.format == 'image') {
                    item.html.find('.fileuploader-item-icon').hide();
                }
            }";

            $data['thumbnails']['onItemRemove'] = "function(html, listEl, parentEl, newInputEl, inputEl) {
                var plusInput = listEl.find('.fileuploader-thumbnails-input'),
                api = $.fileuploader.getInstance(inputEl.get(0));

                html.children().animate({'opacity': 0}, 200, function() {
                html.remove();

                if (api.getOptions().limit && api.getChoosedFiles().length - 1 < api.getOptions().limit)
                    plusInput.show();
                });
            }";

            $data['dragDrop']['container'] = '.fileuploader-thumbnails-input';

            $data['afterRender'] = "function(listEl, parentEl, newInputEl, inputEl) {
                var plusInput = listEl.find('.fileuploader-thumbnails-input'),
                api = $.fileuploader.getInstance(inputEl.get(0));
    
                plusInput.on('click', function() {
                    api.open();
                });
    
                api.getOptions().dragDrop.container = plusInput;
            }";
        }
        
        if($this->ajaxURL){
            $data['startImageRenderer'] = false;
            $data['upload'] = [
                'url' => $this->ajaxURL . 'upload/',
                'data' => null,
                'type' => 'POST',
                'enctype' =>'multipart/form-data',
                'start' => true,
                'synchron' => true,
                'chunk' => false,
                'beforeSend' =>null,
            ];

            $data['upload']['onSuccess'] = "function(result, item) {
                var data = {};

                if (result && result.files)
                    data = result;
                else
                    data.hasWarnings = true;

                // if success
                if (data.isSuccess && data.files.length) {
                    item.name = data.files[0].name;
                    //item.html.find('.content-holder > h5').text(item.name).attr('title', item.name);
                }

                // if warnings
                if (data.hasWarnings) {
                    for (var warning in data.warnings) {
                        alert(data.warnings[warning]);
                    }

                    item.html.removeClass('upload-successful').addClass('upload-failed');
                    return this.onError ? this.onError(item) : null;
                }

                setTimeout(function() {
                    item.html.find('.progress-holder').hide();
                    item.renderThumbnail();

                    item.html.find('.fileuploader-action-make-default').attr('data-id', data.files[0].data.id);

                    item.html.find('.fileuploader-action-popup, .fileuploader-item-image').show();
                }, 400);
            }";

            $data['upload']['onError'] = "function(item) {
                item.html.find('.progress-holder, .fileuploader-action-popup, .fileuploader-item-image').hide();
            }";

            $data['upload']['onProgress'] = "function(data, item) {
                var progressBar = item.html.find('.progress-holder');

                if(progressBar.length > 0) {
                    progressBar.show();
                    progressBar.find('.fileuploader-progressbar .bar').width(data.percentage + \"%\");
                }

                item.html.find('.fileuploader-action-popup, .fileuploader-item-image').hide();
            }";

            $data['onRemove'] = "function(item) {
                $.post('" . $this->ajaxURL . "delete/', {
                    file: item.data.name,
                    id: item.data.id,
                });
            }";
        }

        if($this->actionSort){
            $data['sorter'] = [
                'selectorExclude' => null,
                'placeholder' => null,
                'scrollContainer' => 'window',
            ];

            $data['sorter']['onSort'] = "function(list, listEl, parentEl, newInputEl, inputEl) {
                var api = $.fileuploader.getInstance(inputEl.get(0)),
                fileList = api.getFileList(),
                _list = [];
    
                $.each(fileList, function(i, item) {
                    _list.push({
                        id: item.data.id,
                        index: item.index,
                    });
                });
    
                $.post('" . $this->ajaxURL . "sort/', {
                    list: JSON.stringify(_list)
                });
            }";
        }

        $js = "$('#" . $this->getId() . "').fileuploader(" . $this->encodeJson($data) . ");\n";
        $js .= "$(document).on('click', '.fileuploader-action-make-default', function(){
                    var \$this = $(this);
                    $.ajax({
                        url: '" . $this->ajaxURL . "set-default/?id=' + \$this.data('id'),
                        success: function(data) {
                            $('.fileuploader-action-make-default i').removeClass('fa-check-square').addClass('fa-square');
                            \$this.find('i').removeClass('fa-square').addClass('fa-check-square');
                        }
                    });
                });";

        return $js;
    }

    private function encodeJson($array){
        $values = [];
        $keys = [];

        array_walk_recursive($array, function(&$array) use(&$values, &$keys) {
            static $index = 1;

            if(strpos($array, 'function(') !== false){
                $key = 'function' . $index++;
                $values[] = $array;
                $keys[] = '"%' . $key . '%"';
                $array = '%' . $key . '%';
            }
        });

        $json = json_encode($array, JSON_PRETTY_PRINT);
        $json = str_replace($keys, $values, $json);

        return $json;
    }
}