<?php
class filterForm extends formBuilder {
	protected $parentTable = false;
	public $hasFilter = false;

    public function setupKeyFields() {
    }

	public function setup() {
		$this->boxed = false;

        $this->addButtons(
            (new buttonSubmit('search', 'BTN_SEARCH', 'btn btn-warning'))->setName('save'),
            (new buttonSubmit('resetSearch', 'BTN_CLEAR_FILTERS', 'btn btn-light ml-3'))->setName('reset')
        );
	}

	public function loadValues() {
		$this->values = $this->getSession($this->name);
		if (empty($this->values)) {
			$presets = $this->getFilterPresets();
			if (!empty($presets)) {
				$presets = array_keys($presets);
				$this->values = $this->getFilterPreset($presets[0]);
				$this->setSession($this->name, $this->values);
			}
		}

		if(!Empty($this->values)){
			$this->hasFilter = true;
		}
	}

	public function saveValues() {
		$this->setSession($this->name, $this->values);
		$presets = $this->getFilterPresets();
		if (!empty($presets)) {
			$presets = array_keys($presets);
			$this->saveFilterPreset($presets[0]);
		} else {
			$this->saveFilterPreset(null, 'default');
		}

		$this->owner->pageRedirect('./');
	}

	public function reset() {
		$this->delSession($this->name);
		$presets = $this->getFilterPresets();
		if (!empty($presets)) {
			$presets = array_keys($presets);
			$this->values = [];
			$this->saveFilterPreset($presets[0]);
		}

		if($this->parentTable){
			$this->delSession('table_settings_' . $this->parentTable);
		}

		$this->hasFilter = false;
		$this->owner->pageRedirect('./');
	}

	private function saveFilterPreset($id = null, $name = null) {
		if (!empty($this->name) && !empty($this->owner->user->id)) {
			$db = $this->owner->db;
			if (empty($id)) {
				$db->sqlQuery(
					$db->genSQLInsert(
						DB_NAME_WEB . '.filter_presets',
						[
							'fp_us_id'  => $this->owner->user->id,
							'fp_filter' => $this->name,
							'fp_name'   => $name,
							'fp_values' => json_encode($this->values)
						]
					)
				);
			} else {
				$updateFields = [
					'fp_values' => json_encode($this->values)
				];
				if (!empty($name)) {
					$updateFields['fp_name'] = $name;
				}
				$db->sqlQuery(
					$db->genSQLUpdate(
						DB_NAME_WEB . '.filter_presets',
						$updateFields,
						[
							'fp_us_id'  => $this->owner->user->id,
							'fp_filter' => $this->name,
							'fp_id'     => $id
						]
					)
				);
			}
		}
	}

	private function getFilterPresets() {
		$result = [];
		if (!empty($this->name) && !empty($this->owner->user->id)) {
			$db = $this->owner->db;
			$res = $db->getRows(
				"SELECT fp_id, fp_name FROM " . DB_NAME_WEB . ".filter_presets WHERE fp_us_id = '" . $this->owner->user->id . "' AND fp_filter = '" . $db->escapeString($this->name) . "'"
			);
			if (!empty($res)) {
				foreach($res as $row) {
					$result[$row['fp_id']] = $row['fp_name'];
				}
			}
		}
		return $result;
	}

	private function getFilterPreset($id) {
		$result = [];
		if (!empty($id) && !empty($this->owner->user->id)) {
			$db = $this->owner->db;
			$row = $db->getFirstRow(
				"SELECT fp_values FROM " . DB_NAME_WEB . ".filter_presets WHERE fp_us_id = '" . $this->owner->user->id . "' AND fp_id = '" . $db->escapeString($id) . "'"
			);
			if (!empty($row['fp_values'])) {
				$result = json_decode($row['fp_values'], true);
			}
		}
		return $result;
	}

}
