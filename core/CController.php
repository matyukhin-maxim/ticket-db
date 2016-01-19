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
	private   $hprint     = false;
	private   $viewFolder = './views/';
	private   $classname;
	protected $authdata   = [];
	protected $scripts;

	function __construct() {

		$this->title = Configuration::$siteName;
		$this->arguments = [];
		$this->classname = str_replace('Controller', '', get_class($this));

		$this->scripts = Configuration::$scriptList;
		$this->data['brand'] = Configuration::$brandName;

		$this->authdata = Session::get('auth');
		$this->data['authdata'] = $this->authdata;

		// сформируем и проинициализируем модель по умолчанию
		// для текущего контроллера.
		// её можно будет переопределить в конструкторе потомка
		$defaultModel = $this->classname . "Model";
		if (class_exists($defaultModel))
			$this->model = new $defaultModel();
	}

	public function render($view, $endpage = true) {

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
			$elist = CModel::getErrorList();
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

	public function prepareError($etext, $eclass = 'alert-danger') {
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

	public function createActionUrl($pAction) {
		return sprintf("/%s/%s/", strtolower($this->classname), $pAction);
	}

	// абстракции просто чтоб были (переопределяются в потомках)
	public function actionIndex() {

		$this->render('');
	}

	public function ajaxIndex() {

	}

}
