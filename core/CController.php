<?php

/** @property CModel $model */
class CController {

	// arguments passed in url, for selected action
	public $arguments;
	// page title for each page can be different
	public $title;
	// model class for controller
	public $model = null;
	// variables for output templates
	public $data = [];
	// some special vars for internal use
	private $hprint = false;
	private $viewFolder = './views/';
	private $classname;
	private $grants;
	protected $authdata = [];
	protected $scripts;

	function __construct() {

		$this->title = Configuration::$siteName;
		$this->arguments = [];
		$this->classname = str_replace('Controller', '', get_class($this));

		$this->scripts = Configuration::$scriptList;
		$this->data['brand'] = Configuration::$brandName;

		$this->authdata = Session::get('auth');
		$this->data['authdata'] = $this->authdata;

		/* Перечень прав и доступ ролей к ним */
		$this->grants = [
			'ACE_NEW'       => [Configuration::$ROLE_USER, Configuration::$ROLE_NSS],
			'ACE_AGREE'     => [Configuration::$ROLE_USER, Configuration::$ROLE_NSS],
			'ACE_ACCEPT'    => [Configuration::$ROLE_ME, Configuration::$ROLE_NSS],
			'ACE_OPEN'      => [Configuration::$ROLE_NSS],
			'ACE_COMPLETE'  => [Configuration::$ROLE_USER],
			'ACE_CLOSE'     => [Configuration::$ROLE_NSS],
			'ACE_PROLONG'   => [Configuration::$ROLE_USER, Configuration::$ROLE_NSS],
			'ACE_DELETE'    => [Configuration::$ROLE_NSS],
		];

		// и к каждуму гранту добавим роль АДМИНА. На то он и админ
		foreach ($this->grants as $ace => $roles) $this->grants[$ace][] = Configuration::$ROLE_ADMIN;

		// сформируем и проинициализируем модель по умолчанию
		// для текущего контроллера.
		// её можно будет переопределить в конструкторе потомка
		$defaultModel = $this->classname . "Model";
		if (class_exists($defaultModel))
			$this->model = new $defaultModel();
	}

	public function render($view, $endpage = true) {

		$this->data['elist'] = CModel::getErrorList();
		extract($this->data);
		if (!$this->hprint) {
			include $this->viewFolder . 'hcommon.php';
			$this->hprint = true;
		}

		$viewfile = strtolower($this->viewFolder . $this->classname . "/$view.php");
		if (file_exists($viewfile)) {
			include $viewfile;
		}

		if ($endpage) {
			include $this->viewFolder . 'fcommon.php';
		}
	}

	public function renderPartial($view) {

		ob_start();
		ob_implicit_flush(false);

		extract($this->data);
		$viewfile = strtolower($this->viewFolder . $this->classname . "/$view.php");
		if (file_exists($viewfile)) {
			include $viewfile;
		}

		return ob_get_clean();
	}

	public function preparePopup($etext, $eclass = 'alert-danger') {
		if (!headers_sent() && $etext) {
			setcookie('status', nl2br($etext), time() + 10, '/');
			setcookie('class', $eclass, time() + 10, '/');
		}
		return 0;
	}

	public function redirect($param = null) {

		if (is_null($param)) $param = '/';
		if (!is_array($param)) {
			$param = [
				'location' => $param,
			];
		}

		if (isAjax()) {
			// если идет вызов редиректа при ajax-запросе,
			// то значит сессия устарела
			// но работе это мешать не должно
			return;
		}

		$location = get_param($param, 'location', '/');
		if (get_param($param, 'back') === 1)
			$location = get_param($_SERVER, 'HTTP_REFERER', $location);
		if (get_param($param, 'soft') === 1) {
			$delay = get_param($param, 'delay', 3);
			printf('<meta http-equiv="refresh" content="%d; url=%s">', $delay, $location);
		} else {
			header("Location: $location");
			die;
		}
	}

	public function createActionUrl($pAction, $pArguments = null) {
		return sprintf("/%s/%s/", strtolower($this->classname), $pAction);
	}

	// абстракции просто чтоб были (переопределяются в потомках)
	public function actionIndex() {

		$this->render('');
	}

	public function ajaxIndex() {

	}

	public function isGrantToMe($ace, $department_id = null) {

		$my_dep = get_param($this->authdata, 'depid');
		$my_role = get_param($this->authdata, 'role_id');

		/**
		 * Принципиально не хочу хранить информацию о правах роли в БД
		 * Во-первых лишний запрос
		 * Во-вторых отсутствие возможности измененя прав на лету (при запросе нужно будет перелогиниваться)
		 * В-третьих меньше косяков, если вдруг случайно дал не той роли право
		 **/

		$result = false;
		$result |= in_array($my_role, get_param($this->grants, $ace, []));
		if ($department_id !== null) {
			// Если нужно проверить идентификатор отдела (ННСа и Админа это не касается)
			$result &= in_array($my_role, [Configuration::$ROLE_NSS, Configuration::$ROLE_ADMIN]) ?: $department_id === $my_dep;
		}

		return $result;
	}
}
