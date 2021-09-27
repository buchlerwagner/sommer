<?php
class router extends model {
	const MESSAGE_INFO = 'info';
	const MESSAGE_SUCCESS = 'success';
	const MESSAGE_WARNING = 'warning';
	const MESSAGE_ERROR = 'error';
	const MESSAGE_DANGER = self::MESSAGE_ERROR;

	public $page;
	public $originalPage;
	public $params = [];
	public $menu = [];
	public $currentMenu = [];
	public $context = 'app';
	public $application = 'admin';
	public $theme = 'admin';
	public $layout = 'main';
	public $customHeaders = false;
	public $httpHeaders = [];
	public $root = '';
	public $host = '';
	public $domain = '';
	public $data = [];

	public $sessionId;
	public $shopId = 0;
	public $language;
	public $machineId;
	public $currency;
	public $currencySign;
	public $hostConfig = [];
	public $settings = false;

	/**
	 * @var view
	 */
	public $view;

	/**
	 * @var mysql
	 */
	public $db;

	/**
	 * @var translate
	 */
	public $translate;

	/**
	 * @var functions
	 */
	public $lib;

    /**
     * @var lists
     */
    public $lists;

	/**
	 * @var mp_memcache
	 */
	public $mem;

	/**
	 * @var email
	 */
	public $email;

	/**
	 * @var user
	 */
	public $user;

	/**
	 * @var cart
	 */
	public $cart = null;

	public $output = OUTPUT_HTML;
	protected $messages = [];

	public function __construct(){
		parent::__construct();

		try {
			$this->db = db::factory(DB_TYPE, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_WEB, DB_ENCODING);
			$this->db->connect();
		} catch (Exception $e){
			exit('Could not connect to database.');
		}

		if (class_exists('Memcache')) {
			$this->mem = new mp_memcache(MEMCACHE_HOST, MEMCACHE_PORT, MEMCACHE_COMPRESS);
		} else if (class_exists('Memcached')) {
			$this->mem = new mp_memcached(MEMCACHE_HOST, MEMCACHE_PORT);
		}

        $this->lib = $this->addByClassName('functions');
        $this->lists = $this->addByClassName('lists');
		$this->user = $this->addByClassName('user');
		$this->email = $this->addByClassName('email');

        $this->setHost($_SERVER['HTTP_HOST']);

        if(SESSION_ON_SUBDOMAINS) {
            if(substr_count($this->host, '.') > 1) {
                $mainDomain = substr($this->host, strpos($this->host, '.'), 100);
            }else{
                $mainDomain = '.' . $this->host;
            }

            session_set_cookie_params(0, '/', $mainDomain);
            ini_set('session.cookie_domain', $mainDomain);
        }

		define('APP_ROOT', DOC_ROOT . 'web/applications/' . $this->application . '/');
	}

    private function setHost($host){
        if($this->hostConfig = $this->getHostConfig($host)){
            $this->host = $this->hostConfig['host'];

            $this->shopId = $this->hostConfig['shopId'];
            $this->language = $this->hostConfig['defaultLanguage'];
            $this->application = $this->hostConfig['application'];
            $this->theme = $this->hostConfig['theme'];
            $this->currency = $this->hostConfig['defaultCurrency'];
            $this->settings = $this->lib->getWebShopSettings();
        }else{
            $this->shopId = 1;
            $this->host = DEFAULT_HOST;
            $this->host = DEFAULT_HOST;
            $this->language = DEFAULT_LANGUAGE;
            $this->application = DEFAULT_APPLICATION;
            $this->theme = DEFAULT_THEME;
            $this->currency = DEFAULT_CURRENCY;
            $this->settings = [];
        }

        $this->currencySign = $GLOBALS['CURRENCIES'][$this->currency]['sign'];

        $protocol = 'http://';
        if(!Empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] !== 'off' OR $_SERVER['SERVER_PORT'] == 443) {
            $protocol = 'https://';
        }

        $this->domain = $protocol . $this->host . '/';
    }

    private function getHostConfig($host){
        $host = strtolower(trim($host));

        $config = $this->mem->get(HOST_SETTINGS . $host);
        if(!$config){
            $h = $this->db->getFirstRow(
                $this->db->genSQLSelect(
                    'hosts',
                    [],
                    [
                        'host_host' => $host
                    ]
                )
            );
            if($h){
                $config = [
                    'shopId' => (int) $h['host_shop_id'],
                    'host' => $h['host_host'],
                    'name' => $h['host_name'],
                    'forceSSL' => ($h['host_force_ssl']),
                    'defaultLanguage' => $h['host_default_language'],
                    'languages' => explode('|', trim($h['host_languages'], '|')),
                    'application' => $h['host_application'],
                    'theme' => $h['host_theme'],
                    'defaultCurrency' => $h['host_default_currency'],
                    'currencies' => explode('|', trim($h['host_currencies'], '|')),
                    'timeZoneID' => $h['host_timezone'],
                    'country' => $h['host_country'],
                    'publicSite' => rtrim($h['host_public_site'], '/') . '/',
                    'defaultEmail' => $h['host_default_email'],
                    'production' => ($h['host_production']),
                ];

                $this->mem->set(HOST_SETTINGS . $host, $config);
            }
        }

        return $config;
    }

	public function init(){
		session_start();

		$this->sessionId = session_id();
		$this->machineId = getMachineId();

		$this->messages = $this->getSession(SESSION_MESSAGES);
		$this->user->init();

		include(APP_ROOT . 'menu.php');
		$this->menu = $GLOBALS['MENU'];

		if ($this->user->isLoggedIn()) {
            //

		}else{
            if($this->application == 'admin'){
                $this->page = 'login';
                if (!empty($this->menu['login']['layout'])) {
                    $this->layout = $this->menu['login']['layout'];
                }
            }
		}

        $this->setLanguage();
		$this->translate = $this->addByClassName('translate');
		$this->translate->init($this->context);
		$this->parseUrl();

		if ($this->output == OUTPUT_HTML) {
			$this->view = $this->addByClassName('view');
		}

        if($this->application == 'shop'){
            /**
             * @var $cart cart
             */
            $this->cart = $this->addByClassName('cart');
            $this->cart->init(false, false);
            //$fbLogin = $this->user->getFBLoginUrl();
        }

        /*
        if($this->user->getUser() && $this->user->getUser()['force_pwchange'] && $this->page != 'logout'){
			if (!empty($this->user->getUser()['force_pwchange'])) {
				$this->addMessage('warning', 'LBL_PASSWORD_CHANGE_NEEDED', 'LBL_PASSWORD_CHANGE_NEEDED');
			}
			$this->page = 'change-pwd';
		}
        */

		$this->view->init();
		if ($this->page == 'ajax') {
		    if($this->user->isLoggedIn()) {
                $this->loadModel('ajax', $this->params[0]);
            }else{
		        exit();
            }
		} else {
			$this->loadModel('pages', $this->page);
		}
	}

	public function __destruct() {
		$this->setSession(SESSION_MESSAGES, $this->messages);
	}

	public function display(){
		if ($this->output == OUTPUT_HTML) {
			$this->view->renderPage();

		} elseif ($this->output == OUTPUT_JSON) {
			header('Content-type: application/json; charset=utf-8');
			echo json_encode($this->data, JSON_UNESCAPED_UNICODE);

		} elseif ($this->output == OUTPUT_RAW) {

			echo $this->data;
		}
	}

	private function parseUrl(){
		if(isset($_GET['path']) AND !Empty($_GET['path'])){
			if(substr($_GET['path'], -1) != '/') $_GET['path'] .= '/';
			$uri = explode('/', rtrim($_GET['path'], '/'));
			$this->root = str_repeat('../', count($uri));

			$accessRights = (!empty($this->user->getUser()['access_rights'])) ? array_keys($this->user->getUser()['access_rights']) : [];
			$menuItems =& $this->menu;

			foreach($uri as $i => $mKey) {
                $mKey = $this->findRealMenuKey($this->menu, $mKey);

				if (!empty($menuItems[$mKey])) {
					$menuItems[$mKey]['selected'] = 1;
					$this->currentMenu = $menuItems[$mKey];
					$this->currentMenu['name'] = ($menuItems[$mKey]['title'] ?: 'MENU_' . strtoupper($mKey));

					if (empty($menuItems[$mKey]['access']) || in_array($mKey, $accessRights)
						|| (!empty($menuItems[$mKey]['access']) && !is_bool($menuItems[$mKey]['access']) && in_array($menuItems[$mKey]['access'], $accessRights))) {
						if (!empty($menuItems[$mKey]['pagemodel'])) {
							$this->originalPage = $mKey;
							$this->page = $menuItems[$mKey]['pagemodel'];
						} else {
							$this->page = $mKey;
						}
						if (!empty($menuItems[$mKey]['layout'])) {
							$this->layout = $menuItems[$mKey]['layout'];
						}

						if(isset($menuItems[$mKey]['customheaders'])) {
							$this->customHeaders = $menuItems[$mKey]['customheaders'];
						}
					} else {
						// login required
						$this->originalPage = $_GET['path'];
						$this->page = 'login';
						if (!empty($this->menu['login']['layout'])) {
							$this->layout = $this->menu['login']['layout'];
						}
					}

					unset($uri[$i]);
					if (!empty($menuItems[$mKey]['items'])) {
						$menuItems =& $menuItems[$mKey]['items'];
					} else {
						break;
					}
				} else {
					if (empty($this->page)) {
						$this->data['pageTitle'] = 'MENU_404';
						$this->page = '404';
					}

					break;
				}
			}

			if (!empty($uri)) {
				$this->params = array_values($uri);
			}
		}

		if (empty($this->page)) {
			$this->page = 'index';
			$this->menu['index']['selected'] = 1;
			if (!empty($this->menu['index']['layout'])) {
				$this->layout = $this->menu['index']['layout'];
			}

			$this->currentMenu = $this->menu['index'];
			$this->currentMenu['name'] = 'MENU_INDEX';
		}

		if (empty($this->root)) {
			$this->root = './';
		}
	}

    private function findRealMenuKey($menu, $needle){
        $page = strtolower($needle);

        foreach($menu AS $key => $m){
            if($m['name'] == $needle){
                $page = $key;
                break;
            }

            if(!Empty($m['items'])){
                $page = $this->findRealMenuKey($m['items'], $needle);
            }
        }

        return $page;
    }

	private function setLanguage(){
		if(isset($_REQUEST['lang']) AND isset($GLOBALS['LANGUAGES'][strtolower($_REQUEST['lang'])])){
			$this->language = strtolower($_REQUEST['lang']);
		}else{
			$language = $this->getSession(SESSION_LOCALE);
			if (!empty($language)) {
				$this->language = $language;
			} else if (empty($this->language)) {
				$this->language = DEFAULT_LANGUAGE;
			}
		}

		$this->setSession(SESSION_LOCALE, $this->language);
	}

	public function getAllAccessRights($menuItems = [], $userGroup = false) {
		$result = [];

		if (empty($menuItems)) {
			$menuItems = $this->menu;
		}

		foreach($menuItems as $key => $m) {
			if (!empty($m['access'])) {

				if($userGroup && is_array($m['userGroups']) && !in_array($userGroup, $m['userGroups'])) {

				}else{
					if (is_bool($m['access'])) {
						$result[] = $key;
					} else if (!in_array($m['access'], $result)) {
						$result[] = $m['access'];
					}
				}
			}
			if (!empty($m['items'])) {
				$result = array_merge($result, $this->getAllAccessRights($m['items'], $userGroup));
			}
		}

		return $result;
	}

	public function getAllAccessMenus($menuItems = [], $exclude = [], $userGroup = false) {
		$result = [];

		if (empty($menuItems)) {
			$menuItems = $this->menu;
		}

		$i = 0;
		foreach($menuItems as $key => $m) {
			if($exclude && in_array($key, $exclude)){
				continue;
			}

			if($userGroup && is_array($m['userGroups']) && !in_array($userGroup, $m['userGroups'])){
				continue;
			}

			if (!empty($m['access'])) {
				$result[$i]['name'] = $key;
			}else {
				if (!empty($m['items'])) {
					if($res = $this->getAllAccessRights($m['items'], $userGroup)) {
                        $result[$i]['name'] = $key;
                        $result[$i]['items'] = $res;
                    }
				}
			}

			$i++;
		}

		return $result;
	}

	public function pageRedirect($url, enumHTTPHeaders $header = null){
        if($header){
            $this->addHttpHeader($header);
        }
		header("Location: " . $url);
		exit();
	}

	public function loadForm($name, $params = false, $alias = false) {
		$form = false;
		if ($this->loadModel('forms', $name . '.form')) {
			if (empty($alias)) $alias = $name;
			$form = $this->addByClassName($name . 'Form', $alias, [$params]);
            $form->init();
			$this->data['forms'][$alias] = $form;
		}
		return $form;
	}

	public function loadTable($name, $params = false, $alias = false, $forceReload = false) {
		$table = false;
		if ($this->loadModel('tables', $name . '.table')) {
			if (empty($alias)) $alias = $name;
			$table = $this->addByClassName($name . 'Table', $alias, [$params], $forceReload);
			$table->init();
			if (empty($table->form) && !$table->datatable) {
				$table->loadRows();
			}
			$this->data['tables'][$alias] = $table;
		}
		return $table;
	}

	/**
	 * Add message
	 * @param string $type [success, danger, warning, info]
	 * @param string $title
	 * @param string $label
	 * @return void
	 */
	public function addMessage($type, $title, $label) {
		$args = func_get_args();
		unset($args[0], $args[1]);

		if (empty($this->messages)) $this->clearMessages();
		$this->messages[] = [
			'type' 		=> $type,
			'title' 	=> $this->translate->getTranslation($title),
			'message' 	=> call_user_func_array([$this->translate, 'getTranslation'], $args),
		];
	}

	public function getMessages($json = false) {
		$result = $this->messages;
		$this->clearMessages();
		return ($json ? json_encode($result) : $result);
	}

	public function clearMessages() {
		$this->messages = [];
	}

	public function includeFile($file) {
		if (file_exists($file)) {
			include($file);
		}
	}

	public function setPageTitle($title){
        $this->data['pageTitle'] = $title;
    }

    public function setPageName($pageName){
        $this->data['pageName'] = $pageName;
    }

	public function setPageSubTitle($title){
        $this->data['pageSubTitle'] = $title;
    }

    public function setPageMetaData($data){
        if($data['seo']['title']) {
            $this->setPageTitle($data['seo']['title']);
        }elseif($data['pageTitle']) {
            $this->setPageTitle($data['pageTitle']);
        }

        if($data['pageKeyWords']) {
            $this->data['pageKeyWords'] = $data['pageKeyWords'];
        }

        if($data['seo']['url']) {
            $this->data['meta']['og:url'] = $data['seo']['url'];
        }elseif($data['pageUrl']) {
            $this->data['meta']['og:url'] = $data['pageUrl'];
        }

        if($data['pageType']) {
            $this->data['meta']['og:type'] = $data['pageType'];
        }else{
            $this->data['meta']['og:type'] = 'article';
        }

        if($data['name']) {
            $this->data['meta']['og:title'] = $data['name'];
        }

        if($data['seo']['description']) {
            $this->data['pageDescription'] = $data['seo']['description'];
            $this->data['meta']['og:description'] = $data['seo']['description'];
        }elseif($data['pageDescription']){
            $this->data['pageDescription'] = $data['pageDescription'];
            $this->data['meta']['og:description'] = $data['pageDescription'];
        }elseif($data['description']){
            $this->data['meta']['og:description'] = $data['description'];
        }

        if($data['seo']['image']) {
            $this->data['meta']['og:image'] = $data['seo']['image'];
            $this->data['meta']['og:image:secure_url'] = $data['seo']['image'];
        }elseif($data['pageImage']['absolute']) {
            $this->data['meta']['og:image'] = $data['pageImage']['absolute'];
            $this->data['meta']['og:image:secure_url'] = $data['pageImage']['absolute'];
        }

        if($this->settings['facebookAppId']) {
            $this->data['meta']['fb:app_id'] = $this->settings['facebookAppId'];
        }
    }

    public function addHttpHeader(enumHTTPHeaders $header){
        $this->httpHeaders[] = $header;
    }
}