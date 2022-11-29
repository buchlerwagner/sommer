<?php
class galleryFileUploader extends uploader {

    private $uploadPath;

    protected function getSetup(): array {
        return [
            'limit' => 1,
            'fileMaxSize' => 20,
            'extensions' => array('image/*', 'video/*', 'audio/*'),
            'uploadDir' => $this->uploadPath,
            'replace' => true,
            'required' => false,
            'title' => 'name',
            'editor' => array(
                'maxWidth' => 1980,
                'maxHeight' => 1980,
                'crop' => false,
                'quality' => 90
            )
        ];
    }

    protected function doUpload(array $uploadData, $fileId = false): array {
        $data = [];

        if ($uploadData['isSuccess']) {
            if (count($uploadData['files']) == 1) {
                $item = $uploadData['files'][0];

                if (!$fileId) {
                    $index = 0;
                    $row = $this->owner->db->getFirstRow(
                        $this->owner->db->genSQLSelect(
                            'gallery',
                            [
                                'MAX(g_index) AS idx'
                            ],
                            [
                                'g_shop_id' => $this->owner->shopId,
                                'g_folder' => $this->id
                            ]
                        )
                    );
                    if($row['idx']){
                        $index = (int) $row['idx'] + 1;
                    }

                    $this->owner->db->sqlQuery(
                        $this->owner->db->genSQLInsert(
                            'gallery',
                            [
                                'g_shop_id' => $this->owner->shopId,
                                'g_folder' => $this->id,
                                'g_title' => '',
                                'g_file' => FOLDER_UPLOAD . $this->owner->shopId . '/gallery/' . $item['name'],
                                'g_type' => $item['type'],
                                'g_size' => $item['size'],
                                'g_index' => $index,
                                'g_date' => 'NOW()',
                            ]
                        )
                    );

                    $fileId = $this->owner->db->getInsertRecordId();
                }else {
                    $this->owner->db->sqlQuery(
                        $this->owner->db->genSQLUpdate(
                            'gallery',
                            [
                                'g_size' => $item['size']
                            ],
                            [
                                'g_id' => $fileId,
                                'g_folder' => $this->id,
                                'g_shop_id' => $this->owner->shopId,
                            ]
                        )
                    );
                }

                if ($fileId) {
                    $item['name'] = $this->resizeImages($item['name']);

                    $data['files'][0] = [
                        'title' => $item['title'],
                        'name' => $item['name'],
                        'size' => $item['size'],
                        'size2' => $item['size2'],
                        'url' => FOLDER_UPLOAD . $this->owner->shopId . '/gallery/' . $item['name'],
                        'id' => $fileId,
                        'data' => [
                            'readerForce' => true,
                            'url' => FOLDER_UPLOAD . $this->owner->shopId . '/gallery/' . $item['name'],
                            'thumbnail' => str_replace('.', '_thumbnail.', FOLDER_UPLOAD . $this->owner->shopId . '/gallery/' . $item['name']),
                            'listProps' => [
                                'id' => $fileId,
                            ]
                        ],
                    ];
                } else {
                    if (is_file($item['file'])) {
                        @unlink($item['file']);
                    }

                    unset($data['files'][0]);

                    $data['hasWarnings'] = true;
                    $data['warnings'][] = 'An error occured.';
                }
            }
        }

        return $data;
    }

    protected function doDelete(int $fileId): void {
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'gallery',
                [
                    'g_file'
                ],
                [
                    'g_id' => $fileId,
                    'g_folder' => $this->id,
                    'g_shop_id' => $this->owner->shopId
                ]
            )
        );

        if ($row) {
            $file = str_replace(FOLDER_UPLOAD, DIR_UPLOAD, $row['g_file']);
            if (is_file($file)) {
                unlink($file);
            }

            if($GLOBALS['IMAGE_SIZES']){
                foreach($GLOBALS['IMAGE_SIZES'] AS $postfix => $size){
                    $fileResized = str_replace('.', '_' . $postfix . '.', $file);

                    if (is_file($fileResized)) {
                        unlink($fileResized);
                    }
                }
            }

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLDelete(
                    'gallery',
                    [
                        'g_id' => $fileId,
                        'g_folder' => $this->id,
                        'g_shop_id' => $this->owner->shopId
                    ]
                )
            );

            $this->owner->mem->delete(CACHE_GALLERY . $this->owner->shopId);
        }
    }

    protected function doSort(array $list): void {
        $index = 0;
        foreach($list as $val) {
            if (!isset($val['id']) || !isset($val['name']) || !isset($val['index']))
                break;

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'gallery',
                    [
                        'g_index' => $index
                    ],
                    [
                        'g_id' => $val['id'],
                        'g_folder' => $this->id,
                        'g_shop_id' => $this->owner->shopId
                    ]
                )
            );

            $index++;
        }

        $this->owner->mem->delete(CACHE_GALLERY . $this->owner->shopId);
    }

    protected function setDefault(int $fileId): array {
        $out = [
            'selected' => 0
        ];
        
        $this->owner->db->sqlQuery(
            "UPDATE gallery SET g_main = NOT g_main WHERE g_id = '$fileId' AND g_shop_id='" . $this->owner->shopId . "'"
        );

        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'gallery',
                [
                    'g_main'
                ],
                [
                    'g_id' => $fileId,
                    'g_folder' => $this->id,
                    'g_shop_id' => $this->owner->shopId
                ]
            )
        );

        if($row) {
            $out['selected'] = (int)$row['g_main'];
        }

        $this->owner->mem->delete(CACHE_GALLERY . $this->owner->shopId);

        return $out;
    }

    protected function init(){
        $this->uploadPath = DIR_UPLOAD . $this->owner->shopId . '/gallery/';

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

        return $newFileName . '.' . $parts['extension'];
    }

    protected function loadFiles(): array {
        $files = [];
        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'gallery',
                [],
                [
                    'g_shop_id' => $this->owner->shopId,
                    'g_folder' => $this->id,
                ],
                [],
                false,
                'g_index'
            )
        );
        if ($result) {
            foreach($result AS $row) {
                $files[] = array(
                    'name' => ($row['g_title'] ?: basename($row['g_file'])),
                    'type' => $row['g_type'],
                    'size' => $row['g_size'],
                    'file' => $row['g_file'],
                    'data' => array(
                        'readerForce' => true,
                        'url' => $row['g_file'],
                        //'thumbnail' => str_replace('.', '_thumbnail.', $row['g_file']),
                        'thumbnail' => $row['g_file'],
                        'date' => $row['g_date'],
                        'isMain' => $row['g_main'],
                        'listProps' => array(
                            'id' => $row['g_id'],
                        )
                    ),
                );
            }
        }

        return $files;
    }

    protected function doRename(int $fileId, string $title): array {
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'gallery',
                [
                    'g_title' => $title
                ],
                [
                    'g_id' => $fileId,
                    'g_folder' => $this->id,
                    'g_shop_id' => $this->owner->shopId
                ]
            )
        );

        $this->owner->mem->delete(CACHE_GALLERY . $this->owner->shopId);

        return [
            'title' => $title,
            'file' => $title,
        ];
    }

    protected function doEdit(int $fileId, array $options): void {
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'gallery',
                [
                    'g_file'
                ],
                [
                    'g_id' => $fileId,
                    'g_folder' => $this->id,
                    'g_shop_id' => $this->owner->shopId
                ]
            )
        );

        if ($row) {
            $file = str_replace(FOLDER_UPLOAD, DIR_UPLOAD, $row['g_file']);

            if (is_file($file)) {
                $parts = pathinfo($file);
                $origFileName = $parts['filename'];

                if($GLOBALS['IMAGE_SIZES']){
                    foreach($GLOBALS['IMAGE_SIZES'] AS $postfix => $size){
                        FileUploader::resize(
                            $this->uploadPath . $origFileName . ($postfix != 'default' ? '_' . $postfix : '') . '.' . $parts['extension'],
                            null,
                            null,
                            null,
                            (isset($options['crop']) ?: null),
                            100,
                            (isset($options['rotation']) ?: null)
                        );
                    }
                }

                $size = filesize($file);

                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLUpdate(
                        'gallery',
                        [
                            'g_size' => $size
                        ],
                        [
                            'g_id' => $fileId,
                            'g_folder' => $this->id,
                            'g_shop_id' => $this->owner->shopId
                        ]
                    )
                );
            }
        }
    }
}