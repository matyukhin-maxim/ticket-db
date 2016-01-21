<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 22.12.2015
 * Time: 15:11
 */

/** @todo Убрать из списка отделов для согласования собственный цех, чтобы не отправлять заявки самому себе */

/** @property TicketModel $model */
class TicketController extends CController {

	public function __construct() {
		parent::__construct();

		if (!$this->authdata) {
			$this->redirect('/auth/');
			return;
		}
	}

	public function actionIndex() {

		$this->actionCreate();
	}

	public function actionCreate() {

		// если какм-то чудом сюда попал не руководитель, то отправляем его на главную страницу
		if (get_param($this->authdata, 'role_id') !== Configuration::$ROLE_USER) {
			// Создать новую заявку может только Руководитель
			$this->render('block-create', false);
			$this->redirect([
				'location' => '/contents/',
				'soft' => 1,
				'delay' => 5,
			]);
			$this->render('');
			return;
		}

		$this->data['title'] = 'Новая заявка';
		$this->data['t_number'] = '-';
		$this->data['t_cdate'] = date('d.m.Y');
		$this->data['t_department'] = get_param($this->authdata, 'depname');
		$this->data['t_user'] = get_param($this->authdata, 'fullname');
		$this->data['t_message'] = '';
		$this->data['t_id'] = null;
		$this->data['parent'] = null;


		$this->render('', false);
		$my_dep = intval(get_param($this->authdata, 'depid', -1));

		// Список отделов с заблокировкой текущего цеха
		$departments = array_column($this->model->getDepartments(), 'title', 'id');
		$this->data['departments'] = CHtml::createOption('Не требуется', null);
		foreach ($departments as $id => $title) {
			$param = [];
			if ($id === $my_dep) $param['disabled'] = true;
			$this->data['departments'] .= CHtml::createOption($title, $id, $param);
		}

		// Список узлов
		$nodes = array_column($this->model->getNodes(), 'title', 'id');
		foreach($nodes as $id => $title) $this->data['nodes'] .= CHtml::createOption($title, $id);

		$this->data['buttons'] = join(PHP_EOL, [
			CHtml::createButton('Сохранить черновик', [
				'class' => 'btn btn-default btn-save strong',
				'data-confirm' => 0,
				'title' => 'Сохранить заявку в качестве черновика',
			]),
			CHtml::createButton('Отправить на согласование', [
				'class' => 'btn btn-primary btn-save',
				'data-confirm' => 1,
			]),
		]);

		$this->scripts[] = 'ticket-validate';
		$this->render('new-ticket');
	}

	public function actionEdit() {

		// Получим номер запрашиваемой заявки, ее статус и цех-создатель
		// для проверки возможности редактирования
		$req_id = get_param($this->arguments, 0);
		$req_id = filter_var($req_id, FILTER_VALIDATE_INT, [
			'options' => [
				'min_range' => 1,
				'default' => -1,
			],
		]);


		$this->render('', false);
		$ticket = $this->model->getTicketInfo($req_id);
		$my_dep = get_param($this->authdata, 'depid');
		$my_role = get_param($this->authdata, 'role_id');

		if (!$ticket) {
			$this->prepareError('Заявка не найдена', 'alert-warning');
			$this->redirect('/contents/');
		} elseif (get_param($ticket, 'status') == STATUS_DRAFT) {
			if (get_param($ticket, 'department_id') != $my_dep) {
				$this->prepareError('Редактировать черновики чужого цеха запрещено');
				$this->redirect('/contents/');
			}
		}

		$this->data['title'] = 'Редактирование заявки ';
		$this->data['t_number'] = get_param($ticket, 'number');
		$this->data['t_cdate'] = sqldate2human(get_param($ticket, 'dt_create'), 'd.m.Y');
		$this->data['t_message'] = get_param($ticket, 'message');
		$this->data['t_id'] = $req_id;
		$this->data['parent'] = '';

		$departments = $this->model->getDepartments();
		$tdepid = get_param($ticket, 'department_id');
		$deplist = array_column($departments, 'title', 'id');

		// цех создатель
		$this->data['t_department'] = get_param($deplist, $tdepid, '?');

		// цех согласователь
		$agree = get_param($ticket, 'agree');
		$adepid = get_param($agree, 'department_id', -1);
		$adepname = get_param($deplist, $adepid, null); // Название цеха, кто должен согласовать
		$ares = get_param($agree, 'result'); // null - пока не указанно; 0 - отказ; 1 - добро

		//$this->data['departments'] = generateOptions($departments, $adepid, 'Не требуется');
		// Список отделов с заблокировкой текущего цеха и отметкой выбранного
		$this->data['departments'] = CHtml::createTag('option', ['value'=>''], 'Не требуется');
		foreach ($deplist as $id => $title) {
			$param = [];
			$param['value'] = $id;
			if ($id === $my_dep) $param['disabled'] = true;
			if ($id === $my_dep) $param['selected'] = true;
			$this->data['departments'] .= CHtml::createTag('option', $param, $title);
		}

		// пользователь создавший заявку
		$uid = get_param($ticket, 'user_id');
		$user = $this->model->getUserName($uid);
		$this->data['t_user'] = get_param($user, 'fullname', '?');

		// Информация по выбранному узлу
		$nodes = $this->model->getNodes();
		$nodeid = get_param($ticket, 'node_id');
		$nlist = array_column($nodes, 'title', 'id');
		$this->data['nodename'] = get_param($nlist, $nodeid, '?');

		// получим список устройств узла, и нарисуем чекбокс-лист
		// но изменим порядок, чтобы спервы отрисовались отмеченные в заявке устройства
		$dlist = $this->model->getDevices($nodeid);
		$dlist = array_column($dlist, 'name', 'id');
		$devs = get_param($ticket, 'devices', []);

		$this->data['tstart'] = sqldate2human(get_param($ticket, 'dt_start'));
		$this->data['tstop'] = sqldate2human(get_param($ticket, 'dt_stop'));
		$this->data['resolutions'] = '';
		$ticket_status = intval(get_param($ticket, 'status', -1));

		if ($ticket_status === STATUS_DRAFT) {
			// кнопка удаления заявки доступна только из черновика
			$this->data['buttons'] = join(PHP_EOL, [
				CHtml::createLink('Удалить', "/ticket/delete/$req_id/", ['class' => 'btn btn-danger',]),
				CHtml::createButton('Сохранить черновик', [
					'class' => 'btn btn-default btn-save',
					'data-confirm' => 0,
					'title' => 'Сохранить заявку в качестве черновика',
				])
			]);
		}

		// СУПЕР ХАК
		// если у нас вторым аргументом пришел "clone", то в силу вступает МАГИЯ...
		if (get_param($this->arguments, 1) === 'clone') {

			if ($ticket_status !== STATUS_OPEN) {
				$this->prepareError('Продливать можно только открытую заявку');
				$this->redirect(['back' => 1]);
			} elseif ($tdepid !== $my_dep) {
				$this->prepareError('Продливать можно только заявки своего цеха', 'alert-warning');
				$this->redirect(['back' => 1]);
			}
			// по сути, мы открывем текущую заявку подменив ей статус на черновик,
			// что даст возможность редактирования ё параметров, но идентификатор заявки занулим,
			// что приведет к созданию новой заявки при сохранении
			$ticket_status = STATUS_DRAFT;
			$this->data['t_id'] = null;
			$this->data['t_message'] = sprintf("[Продление заявки #%s]\n%s", get_param($ticket, 'number'), get_param($ticket, 'message'));
			$this->data['button_delete'] = ''; // кнопку удаления убираем
			$this->data['t_number'] = '-';
			$this->data['tstart'] = sqldate2human(get_param($ticket, 'dt_stop'));
			$this->data['tstop'] = '';
			$this->data['parent'] = $req_id;
			$this->data['t_department'] = get_param($deplist, $my_dep, '?');
			$this->data['buttons'] = ''; // Удаляем забитые выше кнопки удаления и сохранения черновика. При клонировании они не нужны
		}

		$devices = '';
		// обходим перечень устройств указанных в заявке
		foreach ($devs as $item_id) {
			// получаем название из списка
			$devname = get_param($dlist, $item_id);

			$this->data['mark'] = 1;
			$this->data['dev_name'] = $devname;
			$this->data['dev_id'] = $item_id;
			$this->data['dev_class'] = mb_strlen($devname) >= 35 ? 'col-md-12' : 'col-md-6';
			$devices .= $ticket_status === STATUS_DRAFT ? $this->renderPartial('device-item') : $devname . PHP_EOL;
		}

		if ($ticket_status !== STATUS_DRAFT) $devices = nl2br($devices);
		if (!count($dlist)) {
			$devices = $this->renderPartial('no-device');
		} elseif (!(count($devs) || $ticket_status === STATUS_DRAFT)) {
			$devices = "Устройства не указаны";
		}
		$review = get_param($ticket, 'review');

		/* AGREE FORM */
		if ($agree) {
			if ($ares === null) {
				if ($my_role === Configuration::$ROLE_USER && $my_dep === $adepid) {
					$this->data['panel_content'] = $this->renderPartial('form-agree');
					$this->scripts[] = 'agree-ticket';
				} else {
					$this->data['panel_content'] = "<em>Ожидание решения руководителя цеха</em>";
				}
			} else {
				$this->data['ag_res'] = get_param($agree, 'result') ? 'Согласованно' : 'Отказанно';
				$this->data['ag_class'] = 'strong';
				$this->data['ag_user'] = makeSortName(get_param($agree, 'fullname', '-'));
				$this->data['ag_date'] = sqldate2human(get_param($agree, 'dt_stamp'));

				$this->data['panel_content'] = $this->renderPartial('agree-info');
			}
		} else {
			$this->data['panel_content'] = "<em>Не требуется</em>";
		}
		$this->data['panel_title'] = "Резолюция цеха $adepname";
		$this->data['resolutions'] .= $this->renderPartial('resolution-panel');
		/* AGREE FORM */

		/* REVIEW FORM */
		// форму рассмотрения нужно рисовать только в случае если согласование получено, либо оно не требуется вовсе
		if (!$agree || $ares !== null) {
			$this->data['panel_title'] = 'Резолюция главного инженера';

			if (!$review) {
				// Если результата согласования нет
				if ($my_role === Configuration::$ROLE_ME) {
					// Рисуем форму и подгружаем скрипт-обработчик
					$this->data['panel_content'] = $this->renderPartial('form-review');
					$this->scripts[] = 'review-ticket';
				} else $this->data['panel_content'] = "<em>Ожидание...</em>";
			} else {
				// Покажем результат согласования ГИ
				$this->data['ag_class'] = '';
				$this->data['ag_res'] = get_param($review, 'result');
				$this->data['ag_user'] = get_param($review, 'fullname');
				$this->data['ag_date'] = sqldate2human(get_param($review, 'dt_stamp'));

				$reason = get_param($review, 'reason', null);
				$this->data['ag_reason'] = nl2br(html_entity_decode($reason)) ?: null;

				$this->data['panel_content'] = $this->renderPartial('review-info');
			}
			$this->data['resolutions'] .= $this->renderPartial('resolution-panel');
		}
		/* REVIEW FORM */


		//$this->scripts[] = 'debug-reload';

		if ($ticket_status >= STATUS_OPEN) {
			// Панелька исполнителей
			$history = $this->model->getTicketHistory($req_id, [STATUS_OPEN, STATUS_COMPLETE, STATUS_CLOSE]);
			$items = '';
			foreach ($history as $action) {
				$items .= CHtml::createTag('li', ['class' => 'list-group-item clearfix'], [
					CHtml::createTag('div', ['class' => 'col-xs-4 strong'], get_param($action, 'action')),
					CHtml::createTag('div', ['class' => 'col-xs-4'], makeSortName(get_param($action, 'fullname'))),
					CHtml::createTag('div', ['class' => 'col-xs-4'], sqldate2human(get_param($action, 'dt_stamp'))),
				]);
			}

			$this->data['resolutions'] .= CHtml::createTag('ul', ['class' => 'list-group text-center'], $items);
		}

		$template = 'ticket-preview';

		switch ($ticket_status) {
			case STATUS_DRAFT : {
				$template = 'new-ticket';
				$this->data['nodes'] = generateOptions($nodes, $nodeid, false);

				// дорисуем все устройства выбранного узла
				// которые не были отмеченны в заявке,
				// тчобы их можно было при необходимости выбрать
				foreach ($dlist as $key => $value) {

					if (!in_array($key, $devs)) {

						$this->data['mark'] = 0;
						$this->data['dev_name'] = $value;
						$this->data['dev_id'] = $key;
						$this->data['dev_class'] = mb_strlen($value) >= 35 ? 'col-md-12' : 'col-md-6';
						$devices .= $this->renderPartial('device-item');
					}
				}
				$this->data['buttons'] .= CHtml::createButton('Отправить на согласование', [
					'class' => 'btn btn-primary btn-save',
					'data-confirm' => 1,
				]);

				$this->scripts[] = 'ticket-validate';
			}
				break;
			case STATUS_AGREE :
				$this->data['title'] = 'Согласование заявки';

				/** todo: Проверить возможность текущего статуса
				 * Возможно согласование уже получено, или вовсе не требуется.
				 * Следовательно заявка находится в статусе согласования не может
				 *  */
				if (!$adepid || $ares !== null) {
					// Если id цеха, который должен согласовывать, не указан, или согласование уже получено (!= null)
					// то статус у заявки неверный, и его желательно исправить

					$this->prepareError('Ошибочный статус заявки. Обратитесь в отдел АСУ.', 'alert-warning');
					$template = 'incorrect-status';

					// Я пока не уверен, что менять статус заяви автоматически - есть хорошо...
					// $this->model->setTicketStatus($req_id, STATUS_REVIEW, 0);
				}

				// Если текущий пользователь - Руководитель, то добавим ему кнопку сохранения,
				// чтобы он мог увековечить результат своего согласования
				if ($my_role === Configuration::$ROLE_USER && $my_dep === $adepid) {

					$this->data['buttons'] = CHtml::createButton('Сохранить', [
						'class' => 'btn btn-primary',
						'id' => 'save-btn',
					]);
					// Скрипт уже будет продгружен на этапе формирования формы
				}

				break;
			case STATUS_REVIEW :
				$this->data['title'] = 'Рассмотрение заявки';

				/** todo Проверить правильность текущего статуса
				 * А именно: если должно быть согласование, то оно должно быть получено (result !== null)
				 * Она не должна быть открытой или закрытой... Вобщем я хз как 100% быть уверенным
				 */

				if ($agree && $ares === null) {
					// Заявка должна быть согласована, но результата согласования нет
					// соответственно в текущем статусе она быть не может

					$this->prepareError('Ошибочный статус заявки. Обратитесь в отдел АСУ.', 'alert-warning');
					$template = 'incorrect-status';

					//$this->model->setTicketStatus($req_id, $agree ? STATUS_AGREE : STATUS_DRAFT, 0);
				}

				if ($my_role === Configuration::$ROLE_ME) {
					$this->data['buttons'] = CHtml::createButton('Сохранить', [
						'class' => 'btn btn-primary',
						'id' => 'save-btn',
					]);
				}

				break;
			case STATUS_ACCEPT :
				$this->data['title'] = 'Обработка заявки';

				if (!$review) {
					// Нет согласования главного инженера
					$this->prepareError('Ошибочный статус заявки. Обратитесь в отдел АСУ.', 'alert-warning');
					$template = 'incorrect-status';

					//$this->model->setTicketStatus($req_id, STATUS_ACCEPT, 0);
				}

				if ($my_role === Configuration::$ROLE_NSS) {
					$this->data['buttons'] = CHtml::createLink('Открыть заявку', "/ticket/start/$req_id/", [
						'class' => 'btn btn-primary',
					]);
				}

				break;
			case STATUS_OPEN :
				$this->data['title'] = 'Прикрытие / Продление заявки';

				if ($my_role === Configuration::$ROLE_USER && $my_dep === $tdepid) {
					$this->data['buttons'] = CHtml::createTag('div', ['class' => 'btn-group',], [
						CHtml::createLink('Продлить заявку', 'clone/', ['class' => 'btn btn-default']),
						CHtml::createButton('Прикрыть заявку', ['id' => 'btn-close', 'class' => 'btn btn-primary',]),
					]);
					$this->scripts[] = 'handle-open';
				}

				break;
			case STATUS_COMPLETE :
				$this->data['title'] = 'Закрытие заявки';

				// Добавим панельку с информацией о том, кто открыл заявку

				if ($my_role === Configuration::$ROLE_NSS) {
					$this->data['buttons'] = CHtml::createLink('Закрыть заявку', "/ticket/close/$req_id/", [
						'class' => 'btn btn-primary strong',
					]);
				}

				break;
			case STATUS_REJECT :
			case STATUS_CLOSE  :

				$template = 'ticket-preview-history';
				$this->data['title'] = 'Просмотр заявки';
				$this->data['history'] = '';
				$log = $this->model->getTicketHistory($req_id);
				foreach ($log as $action) {
					$this->data['history'] .= CHtml::createTag('li', ['class' => 'list-group-item clearfix'], [
						CHtml::createTag('div', ['class' => 'col-xs-12 strong text-center'], get_param($action, 'action')),
						CHtml::createTag('div', ['class' => 'col-xs-6'], makeSortName(get_param($action, 'fullname'))),
						CHtml::createTag('em', ['class' => 'col-xs-6 text-right text-muted'], sqldate2human(get_param($action, 'dt_stamp'))),
					]);
				}

				break;
			default:
				$template = 'incorrect-status';
		}

		$this->data['devlist'] = $devices;
		$this->render($template);

	}

	public function actionStart() {

		$ticket_id = filter_var(get_param($this->arguments, 0, -1), FILTER_VALIDATE_INT);
		$role = get_param($this->authdata, 'role_id', -1);

		$info = $this->model->getTicketInfo($ticket_id);
		$errors = '';

		if (!$info) {
			$errors = 'Запрошенная заявка не найдена.';
		} elseif ($info['status'] != STATUS_ACCEPT) {
			$errors = 'Заявка должна быть утверждена главным инженером.';
		} elseif ($role !== Configuration::$ROLE_NSS) {
			$errors = 'Заявку может открыть только Начальник смены!';
		}

		$ok = $this->model->openTicket($ticket_id);
		if ($ok) $this->model->setTicketStatus($ticket_id, STATUS_OPEN);

		$errors .= CModel::getErrorList();
		if ($errors) {
			$this->prepareError($errors);
		} else $this->prepareError("Статус заявки изменен.\n Заявка открыта.", 'alert-success');

		$this->redirect('/contents/');
	}

	public function actionDelete() {

		$ticket_id = filter_var(get_param($this->arguments, 0, -1), FILTER_VALIDATE_INT);

		$info = $this->model->getTicketInfo($ticket_id);
		if (!$info) {
			$this->prepareError('Запрошенная заявка не найдена.');
			$this->redirect('/contents/');
		} elseif ($info['status'] != STATUS_DRAFT) {
			$this->prepareError('Удалять можно только черновик.');
			$this->redirect('/contents/');
		} elseif ($info['department_id'] !== get_param($this->authdata, 'depid')) {
			$this->prepareError('Заявку другого цеха удалять запрещено!');
			$this->redirect('/contents/');
		}

		$res = $this->model->deleteDraft($ticket_id);
		if ($res) {
			$this->prepareError('Заявка удалена.', 'alert-info');
			$this->model->setTicketStatus($ticket_id, -1);
		} else {
			$this->prepareError('Заявка не найдена.');
		}

		$this->redirect('/contents/');
	}

	public function ajaxSave() {

		$info = filter_input_array(INPUT_POST, [
			'ticket_id' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 1
				],
			],
			'parent' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 1
				],
			],
			't_node' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 1
				],
			],
			'agreement' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 1
				],
			],
			'td_start' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '/^(\d{2}\.){2}\d{4}\s+(\d{2}\:?){2}$/',
					'default' => date('d.m.Y H:i'),
				],
			],
			'td_stop' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '/^(\d{2}\.){2}\d{4}\s+(\d{2}\:?){2}$/',
					'default' => date('d.m.Y H:i'),
				],
			],
			't_message' => FILTER_SANITIZE_SPECIAL_CHARS,
			'devices' => [
				'filter' => FILTER_VALIDATE_INT,
				'flags' => FILTER_REQUIRE_ARRAY,
			],
			't_number' => FILTER_SANITIZE_STRING,
			't_cdate' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '/^(\d{2}\.){2}\d{4}$/',
					'default' => date('d.m.Y'),
				],
			],
			'confirm' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => 0,
				],
			],
		]);

		$info['devices'] = $info['devices'] ?: [];

		// дату создания добиваем нулями (время), и преобразум к mysql формату
		$info['t_cdate'] = date2mysql($info['t_cdate'] . ' 00:00');

		// обнуляем номер заявки если это новая (чтобы номер сгенерировался автоматически)
		if ($info['t_number'] === '-') $info['t_number'] = null;

		$user_id = get_param($this->authdata, 'id', null);
		// информацию о создателе берем из сессии пользователя
		$info['user'] = $user_id;
		$info['depid'] = get_param($this->authdata, 'depid', null);

		$res = $this->model->saveTicket($info);
		if ($res) {
			$this->prepareError('Заявка сохранена.', 'alert-success');
			$this->model->setTicketStatus($res, STATUS_DRAFT, $user_id);

			// определим новый статус заявки, и запишем информацию в журнал
			// если отправляем на согласование, то установим соответствующий статус
			if ($info['confirm'] === 1) {
				// Если agreement задан, то заявка уходит в согласование цеха, иначе сразу к ГИ
				if (get_param($info, 'agreement')) {
					$this->model->setTicketStatus($res, STATUS_AGREE, $user_id);
					$this->prepareError('Заявка отправлена на согласование', 'alert-info');
				} else {
					$this->model->setTicketStatus($res, STATUS_REVIEW, $user_id);
					$this->prepareError('Заявка отправлена на рассмотрение', 'alert-info');
				}
			}

			// если вместе с данными пришел номер родительской заявки, то значи мы делали продление
			// и заявку-родитель нужно тоже перевести в новый статус (прикрытая)
			$parent = get_param($info, 'parent', null);
			if (get_param($info, 'parent')) $this->model->setTicketStatus($parent, STATUS_COMPLETE, $user_id);

		} else $this->prepareError('Ошибка создания заявки.' . PHP_EOL . CModel::getErrorList());
	}

	public function ajaxDevices() {

		$node = filter_input(INPUT_POST, 'node', FILTER_VALIDATE_INT);
		$devices = $this->model->getDevices($node);
		$data = '';

		if (count($devices) === 0) {
			$data .= $this->renderPartial('no-device');
		} else {
			foreach ($devices as $item) {
				$this->data['mark'] = 0;
				$this->data['dev_name'] = get_param($item, 'name');
				$this->data['dev_id'] = get_param($item, 'id');
				$this->data['dev_class'] = mb_strlen($this->data['dev_name']) >= 35 ? 'col-md-12' : 'col-md-6'; // fine view of wide name
				$data .= $this->renderPartial('device-item');
			}
		}
		echo $data;
	}

	public function ajaxAgreement() {

		// проверим чтоб статус заявки был нужный
		// и что у пользоателя есть право согласования
		$role = get_param($this->authdata, 'role_id');
		$depid = get_param($this->authdata, 'depid');
		$me = get_param($this->authdata, 'id');
		$request = filter_input_array(INPUT_POST, [
			'ticket_id' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => -1,
					'min_range' => 1
				],
			],
			'result' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 0,
					'max_range' => 1,
				],
			],
			'agree-text' => FILTER_SANITIZE_SPECIAL_CHARS,
		]);

		$tid = get_param($request, 'ticket_id');
		$info = $this->model->getTicketInfo($tid);
		$agree = get_param($info, 'agree'); // информация о цехе, кто должен согласовать
		$need = get_param($agree, 'department_id'); // его id

		if ($role !== Configuration::$ROLE_USER) {
			return $this->prepareError('Согласовывать заявки могут только руководители цехов', 'alert-warning');
		} elseif (!$info) {
			return $this->prepareError('Запрошеная заявка не найдена');
		} elseif (!$agree) {
			return $this->prepareError('Заявка не нуждается в согласовании', 'alert-warning');
		} elseif ($depid !== $need) {
			return $this->prepareError("Заявку должен согласовывать\n руководитель другого цеха", 'alert-warning');
		}

		$result = get_param($request, 'result', null);

		$ok = $this->model->setAgreement($tid, $need, $me, $result, get_param($request, 'agree-text'));
		if ($result !== null) {
			// если получили согласование или отказ, то нужно установить новый статус заявки
			if ($ok) $this->model->setTicketStatus($tid, $result === 1 ? STATUS_REVIEW : STATUS_REJECT, $me);
		}

		if (count($this->model->getErrors())) {
			echo CModel::getErrorList();
		} else {
			$this->prepareError('Заявка сохранена', 'alert-success');
		}
	}

	public function ajaxConfirmation() {

		// проверим чтоб статус заявки был нужный
		// и что у пользоателя есть право согласования
		$role = get_param($this->authdata, 'role_id');
		$me = get_param($this->authdata, 'id');
		$request = filter_input_array(INPUT_POST, [
			'ticket_id' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => -1,
					'min_range' => 1
				],
			],
			'result' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 1,
					'max_range' => 3,
				],
			],
			'agree-text' => FILTER_SANITIZE_SPECIAL_CHARS,
		]);

		$tid = get_param($request, 'ticket_id');
		$info = $this->model->getTicketInfo($tid);

		if ($role !== Configuration::$ROLE_ME) {
			return $this->prepareError('Согласовывать заявки может только главный инженер', 'alert-warning');
		} elseif (!$info) {
			return $this->prepareError('Запрошеная заявка не найдена');
		} elseif (get_param($info, 'status') != STATUS_REVIEW) {
			return $this->prepareError('Неправильный статус рассматриваемой заявки');
		}

		$result = get_param($request, 'result', null);

		if ($result !== null) {
			$ok = $this->model->setConfirmation($tid, $me, $result, get_param($request, 'agree-text'));

			// в зависимости от результата рассмотрения, установим новый статус заявки
			if ($ok) $this->model->setTicketStatus($tid, $result === 3 ? STATUS_REJECT : STATUS_ACCEPT, $me);
		}

		if (count($this->model->getErrors())) {
			echo CModel::getErrorList();
		} else {
			$this->prepareError('Заявка сохранена', 'alert-success');
		}
	}

	public function ajaxComplete() {


		$role = get_param($this->authdata, 'role_id');
		$me = get_param($this->authdata, 'id');
		$depid = get_param($this->authdata, 'depid');
		$ticket_id = filter_input(INPUT_POST, 'ticket', FILTER_VALIDATE_INT);

		$info = $this->model->getTicketInfo($ticket_id);
		if (!$info) {
			return $this->prepareError('Запрошеная заявка не найдена');
		} elseif (get_param($info, 'status') != STATUS_OPEN) {
			return $this->prepareError('Нельзя прикрыть заявку, которая не открыта', 'alert-warning');
		} elseif ($role !== Configuration::$ROLE_USER || get_param($info, 'department_id') !== $depid) {
			return $this->prepareError("Прикрыть заявку может руководитель цеха,\n который создал ее.");
		}

		// Если ошибок нет, то переводим ее в статус закрытая, установив дату закрытия
		$ok = $this->model->completeTicket($ticket_id);
		if ($ok) $this->model->setTicketStatus($ticket_id, STATUS_COMPLETE);

		if (count($this->model->getErrors())) {
			echo CModel::getErrorList();
		} else {
			$this->prepareError('Заявка прикрыта', 'alert-info');
		}
	}

	public function actionClose() {

		$role = get_param($this->authdata, 'role_id');
		$ticket_id = filter_var(get_param($this->arguments, 0, -1), FILTER_VALIDATE_INT);

		$info = $this->model->getTicketInfo($ticket_id);
		if (!$info) {
			$this->prepareError('Запрошеная заявка не найдена');
		} elseif (get_param($info, 'status') != STATUS_COMPLETE) {
			$this->prepareError('Закрыть можно только прикрытую заявку', 'alert-warning');
		} elseif ($role !== Configuration::$ROLE_NSS) {
			$this->prepareError('Не достаточно прав для выполнения операции.');
		}

		$ok = $this->model->closeTicket($ticket_id);
		if ($ok) $this->model->setTicketStatus($ticket_id, STATUS_CLOSE);

		$this->prepareError('Заявка закрыта.', 'alert-info');
		$this->redirect(['back' => 1]);
	}

}