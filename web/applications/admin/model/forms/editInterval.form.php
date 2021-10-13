<?php
class editIntervalForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['si_id', 'si_sm_id']);
    }

    public function setup() {
       $this->boxed = false;

       $this->addControls(
           (new groupRow('row1', '', 'm-0 p-0'))->addElements(
               (new inputText('si_time_start', 'LBL_TIME_START'))
                   ->setColSize('col-2')
                   ->setIcon('fal fa-clock')
                   ->setPlaceholder('10:00')
                   ->setMaxLength(5)
                   ->onlyNumbers(':')
                   ->addClass('text-right'),
               (new groupHtml('txt', '<div class="text-center mt-2 pt-4">-</div>')),
               (new inputText('si_time_end', 'LBL_TIME_END'))
                   ->setColSize('col-2')
                   ->setIcon('fal fa-clock')
                   ->setPlaceholder('12:00')
                   ->setMaxLength(5)
                   ->onlyNumbers(':')
                   ->addClass('text-right'),
               (new inputButton('btnAddInterval', '', 1))
                   ->addClass('addInterval')
                   ->addEmptyLabel()
                   ->setIcon('fal fa-plus')
                   ->addData('smId', $this->parameters['smId'])
                   ->setColSize('col-2')
           )
       );
	}

}
