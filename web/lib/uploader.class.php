<?php
abstract class uploader extends ancestor {
    protected $id = null;

    abstract protected function init();

    abstract protected function doUpload(array $uploadData, $fileId = false): array;

    abstract protected function doDelete(int $fileId): void;

    abstract protected function doSort(array $list): void;

    abstract protected function setDefault(int $fileId): array;

    abstract protected function loadFiles(): array;

    abstract protected function doRename(int $fileId, string $title): array;

    abstract protected function doEdit(int $fileId, array $options): void;

    public function load($id): array{
        $this->id = (int) $id;

        $this->init();
        return $this->loadFiles();
    }

    public function upload(int $id, string $uploaderFormName, $fileId = false): array{
        $this->id = $id;

        $this->init();
        $FileUploader = new FileUploader($uploaderFormName, $this->getSetup());

        return $this->doUpload($FileUploader->upload());
    }

    public function delete(int $id, int $fileId){
        $this->id = $id;

        $this->init()->doDelete($fileId);
    }

    public function sort(int $id, array $list){
        $this->id = $id;

        $this->init()->doSort($list);
    }

    public function mark(int $id, int $fileId){
        $this->id = $id;

        return $this->init()->setDefault($fileId);
    }

    public function rename(int $id, int $fileId, string $title){
        $this->id = $id;

        return $this->init()->doRename($fileId, $title);
    }

    public function edit(int $id, int $fileId, array $options){
        $this->id = $id;

        $this->init()->doEdit($fileId, $options);
    }

    protected function getSetup():array {
        return [
            'fileMaxSize'   => null,
            'maxSize'       => null,
            'limit'         => null,
            'extensions'    => null,
            'disallowedExtensions'    => null,
            'uploadDir'     => DIR_UPLOAD,
            'replace'       => true,
            'required'      => false,
            'title'         => 'name'
        ];
    }
}