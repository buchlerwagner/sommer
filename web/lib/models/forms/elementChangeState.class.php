<?php
trait elementChangeState {
    private $states = [];

    public function changeDefaultState(enumChangeAction $action, $elementIds){
        $this->addData('state-default', json_encode([$action->getValue() => $elementIds]));
        return $this;
    }

    public function changeState($onValue, enumChangeAction $action, $elementIds){
        $this->addClass('change-state');
        $this->states[$onValue][$action->getValue()] = $elementIds;
        $this->buildData();

        return $this;
    }

    private function buildData(){
        $this->addData('state-options', json_encode($this->states));
    }
}