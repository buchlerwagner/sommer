<?php
class PrintLabels extends docs {
    const PADDING_TOP   = 5;     // mm
    const PADDING_LEFT  = 5;     // mm
    const TOP_OFFSET    = 4;     // mm
    const LABEL_WIDTH   = 70;    // mm
    const LABEL_HEIGHT  = 36;    // mm

    const TOTAL_COLS    = 3;
    const TOTAL_ROWS    = 8;

    private $filters    = [];

    public function setFilters($filters){
        $this->filters = $filters;
        return $this;
    }

    protected function generateContent() {
        $this->setTemplate('print-labels');

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'orders',
                [],
                $this->createQuery()
            )
        );

        if($result){
            $row = 1;
            $col = 0;
            foreach($result AS $item){
                $col++;

                $x = self::PADDING_LEFT + (($col - 1) * self::LABEL_WIDTH);
                $y = self::PADDING_TOP + (($row - 1) * self::LABEL_HEIGHT) + self::TOP_OFFSET;

                $html  = '<b class="big">' . $item['orderNumber'] . '</b><br>';
                $html .= '<span class="small">' . $item['categoryName'] . '</span><br>';
                $html .= $item['productName'] . '<br>';
                if($item['variantName']) {
                    $html .= '<span class="small">' . $item['variantName'] . '</span><br>';
                }
                $html .= '<b>' . $item['quantity'] . ' ' . $item['unit'] . '</b><br>';


                $html .= '[' . ($item['shippingStoreName'] ?: 'kiszállítás') . ']<br>';

                $this->addFixedText($html, $x, $y, self::LABEL_WIDTH, self::LABEL_HEIGHT);

                if($col == self::TOTAL_COLS){
                    $col = 0;
                    $row++;

                    if($row > self::TOTAL_ROWS){
                        $row = 1;
                        $this->addFixedText(self::PAGE_BREAK);
                    }
                }
            }
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
                    case 'shippingDate_min':
                        $field = substr($field, 0, -4);
                        $where[$field]['greater='] = standardDate($values);
                        break;

                    case 'shippingDate_max':
                        $field = substr($field, 0, -4);
                        $where[$field]['less='] = standardDate($values);
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