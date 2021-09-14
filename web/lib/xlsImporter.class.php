<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

abstract class xlsImporter extends ancestor {
	private $spreadsheet;
	private $hasHeaderRow = true;

	protected $totalRows = 0;

	abstract protected function init($params);
	abstract protected function processRow($data, $rowIndex);
	abstract protected function getResult():array;

	public function __construct() {
		parent::__construct();
	}

    protected function hasHeaderRow($hasHeaderRow = true){
		$this->hasHeaderRow = $hasHeaderRow;
		return $this;
	}

    protected function formatTimestamp($timestamp){
        return date('Y-m-d H:i:s', strtotime(str_replace('.', '-', $timestamp)));
    }

    protected function zeroOnEmpty($data){
        return (Empty($data) ? 0 : $data);
    }

	public function doImport($fileName, $params = []){
        $result = false;
        $r = 0;

	    $this->init($params);
	    $this->loadFile($fileName);

        $worksheet = $this->spreadsheet->getActiveSheet();
        if($worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                $data = [];
                if ($r === 0 && $this->hasHeaderRow) {
                    $r++;
                    continue;
                }

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                foreach ($cellIterator as $cell) {
                    $key = preg_replace("/[^A-Z]+/", "", $cell->getCoordinate());
                    $data[$key] = $cell->getValue();
                }

                if($this->processRow($data, $r)){
                    $this->totalRows++;
                }

                $r++;
            }

            $result = $this->getResult();
        }

		return $result;
	}

    private function loadFile($inputFileName){
        $inputFileType = IOFactory::identify($inputFileName);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true);

        $worksheetData = $reader->listWorksheetInfo($inputFileName);
        $reader->setLoadSheetsOnly($worksheetData[0]['worksheetName']);
        $this->spreadsheet = $reader->load($inputFileName);
    }

}