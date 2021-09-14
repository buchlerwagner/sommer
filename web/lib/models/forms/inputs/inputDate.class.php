<?php
class inputDate extends formElement {
    const Type = 'text';
    private $placeholder = '';

    public function getType():string {
        return $this::Type;
    }

    public function getTemplate() {
        return 'text';
    }

    public function onlyNumbers($chars = ''){
        $this->addClass('numbersonly');

        if(!Empty($chars)){
            $this->addData('chars', $chars);
        }

        return $this;
    }

    public function setPlaceholder($placeholder){
        $this->placeholder = $placeholder;
        return $this;
    }

    public function getPlaceholder():string{
        return $this->placeholder;
    }

    public function setNumberOfCalendars($calendars){
        $this->addData('calendars', $calendars);
        return $this;
    }

    public function setMaxDate($maxDate){
        /**
         * @todo formated date
         * $this->owner->lib->formatDate( date('Y-m-d') ),
         */
        $this->addData('max-date', $maxDate);
        return $this;
    }

    public function setMinDate($minDate){
        /**
         * @todo formated date
         * $this->owner->lib->formatDate( date('Y-m-d') ),
         */
        $this->addData('min-date', $minDate);
        return $this;
    }

    public function setYearRange(int $from, int $to){
        $this->addData('year-range', $from . ':' . $to);
        return $this;
    }

    public function limitRangeFrom($formId){
        $this->addData('range-from', $formId);
        return $this;
    }

    public function limitRangeTo($formId){
        $this->addData('range-to', $formId);
        return $this;
    }

    protected function init() {
        $this->addClass('datepicker');
        $this->addData('calendars', 1);
        $this->addData('change-month', 'true');
        $this->addData('change-year', 'true');
        $this->addData('dateformat', $this->locals['dateformat']);
        $this->addData('language', $this->language);

        /**
         * @todo format default value
         */
        //$this->default = $this->owner->lib->formatDate($this->default);
    }
}