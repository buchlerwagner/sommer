<?php
use Twig\Extension\AppExtension;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;
use Twig\Loader\FilesystemLoader;

class view extends ancestor {
	protected $css = [
		'files' 	=> [],
		'inline' 	=> []
	];
	protected $js = [
		'header' 	=> [
			'files' 	=> [],
			'inline' 	=> []
		],
		'footer' 	=> [
			'files' 	=> [],
			'inline' 	=> []
		],
	];

	protected $headers = [];

	/**
	 * @var $twig Twig\
	 */
	protected $twig;

	protected $common = [];

	public function init() {
		if($this->owner->page != 'ajax' AND isset($_SERVER['REMOTE_ADDR'])){
			if(file_exists(APP_ROOT . 'init.php')){
				require_once APP_ROOT . 'init.php';
			}
		}

		$this->common = [
			'domain'        => $this->owner->domain,
			'host'        	=> $this->owner->host,
			'languages'     => $GLOBALS['HOSTS'][$this->owner->host]['languages'],
			'currencies'    => $GLOBALS['HOSTS'][$this->owner->host]['currencies'],
			'language'      => $this->owner->language,
			'menu'          => $this->owner->lib->getAccessibleMenu($this->owner->menu),
			'params'        => $this->owner->params,
			'user'          => $this->owner->user->getUser(),
			'loggedin'      => $this->owner->user->isLoggedIn(),
			'accessLevel'   => $this->owner->user->getAccessLevel($this->owner->page),
			'images'  		=> $this->owner->root . 'images/',
			'production'  	=> (SERVER_ID == 'development' ? false : true),
			'sitedata'  	=> $GLOBALS['HOSTS'][$this->owner->host]['sitedata'],
		];
	}

	protected function getTwig() {
		if (empty($this->twig)) {
			include_once __DIR__ . '/twig.extensions.php';

            $theme = '';
            if($this->owner->theme) {
                $theme = '/' . $this->owner->theme;
            }

			$loader = new FilesystemLoader(
				[
					APP_ROOT . 'views' . $theme,
					APP_ROOT . 'views'  .$theme . '/macros',
                    DOC_ROOT . 'web/applications/common/views' . $theme,
                    DOC_ROOT . 'web/applications/common/views'  .$theme . '/formelements',
                    DOC_ROOT . 'web/applications/common/views'  .$theme . '/macros',
                    DOC_ROOT . 'web/applications/common/views'  .$theme . '/layouts',
                    DOC_ROOT . 'web/applications/common/doctemplates',
				],
				APP_ROOT . 'views'
			);

			$params = [
				'cache' => (TWIG_CACHE_ENABLED ? DIR_CACHE . 'twig/' . $this->owner->application : false),
				'debug' => true
			];

			$this->twig = new Twig\Environment($loader, $params);

			$this->twig->addExtension(new StringLoaderExtension());
			$this->twig->addExtension(new AppExtension());
			if($params['debug']) {
				$this->twig->addExtension(new DebugExtension());
			}
		}

		return $this->twig;
	}

	public function includeValidationJS() {
		$this->addJs('parsley/parsley.min.js', 'parsley', false, false, false); // form validation
		if($this->owner->language != 'en'){
			$this->addJs('parsley/i18n/'.$this->owner->language . '.js', 'parsley-' . $this->owner->language, false, false, false);
			$this->addJs('parsley/i18n/'.$this->owner->language . '.extra.js', 'parsley-' . $this->owner->language.'-extra', false, false, false);
		}
	}

	public function beforeRenderPage() {
	}

	public function renderPage(){
		$this->beforeRenderPage();

		if((!empty($this->owner->data['forms']) || !empty($this->owner->data['tables']))) {
			$this->includeValidationJS();
		}

		$twig = $this->getTwig();
		$this->common['page'] = $this->owner->page;
		$this->common['messages'] = $this->owner->getMessages(true);

		try {
			$content = $twig->load($this->owner->layout . '.layout' . TWIG_FILE_EXTENSION);
		} catch(Exception $e) {
			$this->owner->data['pageTitle'] = $this->owner->currentMenu['name'];
			$this->owner->data['error'] = $e->getMessage();
			$this->owner->page = '404';
			$content = $twig->load('404' . TWIG_FILE_EXTENSION);
		}

		if(Empty($this->owner->data['pageTitle']) && $this->owner->currentMenu['name']){
			$this->owner->data['pageTitle'] = $this->owner->currentMenu['name'];
		}
        if(Empty($this->owner->data['pageName']) && $this->owner->currentMenu['name']){
            $this->owner->data['pageName'] = $this->owner->currentMenu['name'];
        }

		if($this->owner->page != 'ajax'){
			$headerData = [
				'title'         => $this->owner->data['pageTitle'],
				'description'   => $this->owner->data['pageDescription'],
				'keywords'      => $this->owner->data['keyWords'],
				'language'      => $this->owner->language,
				'css'           => $this->css,
				'js'            => $this->js,
				'headers'       => $this->headers,
				'meta'       	=> $this->owner->data['meta'],
				'version'       => [
					'css'		=> VERSION_CSS,
					'js'		=> VERSION_JS,
				],
			];

			echo $twig->render('html_header' . TWIG_FILE_EXTENSION, $this->common + $headerData);
		}

		echo $content->render($this->common + $this->owner->data);

		if($this->owner->page != 'ajax'){
			echo $twig->render('html_footer' . TWIG_FILE_EXTENSION, ['js' => $this->js, 'jsVersion' => VERSION_JS]);
		}
	}

	/**
	 * Custom rendering
	 * @param string $content (template filename)
	 * @param array $data (data array used in template)
	 * @param bool $includeJs (include inline js or not)
	 * @param bool $addCommon (add common variables or not)
	 * @return string (generated html content)
	 */
	public function renderContent($content, $data, $includeJs = true, $addCommon = true) {
		$twig = $this->getTwig();

		if($addCommon){
			$data = array_merge($data, $this->common);
		}

		try {
			$result = $twig->render($content . TWIG_FILE_EXTENSION, $data);
			if ($includeJs && !empty($this->js['footer']['inline'])) {
				$result .= '<script>' . "\n";
				$result .= implode("\n", $this->js['footer']['inline']) . "\n";
				$result .= '</script>' . "\n";
			}

		} catch(Exception $e) {
			echo $e->getMessage();
			return false;
		}

		return $result;
	}

	public function addCss($fileName, $name = false, $forceInclude = false, $addVersion = true) {
		$add = false;
		$file = false;
		$external = false;

		if(!$forceInclude){
			$pos = strpos($fileName, 'http');
			if($pos === false){
				if(file_exists(WEB_ROOT . 'assets/css/' . $fileName )) {
					$add = true;
					$file = '/assets/css/' . $fileName;
				}elseif(file_exists(WEB_ROOT . 'assets/fonts/' . $fileName )){
					$add = true;
					$file = '/assets/fonts/' . $fileName;
				}elseif(file_exists(WEB_ROOT . 'vendor/' . $fileName )){
					$add = true;
					$file = '/vendor/' . $fileName;
				}
			}else{
				$external = true;
				$add = true;
				$file = $fileName;
			}
		}

		if($add || $forceInclude){
			if(!in_array($file, $this->css)){
				if($addVersion && !$external){
					$file .= '?v=' . VERSION_CSS;
				}

				if (empty($name)) {
					$this->css['files'][] = $file;
				} else {
					$this->css['files'][$name] = $file;
				}
			}
		}
	}

	public function addInlineCss($string) {
		$this->css['inline'][] = $string;
	}

	public function addJs($fileName, $name = false, $header = false, $forceInclude = false, $addVersion = true) {
		$add = false;
		$file = false;
		$external = false;
		$location = ($header ? 'header' : 'footer');

		if(!$forceInclude){
			$pos = strpos($fileName, 'http');
			if($pos === false){
				if(file_exists(WEB_ROOT . 'assets/js/' . $fileName)) {
					$add = true;
					$file = '/assets/js/' . $fileName;
				}elseif(file_exists(WEB_ROOT . 'vendor/' . $fileName )){
					$add = true;
					$file = '/vendor/' . $fileName;
				}
			}else{
				$add = true;
				$external = true;
				$file = $fileName;
			}
		}else{
			$file = $fileName;
		}

		if($add || $forceInclude){
			if(!in_array($file, $this->js[$location]['files'])){
				if($addVersion && !$external){
					$file .= '?v=' . VERSION_JS;
				}

				if (empty($name)) {
					$this->js[$location]['files'][] = $file;
				} else {
					$this->js[$location]['files'][$name] = $file;
				}
			}
		}
	}

	public function addInlineJs($string, $header = false) {
		$location = ($header ? 'header' : 'footer');
		$this->js[$location]['inline'][] = $string;
	}

	public function clearInlineJs() {
		$this->js['inline'] = [];
	}

	public function getCss() {
		return $this->css;
	}

	public function getJs() {
		return $this->js;
	}

	public function addHeader($header){
		if(!in_array($header, $this->headers)) {
			array_push($this->headers, $header);
		}
	}

	public function setPageTitle($title){
		$this->owner->data['pageTitle'] = $title;
	}

}