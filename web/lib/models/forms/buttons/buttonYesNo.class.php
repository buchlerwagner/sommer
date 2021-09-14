<?php
class buttonYesNo extends buttonModal {
    const Template = 'href';

    public function getTemplate() {
        return $this::Template;
    }

    public function setYesAction(enumModalActions $action, $value = 1, $additionalAction = false){
        if($action == enumModalActions::PostForm()){
            $this->postForm('action', $value, $additionalAction);
        }elseif ($action == enumModalActions::PostModalForm()){
            $this->postModalForm('action', $value, $additionalAction);
        }
        return $this;
    }

    public function setNoAction($action = ''){
        $this->addData('no-action', ($action ? $action . ';' : '') . "$('#yesno-modal').modal('hide');");
        return $this;
    }

    public function setQuestion($question):formButton {
        $this->addData('confirm-question', $question);
        return $this;
    }

    protected function init() {
        $this->addData('toggle', 'modal');
        $this->addData('target', '#yesno-modal');
        $this->addData('backdrop', 'static');
        $this->addData('keyboard', 'false');

        $this->dialogColor();
    }
}