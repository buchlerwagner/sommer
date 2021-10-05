<?php
class content extends ancestor {
    private $pagUrl;
    private $data = [];

	public function init($pageUrl){
        $this->pagUrl = $pageUrl;

        $this->loadContent();
        return $this;
	}

    public function getContent(){
        $this->owner->setPageMetaData($this->data);
        return $this->data;
    }

	private function loadContent(){
        $content = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'contents',
                [],
                [
                    'c_shop_id' => $this->owner->shopId,
                    'c_page_url' => $this->pagUrl,
                    'c_empty_menu' => 0,
                    'c_deleted' => 0,
                    'c_published' => 1,
                    'c_widget' => '',
                    'c_language' => $this->owner->language,
                ]
            )
        );

        if($content){
            $parentUrl = $this->getParent($content['c_parent_id']);

            $this->data = [
                'title' => $content['c_title'],
                'subTitle' => $content['c_subtitle'],
                'headline' => $content['c_headline'],
                'content' => $content['c_content'],
                'image' => ($content['c_page_img'] ? $this->getImagePath($content['c_id']) . $content['c_page_img'] : false),
                'seo' => [
                    'title' => $content['c_page_title'],
                    'name' => $content['c_page_title'],
                    'description' => $content['c_page_description'],
                    'image' => ($content['c_page_img'] ? 'https://' . rtrim($this->owner->host, '/') . $this->getImagePath($content['c_id']) . $content['c_page_img'] : false),
                    'url' => 'https://' . rtrim($this->owner->host, '/') . '/' . $parentUrl . $content['c_page_url'] . '/',
                ]
            ];
        }else{
            $this->setNotFound();
        }

		return $this;
	}

    private function getParent($id){
        $url = '';

        if($id) {
            $parent = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'contents',
                    [
                        'c_page_url'
                    ],
                    [
                        'c_shop_id' => $this->owner->shopId,
                        'c_id' => $id,
                        'c_deleted' => 0,
                        'c_published' => 1,
                        'c_widget' => '',
                        'c_language' => $this->owner->language,
                    ]
                )
            );
            if($parent){
                $url = $parent['c_page_url'] . '/';
            }
        }

        return $url;
    }

    private function setNotFound(){
        $this->owner->addHttpHeader(enumHTTPHeaders::NotFound404());
        $this->owner->page = '404';
    }

    public function getImagePath($pageId, $absolutePath = false){
        if($absolutePath) {
            return DIR_UPLOAD . $this->owner->shopId . '/pages/' . $pageId . '/';
        }else{
            return FOLDER_UPLOAD . $this->owner->shopId . '/pages/' . $pageId . '/';
        }
    }
}
