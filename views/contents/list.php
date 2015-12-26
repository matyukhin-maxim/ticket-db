<div class="row">
	<div class="col-md-3">
		<div class="panel panel-default strong">
			<div class="panel-heading hidden-sm hidden-xs">
				<i class="glyphicon glyphicon-menu-hamburger"></i>&nbsp;
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
		<div class="panel panel-default screen-wnd">
			<div class="panel-heading strong">
				<i class="glyphicon glyphicon-bullhorn"></i>&nbsp;
				Список заявок
			</div>
			<div class="screen--wnd">
				<div class="panel-body no-pad">
					<table class="table table-bordered no-pad">
						<thead>
						<tr>
							<th class="col-xs-1">#</th>
							<th class="col-xs-2">Дата подачи</th>
							<th class="col-xs-1">Цех</th>
							<th class="col-xs-2">Период работ</th>
							<th class="col-xs-4">Узел</th>
							<th class="col-xs-2">Статус</th>
						</tr>
						</thead>
					</table>
				</div>
				<div class="panel-body" id="ticket-list">
					<table class="table table-bordered">
						<colgroup>
							<col class="col-xs-1">
							<col class="col-xs-2">
							<col class="col-xs-1">
							<col class="col-xs-2">
							<col class="col-xs-4">
							<col class="col-xs-2">
						</colgroup>
						<tbody>
						<?php
						var_dump($tickets);
						?>
						<!--						<tr>-->
						<!--							<td>32131</td>-->
						<!--							<td>28.12.2015</td>-->
						<!--							<td>СДТУ</td>-->
						<!--							<td>04.12.2015 10:00 <br/> 14.12.2015 12:00</td>-->
						<!--							<td>Общестанционное оборудование ЦТАИ</td>-->
						<!--							<td>Разрешена</td>-->
						<!--						</tr>-->
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
