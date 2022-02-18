<?php
class salesReportTable extends table {

	public function setup() {
		$this->dbTable = 'daily_sales';
        $this->where  = 'shopId = ' . $this->owner->shopId;
        $this->where .= ' AND orderDate >= "' . date('Y-m-d 00:00:00') . '" AND orderDate <= "' . date('Y-m-d 23:59:59') . '"';

        $this->subTable = false;
		$this->options = false;
		$this->delete = false;
		$this->header = true;
		$this->edit = false;
		$this->view = true;
		$this->hideCounter = true;

        $this->settings['display']    = 200;
		$this->settings['orderfield'] = 'orderDate';
		$this->settings['orderdir']   = 'DESC';

        $this->addGroup('orderOrigin', 'originStoreName');

        $this->summarize = [
            0 => [
                0 => [
                    'caption' => 'LBL_TOTAL',
                    'class' => 'col-9',
                    'colspan' => 4
                ],
                1 => [
                    'field'     => 'netTotal',
                    'class'     => 'col-1',
                    'unitfield' => 'currency',
                    'where'     => $this->where
                ],
                2 => [
                    'field'     => 'vat',
                    'class'     => 'col-1',
                    'unitfield' => 'currency',
                    'where'     => $this->where
                ],
                3 => [
                    'field'     => 'grossTotal',
                    'class'     => 'col-1',
                    'unitfield' => 'currency',
                    'where'     => $this->where
                ]
            ]
        ];

        $this->addColumns(
            (new column('orderStatus', 'LBL_ORDER_STATUS', 1))
                ->setTemplate('{{ orderState(val)|raw }}')
                ->addClass('text-center'),
            (new column('orderNumber', 'LBL_ORDER_NUMBER', 2)),
            (new column('productName', 'LBL_PRODUCT_TITLE', 4))
                ->setTemplate('{{ val }}{% if row.variantName %}<br><span class="small text-muted">{{ row.variantName }}</span>{% endif %}'),
            (new column('quantity', 'LBL_QUANTITY', 2))
                ->setTemplate('{{ val }} {{ row.unit }}')
                ->addClass('text-right'),
            (new column('netTotal', 'LBL_NET_TOTAL', 1))
                ->setTemplate('{{ _price(val, row.currency) }}')
                ->addClass('text-right'),
            (new column('vat', 'LBL_VAT', 1))
                ->setTemplate('{{ _price(val, row.currency) }}<br><span class="small text-muted">({{ row.vatKey }}%)</span>')
                ->addClass('text-right'),
            (new column('grossTotal', 'LBL_GROSS_TOTAL', 1))
                ->setTemplate('{{ _price(val, row.currency) }}')
                ->addClass('text-right'),

            new columnHidden('currency'),
            new columnHidden('vatKey'),
            new columnHidden('variantName'),
            new columnHidden('unit')
        );

	}

    public function loadRows() {
        $this->settings['filters'] = $this->createQuery();
        parent::loadRows();
    }

    public function getFilters(){
        return $this->getSession('salesReportFilters');
    }

    private function createQuery(){
        $where = [];

        $filterValues = $this->getFilters();

        if (!empty($filterValues)) {
            $this->where = 'shopId = ' . $this->owner->shopId;

            foreach ($filterValues as $field => $values) {
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
                        $where[$field] = substr($field, 0, -4) . " >= '" . standardDate($values) . "'";
                        break;

                    case 'orderDate_max':
                        $where[$field] = substr($field, 0, -4) . " <= '" . standardDate($values) . "'";
                        break;
                    default:
                        $where[$field] = $field . " = '$values'";
                        break;

                    case 'isPaid':
                        if($values == 1){
                            $where[$field] = "isPaid = 1";
                        }elseif($values == -1){
                            $where[$field] = "isPaid = 0";
                        }
                        break;
                }
            }
        }

        return $where;
    }

}
