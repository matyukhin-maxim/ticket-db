<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading strong">
				<a href="/contents/" class="close" title="Закрыть">&times;</a>
				<?= $title; ?>
			</div>
			<div class="panel-body">
				<form action="/ticket/save/" id="ticket">
					<table class="table table-bordered text-center">
						<tr class="strong">
							<td class="col-xs-1">№ заявки</td>
							<td class="col-xs-2">Дата подачи</td>
							<td class="col-xs-2">Цех</td>
							<td class="col-xs-3">Заявку подал</td>
							<td class="col-xs-4">Согласование</td>
						</tr>
						<tr>
							<td>
								<input type="text" class="form-control text-center" name="t_number" disabled
								       value="<?= $t_number; ?>">
								<input type="hidden" name="ticket_id" value="<?= $t_id; ?>">
							</td>
							<td>
								<input id="dcurrent" type="text" class="form-control text-center" name="t_cdate"
								       disabled
								       value="<?= $t_cdate; ?>">
							</td>
							<td>
								<input type="text" class="form-control" name="t_department" disabled
								       value="<?= $t_department; ?>">
							</td>
							<td>
								<input type="text" class="form-control" name="t_user" disabled value="<?= $t_user; ?>">
							</td>
							<td>
								<select class="selectpicker form-control" name="agreement" disabled>
									<?= $departments; ?>
								</select>
							</td>
						</tr>
					</table>
					<div class="row form-group">
						<div class="col-xs-6">
							<div class="row">
								<div class="col-sm-6">
									<label class="control-label">Дата и время начала работ :</label>
									<div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-calendar"></i>
                                </span>
										<input id="sdate" type="text" class="form-control" name="td_start" disabled
										       value="<?= $tstart; ?>"/>
									</div>
								</div>
								<div class="col-sm-6">
									<label class="control-label">Дата и время окончания работ :</label>
									<div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-calendar"></i>
                                </span>
										<input id="edate" type="text" class="form-control" name="td_stop" disabled
										       value="<?= $tstop; ?>"/>
									</div>
								</div>
								<div class="form-group">
									<div class="col-xs-12">
										<label class="control-label">Узел :</label>
										<input type="text" class="form-control" disabled value="<?= $nodename;?>">
									</div>
									<div class="col-xs-12">
										<label class="control-label">Механизмы :</label>
										<div id="devices" class="well"><?= $devlist; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xs-6">
							<div class="alert alert-warning strong">
								<h4>Согласование:</h4>
								<span class="text-info">Фамилия</span>
							</div>
						</div>
					</div>
					<div class="form-group no-pad">
						<label for="ticket-message" class="control-label">Содержание заявки :</label>
						<textarea name="t_message" id="ticket-message" class="form-control" readonly
						          placeholder="Текстовое сообщение, описывающее характер работ"
						          spellcheck="false"><?= $t_message; ?></textarea>
					</div>
				</form>
				<div id="response"></div>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-xs-12">
						<div class="pull-right">
							<div class="btn-toolbar">

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
