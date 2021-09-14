<?php
trait elementOptions {
    private $options = [];

    public function setOptions(array $options) {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array {
        return $this->options;
    }
}