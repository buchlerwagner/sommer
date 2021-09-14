<?php
class inputAutocomplete extends inputText {
    const Type = 'autocomplete';

    protected function init() {
        $this->addClass('autocomplete');
    }

    public function getType():string{
        return $this::Type;
    }

    public function getTemplate() {
        return 'text';
    }

    public function setUrl($listUrl, $scope = false, $clearOnSelect = false):formControl {
        $this->addData('list', $listUrl);
        if($scope) {
            $this->addData('scope', $scope);
        }
        if($clearOnSelect) {
            $this->addData('clearonselect', 1);
        }
        return $this;
    }

    public function connectTo($elementIds):formControl {
        if(is_array($elementIds)){
            $elementIds = implode(',', $elementIds);
        }

        $this->addData('connected-select', $elementIds);
        return $this;
    }

    public function postFields($elementIds):formControl {
        if(is_array($elementIds)){
            $elementIds = implode(',', $elementIds);
        }

        $this->addData('extra-params', $elementIds);
        return $this;
    }

    public function callback($action):formControl {
        $this->addData('callback', $action);
        return $this;
    }
}