<?php
class productFileUploader extends uploader {
    /**
     * @var $product product
     */
    private $product = null;

    private $uploadPath;

    protected function getSetup(): array {
        return [
            'fileMaxSize' => FILEUPLOAD_MAX_FILESIZE,
            'extensions' => ['jpg', 'jpeg', 'png'],
            'uploadDir' => $this->uploadPath,
            'replace' => true,
            'required' => false,
            'title' => function ($item) {
                return UUID::v4();
            }
        ];
    }

    protected function doUpload(array $uploadData, $fileId = false): array {
        $data = [];

        if ($uploadData['isSuccess']) {
            foreach ($uploadData['files'] AS $key => $value) {
                $value['extension'] = strtolower($value['extension']);
                $value['name'] = $this->resizeImages($value['name']);
                $order = 1;

                $row = $this->owner->db->getFirstRow("SELECT MAX(pimg_order) AS ord FROM " . DB_NAME_WEB . ".product_images WHERE pimg_prod_id='" . $this->id . "'");
                if(!$row){
                    $this->product->setDefaultImg($value['name'] . '.' . $value['extension']);
                }else{
                    $order = (int) $row['ord'] + 1;
                }

                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLInsert(
                        'product_images',
                        [
                            'pimg_prod_id' => $this->id,
                            'pimg_filename' => $value['name'],
                            'pimg_orig_filename' => $value['old_name'],
                            'pimg_type' => 'IMAGE',
                            'pimg_size' => $value['size'],
                            'pimg_mimetype' => $value['type'],
                            'pimg_extension' => $value['extension'],
                            'pimg_order' => $order,
                        ]
                    )
                );

                $path = $this->product->getImagePath();

                $data['files'][$key] = [
                    'title'     => $value['old_name'],
                    'name'      => $value['old_name'],
                    'size'      => $value['size'],
                    'file'      => $path . $value['name'] . '.' . $value['extension'],
                    'data'      => [
                        'id'    => $this->owner->db->getInsertRecordId(),
                        'name'  => $value['name'] . '.' . $value['extension'],
                        'thumbnail' => $path . $value['name'] . '_thumbnail.' . $value['extension'],
                    ],
                ];
            }
        }
        return $data;
    }

    protected function doDelete(int $fileId): void {
        $img = $this->product->getImage($fileId);
        if ($img['prod_id'] == $this->id) {

            if($GLOBALS['IMAGE_SIZES']){
                foreach($GLOBALS['IMAGE_SIZES'] AS $postfix => $size){
                    @unlink($this->uploadPath . $img['pimg_filename'] . ($postfix != 'default' ? '_' . $postfix : '') . '.' . $img['pimg_extension']);
                }
            }else {
                @unlink($this->uploadPath . $img['pimg_filename'] . '.' . $img['pimg_extension']);
            }

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLDelete(
                    'product_images',
                    [
                        'pimg_id' => $fileId,
                        'pimg_prod_id' => $this->id,
                    ]
                )
            );

            $this->product->reorderImages()->setDefaultImg();
        }
    }

    protected function doSort(array $list): void {
        $this->product->reorderImages($list)->setDefaultImg();
    }

    protected function setDefault(int $fileId): array {
        $img = $this->product->getImage($fileId);
        if($img) {
            $this->product->setDefaultImg($img['pimg_filename'] . ' ' . $img['pimg_extension']);
        }

        return [];
    }

    protected function init(){
        $this->product = $this->owner->addByClassName('product');
        $this->product->init($this->id);

        $this->uploadPath = $this->product->getImagePath(true);

        if (!file_exists($this->uploadPath)) {
            @mkdir($this->uploadPath, 0777, true);
            @chmod($this->uploadPath, 0777);
        }

        return $this;
    }

    private function resizeImages($origFileName, $removeOriginal = false){
        $parts = pathinfo($origFileName);
        $newFileName = $parts['filename'];

        if($GLOBALS['IMAGE_SIZES']){
            foreach($GLOBALS['IMAGE_SIZES'] AS $postfix => $size){
                FileUploader::resize(
                    $this->uploadPath . $origFileName,
                    $size['width'],
                    $size['height'],
                    $this->uploadPath . $newFileName . ($postfix != 'default' ? '_' . $postfix : '') . '.' . $parts['extension'],
                    ($size['crop'] ?: false),
                    ($size['quality'] ?: 97),
                    ($size['rotation'] ?: 0)
                );
            }

            if($removeOriginal){
                @unlink($this->uploadPath . $origFileName);
            }
        }

        return $newFileName;
    }

    protected function loadFiles(): array {
        return [];
    }

    protected function doRename(int $fileId, string $title): array {
        return [];
    }

    protected function doEdit(int $fileId, array $options): void {
    }
}