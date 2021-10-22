<?php
class syncDictionary extends apiClient {
    private $language = [];
    private $shopId = 0;

    public function syncLabels($language, $shopId = 0):array{
        $out = [
            'master' => [
                'new' => 0,
                'deleted' => 0,
            ],
            'dev' => [
                'new' => 0,
                'deleted' => 0,
            ]
        ];

        $this->shopId = (int) $shopId;
        $this->language = strtolower(trim($language));

        if(!in_array($this->language, $this->owner->hostConfig['languages'])){
            return $out;
        }

        $this->setEndPoint(SYNC_SERVER_ENDPOINT)
             ->setCredentials(SYNC_API_USERNAME, SYNC_API_PASSWORD);


        $out['master'] = $this->downloadLabels();
        $out['dev'] = $this->uploadLabels();

        // Remote clean up
        $this->setServiceUrl('dictionary/cleanup/' . $this->language . '/')->callService(self::CALL_METHOD_GET);

        // Local clean up
        $this->owner->translate->deleteUnusedLabels();
        $this->owner->translate->removeUnusedContextItems();
        $this->owner->translate->clearTranslationCache($this->language);

        return $out;
    }

    private function downloadLabels(){
        $out = [
            'new' => 0,
            'deleted' => 0,
        ];

        $result = $this->setServiceUrl('dictionary/get-labels/' . $this->language . '/')->callService(self::CALL_METHOD_GET);

        if($result['new']){
            $this->owner->translate->updateLabelSet($result['new']);

            foreach($result['new'] AS $labels){
                $out['new'] += count($labels);
            }

            $this->setServiceUrl('dictionary/mark-synced/' . $this->shopId . '/')->setPayload($result['new'])->callService(self::CALL_METHOD_POST);
        }

        if($result['delete']) {
            $this->owner->translate->deleteLabels($result['delete']);
            $out['deleted'] = count($result['delete']);
        }

        return $out;
    }

    private function uploadLabels(){
        $labelSet = [];
        $out = [
            'new' => 0,
            'deleted' => 0,
        ];

        $labelSet['new'] = $this->owner->translate->loadLabelSet($this->language, $this->shopId);
        $labelSet['delete'] = $this->owner->translate->listDeletedLabels($this->shopId);

        if(!Empty($labelSet['new']) || !Empty($labelSet['delete'])) {
            $ret = $this->setServiceUrl('dictionary/set-labels/' . $this->shopId . '/')->setPayload($labelSet)->callService(self::CALL_METHOD_POST);

            if($labelSet['new']) {
                foreach($labelSet['new'] AS $labels) {
                    $out['new'] += count($labels);
                }
            }

            if($labelSet['delete']) {
                $out['deleted'] = count($labelSet['delete']);
            }

            if($ret['update']){
                $this->owner->translate->markLabelsSynced($labelSet['new'], $this->shopId);
            }

            if($ret['delete']){
                $this->owner->translate->deleteLabels();
            }
        }

        return $out;
    }
}