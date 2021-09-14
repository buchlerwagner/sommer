<?php
require_once  DOC_ROOT . 'vendor/autoload.php';

abstract class docs extends ancestor {
	const PDF_INLINE 	= 'I';
	const PDF_DOWNLOAD 	= 'D';
	const PDF_FILE 		= 'F';
	const PDF_STRING 	= 'S';

    private $fileMode = self::PDF_INLINE;
    private $fileName = 'doc.pdf';

    private $path = '';
    private $options = [];
    private $pdfTemplate = false;
    private $template = false;
    private $data = [];
    private $html = '';

    abstract protected function generateContent();

    public function __construct() {
        $this->setOption('format', 'A4');
        ini_set("pcre.backtrack_limit", "5000000");
    }

	public function setOptions(array $options){
        foreach($options AS $key => $value){
            $this->setOption($key, $value);
        }
    }

	public function setOption($key, $value){
        $this->options[$key] = $value;
        return $this;
    }

    public function setData($key, $value){
        $this->data[$key] = $value;
        return $this;
    }

    public function setFileName($fileName){
        $this->fileName = $fileName;
    }

    public function setHtml($html){
        $this->html = $html;
        return $this;
    }

    public function setPdfTemplate($template){
        $this->pdfTemplate = $template;
        return $this;
    }

	public function getHtml(){
		return $this->html;
	}

	public function setPath($path){
		$this->path = rtrim($path, '/') . '/';

		if(!is_dir($this->path)){
			@mkdir($this->path, 0777, true);
			@chmod($this->path, 0777);
		}

		return $this;
	}

	/**
	 * Set PDF generation file mode
	 * @param string $mode
	 */
	public function setFileMode($mode = self::PDF_INLINE){
		$this->fileMode = $mode;
		return $this;
	}

    /**
     * HTML 2 PDF generálás (with mPDF)
     * @link http://mpdf1.com/manual/index.php
     *
     * @param string $header
     * @param string $footer
     * @throws \Mpdf\MpdfException
     * @return mixed
     */
    public function getPDF($header = '', $footer = '') {
        $this->generateContent();

        if (isset($_REQUEST['debug'])) {
            if ($_REQUEST['debug'] == 'data') {
                dd($this->data);
            }
            if ($_REQUEST['debug'] == 'html') {
                print str_replace('<pagebreak />', '<hr style="margin:40px 0;color: blue;">', $this->getHtml());
                exit();
            }
        }

        $pdf = new \Mpdf\Mpdf($this->options);
        $pdf->showImageErrors = true;
        $pdf->SetDisplayMode('fullpage');

        if($this->pdfTemplate) {
            $pdf->SetDocTemplate($this->pdfTemplate, true);
        }

        if (!empty($header)) {
            $pdf->SetHTMLHeader($header);
        }

        $pdf->WriteHTML($this->getHtml());

        if (!empty($footer)) {
            $pdf->SetHTMLFooter($footer);
        }

        if ($this->fileMode == 'S') {
            return $pdf->Output($this->fileName, $this->fileMode);
        } else {
            $pdf->Output($this->path . $this->fileName, $this->fileMode);
            return true;
        }
    }

    protected function getTemplate($template){
        $content = $this->owner->lib->getTemplate($template);
        if($content['template']){
            $this->template = $content['template'];
        }else{
            $this->template = $content['tag'];
        }

        $this->setData('title', $content['title']);
        $this->setData('template', $content['text']);

        return $content;
    }

    protected function renderContent(){
        if($this->template) {
            $this->setHtml($this->owner->view->renderContent($this->template, $this->data, false));
        }
        return $this;
    }
}
