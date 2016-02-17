<div class="row">
	<div class="col-md-3">
		<div class="panel panel-default strong">
			<div class="panel-heading hidden-sm hidden-xs">
				<i class="glyphicon glyphicon-menu-hamburger"></i>
				Меню
			</div>
			<div class="panel-body">
				<ul class="nav nav-tabs-justified menu">
					<?= $usermenu; ?>
				</ul>
			</div>
		</div>
	</div>
	<div class="col-md-9">
		<div class="alert alert-success text-center strong compact" style="margin-bottom: 0">
			Наведите курсор мыши на УЗЕЛ, чтобы увидеть механизмы, указанные в заявке
			<br>
			<em class="text-muted small">Если не работает, нажмите <kbd>ctrl+F5</kbd></em>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading strong">
				<i class="glyphicon glyphicon-bullhorn"></i>
				Список заявок
			</div>
			<div class="">
				<div class="panel-body no-pad">
					<table class="table table-condensed no-pad">
						<thead>
						<tr>
							<th class="col-xs-1">#</th>
							<th class="col-xs-2">Дата подачи</th>
							<th class="col-xs-1">Цех</th>
							<th class="col-xs-2">Период работ</th>
							<th class="col-xs-4">Узел</th>
							<th class="col-xs-2"></th>
						</tr>
						</thead>
					</table>
				</div>
				<div class="panel-body ticket-window">
					<table class="table table-bordered table-condensed table-hover text-center">
						<colgroup>
							<col class="col-xs-1">
							<col class="col-xs-2">
							<col class="col-xs-1">
							<col class="col-xs-2">
							<col class="col-xs-4">
							<col class="col-xs-2">
						</colgroup>
						<tbody id="ticket-list"></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
