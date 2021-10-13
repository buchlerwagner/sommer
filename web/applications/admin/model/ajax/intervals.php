<?php
/**
 * @var $this router
 */
$this->output = OUTPUT_JSON;
$data = [];

$smid = (int) $this->db->escapeString($_REQUEST['smid']);

if($smid) {
    $action = $this->params[1];
    switch ($action) {
        case 'add':
            $start = $this->db->escapeString($_REQUEST['start']) . ':00';
            $end = $this->db->escapeString($_REQUEST['end']) . ':00';

            $this->db->sqlQuery(
                $this->db->genSQLInsert(
                    DB_NAME_WEB . '.shipping_intervals',
                    [
                        'si_sm_id' => $smid,
                        'si_shop_id' => $this->shopId,
                        'si_time_start' => $start,
                        'si_time_end' => $end,
                    ]
                )
            );

            $data['id'] = $this->db->getInsertRecordId();

            break;

        case 'remove':
            if($_REQUEST['id']) {
                $id = (int) $this->db->escapeString($_REQUEST['id']);

                $this->db->sqlQuery(
                    $this->db->genSQLDelete(
                        DB_NAME_WEB . '.shipping_intervals',
                        [
                            'si_id' => $id,
                            'si_sm_id' => $smid,
                            'si_shop_id' => $this->shopId
                        ]
                    )
                );

                $this->data['ok'] = 1;
            }else{
                $this->data['ok'] = 0;
            }
            break;
    }
}