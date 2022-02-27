<?php
trait elementMask {

    public function setCustomMask($mask){
        $this->addClass('inputmask');
        $this->addData('inputmask', "'mask': '" . $mask . "'");

        return $this;
    }

    public function setMaskAlias($alias){
        $this->addClass('inputmask');
        $this->addData('inputmask-alias', $alias);

        return $this;
    }

    public function setMaskDecimal($digits = 2, $separator = ','){
        $this->setMaskAlias('decimal');

        $this->addData('inputmask-digits', $digits);
        $this->addData('inputmask-separator', $separator);

        return $this;
    }

    public function setMaskInteger($min = 0, $max = 0){
        $this->setMaskAlias('integer');

        if($min){
            $this->addData('inputmask-min', $min);
        }

        if($max){
            $this->addData('inputmask-max', $max);
        }

        return $this;
    }

    public function setMaskPercent(){
        $this->setMaskAlias('percentage');

        return $this;
    }

    public function setMaskDateTime($format, $showPlaceholder = true){
        $this->setMaskAlias('datetime');
        $this->addData('inputmask-inputformat', $format);

        if($showPlaceholder) {
            $this->setPlaceholder($format);
        }

        return $this;
    }

    public function setMaskUrl(){
        $this->setMaskAlias('url');

        return $this;
    }
}