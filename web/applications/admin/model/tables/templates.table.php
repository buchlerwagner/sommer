<?php
class templatesTable extends table {

	public function setup() {
		$this->dbTable = DB_NAME_WEB . '.templates';
		$this->keyFields = ['mt_id'];
		$this->formName = 'template';
		$this->delete = false;
		$this->header = true;

		$this->settings['display']    = 25;
		$this->settings['orderfield'] = 'mt_id';
		$this->settings['orderdir']   = 'asc';

        $this->hideCounter = true;

		$this->addColumns(
            (new column('mt_type', 'LBL_TYPE', 1))
                ->addClass('text-center')
                ->setTemplate('<b>{{ val }}</b>'),
            (new column('mt_key', 'LBL_TEMPLATE', 9))
                ->setTemplate('{{ _("LBL_TEMPLATE_" ~ val) }}{% if row.mt_description %}<br><i class="text-muted text-sm">{{ row.mt_description }}</i>{% endif %}'),
            new columnHidden('mt_description')
        );
	}
}
