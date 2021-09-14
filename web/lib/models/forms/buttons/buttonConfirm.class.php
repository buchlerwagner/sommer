<?php
class buttonConfirm extends buttonModal {
    const Template = 'button';

    public function getTemplate() {
        return $this::Template;
    }

    public function setAction(enumModalActions $action, $value = 1, $additionalAction = ''){
        if($action == enumModalActions::PostForm()){
            $this->postForm('action', $value, $additionalAction);
        }elseif ($action == enumModalActions::PostModalForm()){
            $this->postModalForm('action', $value, $additionalAction);
        }
        return $this;
    }

    public function setTexts($question, $buttonCaption = false):formButton {
        $this->addData('confirm-question', $question, true);
        if($buttonCaption){
            $this->addData('confirm-button', $buttonCaption, true);
        }else{
            $this->addData('confirm-button', $this->caption, true);
        }
        return $this;
    }

    public function requestReason($fieldId):formButton {
        $this->addData('confirm-reason', true);
        $this->addData('confirm-reason-field', $fieldId);
        return $this;
    }

    public function init() {
        $this->setType(enumButtonTypes::Button());

        $this->addData('toggle', 'modal');
        $this->addData('target', '#confirm-delete');
        $this->addData('backdrop', 'static');
        $this->addData('keyboard', 'false');

        $this->dialogColor();

        return $this;
    }
}