<?php
abstract class uploader extends ancestor {
    protected $id = null;

    abstract protected function init();

    abstract protected function doUpload(array $uploadData): array;

    abstract protected function doDelete(int $fileId): void;

    abstract protected function doSort(array $list): void;

    abstract protected function setDefault(int $fileId): void;

    public function upload(int $id, string $uploaderFormName): array{
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