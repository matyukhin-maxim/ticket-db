<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="icon" href="data:;base64,iVBORw0KGgo=">
	<title><?= $this->title; ?></title>
	<link rel="stylesheet" href="/public/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="/public/css/bootstrap-select.min.css"/>
	<link rel="stylesheet" href="/public/css/bootstrap-datetimepicker.min.css"/>
	<link rel="stylesheet" href="/public/css/main.css"/>
	<!--[if lt IE 9]>
	<script src="./public/js/html5shiv.min.js"></script>
	<script src="./public/js/respond.min.js"></script>
	<![endif]-->
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar"
		        aria-expanded="false" aria-controls="navbar">
			<span class="sr-only">Переключить</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="/">
			<?= $brand; ?>
		</a>
		<?php if ($authdata !== false): ?>
			<ul class="nav navbar-left">
				<li class="role-name alert-info strong text-center" title="Роль">
					<?= get_param($authdata, 'rolename', '?'); ?>
				</li>
			</ul>
		<?php endif; ?>
	</div>
	<div id="navbar" class="collapse navbar-collapse">
		<ul class="nav navbar-top-links navbar-right">
			<?php if ($authdata === false): ?>
				<li>
					<a href="/auth/"><i class="glyphicon glyphicon-log-in"></i>
						Вход</a>
				</li>
			<?php else: ?>
				<li class="navbar-text"><span>Вы вошли как:</span></li>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="glyphicon glyphicon-user"></i>
						<?= get_param($authdata, 'login', 'n/a'); ?>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu dropdown-user">
						<li class="dropdown-header text-right strong">
							<?= get_param($authdata, 'fullname', '-'); ?>
						</li>
						<li class="divider"></li>
						<li><a href="/auth/changepassword/">
								<i class="glyphicon glyphicon-lock"></i>
								Изменить пароль
							</a>
						</li>
						<li>
							<a href="/auth/logout/">
								<i class="glyphicon glyphicon-log-out"></i>
								Выход
							</a>
						</li>
					</ul>
				</li>
			<?php endif; ?>
			<li>
				<a href="/about/"><i class="glyphicon glyphicon-question-sign"></i>
					Помощь</a>
			</li>
		</ul>
	</div>
</nav>
<div class="container">
	<div class="row">
		<div class="col-xs-4 col-xs-offset-8">
			<div class="alert alert-dismissable fade in text-center strong" id="status-box">
				<button class="close" aria-hidden="true" data-dismiss="alert" type="button">&times;</button>
				<div id="status-text"></div>
			</div>
		</div>
	</div>
</div>

<!-- Begin page content -->
<div class="container wrapper">

