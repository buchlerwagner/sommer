<?php
class PrintOrders extends docs {
    private $filters = [];

    public function setFilters($filters){
        $this->filters = $filters;
        return $this;
    }

    protected function generateContent() {
        $this->setTemplate('print-orders');

        $categories = $this->owner->lists->reset()->getCategories();
        $stores = $this->owner->lists->reset()->getShippingModes(true);

        if ($this->filters['shippingCode']) {

            $places = [];
            foreach($this->filters['shippingCode'] AS $code){
                $places[] = $stores[$code];
            }

            $this->setVar('delivery', implode(', ', $places));
        }

        if ($this->filters['categoryId']) {
            $this->setVar('category', $categories[$this->filters['categoryId']]);
        }

        if ($this->filters['shippingDate_min']) {
            $this->setVar('dateMin', $this->filters['shippingDate_min']);
        }

        if ($this->filters['shippingDate_max']) {
            $this->setVar('dateMax', $this->filters['shippingDate_max']);
        }

        if ($this->filters['orderDate_min']) {
            $this->setVar('orderDateMin', $this->filters['orderDate_min']);
        }

        if ($this->filters['orderDate_max']) {
            $this->setVar('orderDateMax', $this->filters['orderDate_max']);
        }

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'orders',
                [],
                $this->createQuery()
            )
        );

        if($result){
            $this->setVar('rows', $result);
        }
    }

    private function createQuery(){
        $where = [];
        $where['shopId'] = $this->owner->shopId;

        if (!empty($this->filters)) {
            foreach ($this->filters as $field => $values) {
                if (empty($values)) {
                    continue;
                }
                if (is_array($values)) {
                    foreach ($values as $key => $val) {
                        $values[$key] = $this->owner->db->escapestring($val);
                    }
                } else {
                    $values = $this->owner->db->escapestring($values);
                }
                switch ($field) {
                    case 'orderDate_min':
                        $field = substr($field, 0, -4);
                        $where[$field]['greater='] = standardDate($values) . " 00:00:00'";
                        break;

                    case 'orderDate_max':
                        $field = substr($field, 0, -4);
                        $where[$field]['less='] = standardDate($values) . " 23:59:59'";
                        break;

                    case 'shippingDate_min':
                        $field = substr($field, 0, -4);
                        $where[$field]['greater='] = standardDate($values);
                        break;

                    case 'shippingDate_max':
                        $field = substr($field, 0, -4);
                        $where[$field]['less='] = standardDate($values);
                        break;

                    case 'shippingCode':
                        $codes = [];
                        foreach($values AS $v){
                            $codes[] = "'" . $v . "'";
                        }

                        $where[$field]['in'] = $codes;
                        break;

                    default:
                        $where[$field] = $values;
                        break;
                }
            }
        }else{
            $where['shippingDate'] = date('Y-m-d');
        }

        return $where;
    }
}