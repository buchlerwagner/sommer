<?php
abstract class docs extends ancestor {
	const PDF_INLINE 	= 'I';
	const PDF_DOWNLOAD 	= 'D';
	const PDF_FILE 		= 'F';
	const PDF_STRING 	= 'S';

    const PAGE_BREAK = '<pagebreak>';

    private $fileMode = self::PDF_INLINE;
    private $fileName = 'doc.pdf';

    private $path = '';
    private $options = [];
    private $pdfTemplate = false;
    private $template = false;
    private $data = [];
    private $html = '';
    private $fixedTexts = [];
    private $printDialog = false;

    abstract protected function generateContent();

    public function __construct() {
        $this->setOption('format', 'A4');
        ini_set("pcre.backtrack_limit", "5000000");
    }

	public function setOptions(array $options){
        foreach($options AS $key => $value){
            $this->setOption($key, $value);
        }
        return $this;
    }

	public function setOption($key, $value){
        $this->options[$key] = $value;
        return $this;
    }

    public function setData($data){
        $this->data = $data;
        return $this;
    }

    public function setVar($key, $value){
        $this->data[$key] = $value;
        return $this;
    }

    public function setFileName($fileName){
        $this->fileName = $fileName;
        return $this;
    }

    public function addFixedText($html, $x = 0, $y = 0, $w = 0, $h = 0, $overflow = 'auto'){
        $this->fixedTexts[] = [
            'html' => $html,
            'x' => $x,
            'y' => $y,
            'w' => $w,
            'h' => $h,
            'overflow' => $overflow,
        ];
        return $this;
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

    public function print(){
        $this->printDialog = true;
        return $this;
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
        $this->renderContent();

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
        $pdf->showImageErrors = false;
        $pdf->SetDisplayMode('fullpage');

        if($this->pdfTemplate) {
            $pdf->SetDocTemplate($this->pdfTemplate, true);
        }

        if (!empty($header)) {
            $pdf->SetHTMLHeader($header);
        }

        if($html = $this->getHtml()) {
            $pdf->WriteHTML($html);
        }

        if($this->fixedTexts){
            foreach($this->fixedTexts AS $data){
                if($data['html'] == self::PAGE_BREAK){
                    $pdf->AddPage();
                }else{
                    $pdf->WriteFixedPosHTML($data['html'], $data['x'], $data['y'], $data['w'], $data['h'], $data['overflow']);
                }
            }
        }

        if (!empty($footer)) {
            $pdf->SetHTMLFooter($footer);
        }

        if($this->printDialog) {
            $pdf->SetJS('this.print();');
        }

        if ($this->fileMode == self::PDF_STRING) {
            return $pdf->Output($this->fileName, $this->fileMode);
        } else {
            $pdf->Output($this->path . $this->fileName, $this->fileMode);
            return true;
        }
    }

    protected function setTemplate(string $template){
        $this->template = $template;
        return $this;
    }

    protected function getTemplate($template){
        $content = $this->owner->lib->getTemplate($template);
        if($content['template']){
            $this->template = $content['template'];
        }else{
            $this->template = $content['tag'];
        }

        $this->setVar('title', $content['title']);
        $this->setVar('template', $content['text']);

        return $content;
    }

    protected function renderContent(){
        $this->setVar('shopId', $this->owner->shopId);

        if($this->template) {
            $this->setHtml($this->owner->view->renderContent($this->template, $this->data, false, false));
        }
        return $this;
    }
}
