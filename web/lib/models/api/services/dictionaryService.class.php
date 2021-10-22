<?php
/**
 * Dictionary API calls
 */
class dictionaryService extends requester {
    /**
     * @var $dictionary translate
     */
    private $dictionary;

    private $shopId = 0;

    public function init(): void {
        $this->dictionary = $this->owner->addByClassName('translate');
    }

    public function get_GetLabels($id){
        if(!isset($id[0])){
            throw new apiException('Please specify the language', 400, API_HTTP_BAD_REQUEST);
        }else{
            $language = strtolower(trim($id[0]));
        }

        if(isset($id[1])){
            $this->shopId = (int) $id[1];
        }

        return [
            'shopId' => $this->shopId,
            'new'    => $this->dictionary->loadLabelSet($language, $this->shopId),
            'delete' => $this->dictionary->listDeletedLabels($this->shopId),
        ];
    }

    public function post_SetLabels($id){
        $out['update'] = 0;
        $out['delete'] = 0;

        $labels = $this->getRequestBody();

        if(isset($id[0])){
            $this->shopId = (int) $id[0];
        }

        // update labels which are coming from dev server
        if($labels['new']) {
            $this->dictionary->updateLabelSet($labels['new'], $this->shopId);
            $out['update'] = 1;
        }

        // delete labels which were marked on dev server
        if($labels['delete']) {
            $this->dictionary->deleteLabels($labels['delete']);
            $out['delete'] = 1;
        }

        return $out;
    }

    public function get_CleanUp($id){
        if(!isset($id[0])){
            throw new apiException('Please specify the language', 400, API_HTTP_BAD_REQUEST);
        }else{
            $languages = explode(',', trim($id[0], ','));
        }

        $this->dictionary->removeUnusedContextItems();

        // clear memcache
        $this->dictionary->clearTranslationCache($languages);

        return [];
    }

    public function post_MarkSynced($id){
        $labels = $this->getRequestBody();

        if(isset($id[0])){
            $this->shopId = (int) $id[0];
        }

        // mark labels as synced (di_new=0) on local DB
        if($labels) {
            $this->dictionary->markLabelsSynced($labels, $this->shopId);
        }

        // delete labels which are marked for delete on local DB
        $this->dictionary->deleteLabels();

        return [];
    }

}