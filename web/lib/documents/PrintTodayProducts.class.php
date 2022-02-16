<?php
class PrintTodayProducts extends docs {
    private $table = null;

    public function setTable(table $table){
        $this->table = $table;

        return $this;
    }

    protected function generateContent() {
        if($this->table) {
            $this->setTemplate('print-today-products');

            $categories = $this->owner->lists->reset()->getCategories();
            $stores = $this->owner->lists->reset()->getShippingModes(true);
            $filters = $this->table->getFilters();

            if ($filters['shippingCode']) {
                $this->setVar('delivery', $stores[$filters['shippingCode']]);
            }

            if ($filters['categoryId']) {
                $this->setVar('category', $categories[$filters['categoryId']]);
            }

            if ($filters['shippingDate_min']) {
                $this->setVar('dateMin', $filters['shippingDate_min']);
            }

            if ($filters['shippingDate_max']) {
                $this->setVar('dateMax', $filters['shippingDate_max']);
            }

            $this->setVar('rows', $this->table->rows);
        }
    }
}