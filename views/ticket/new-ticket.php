<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading strong">
				<a href="/contents/" class="close" title="Закрыть без сохранения">&times;</a>
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
								<input type="text" class="form-control text-center" name="t_number" readonly
								       value="<?= $t_number; ?>">
								<input type="hidden" name="ticket_id" value="<?= $t_id; ?>">
								<input type="hidden" name="parent" value="<?= $parent; ?>">
							</td>
							<td>
								<input id="dcurrent" type="text" class="form-control text-center" name="t_cdate"
								       disabled value="<?= $t_cdate; ?>">
							</td>
							<td>
								<input type="text" class="form-control" name="t_department" disabled
								       value="<?= $t_department; ?>">
							</td>
							<td>
								<input type="text" class="form-control" name="t_user" disabled value="<?= $t_user; ?>">
							</td>
							<td>
								<select class="selectpicker show-tick form-control" data-width="100%" name="agreement">
									<?= $departments; ?>
								</select>
							</td>
						</tr>
					</table>
					<div class="row form-group">
						<div class="col-xs-6">
							<label class="control-label">Дата и время начала работ :</label>

							<div class="input-group date tpicker">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-calendar"></i>
                                </span>
								<input id="sdate" type="text" class="form-control" name="td_start" required
								       value="<?= $tstart; ?>"/>
							</div>
						</div>
						<div class="col-xs-6">
							<label class="control-label">Дата и время окончания работ :</label>

							<div class="input-group date tpicker">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-calendar"></i>
                                </span>
								<input id="edate" type="text" class="form-control" name="td_stop" required
								       value="<?= $tstop; ?>"/>
							</div>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-xs-4">
							<label class="control-label">Узел :</label>
							<select class="selectpicker show-tick form-control" data-width="100%" name="t_node"
							        title="Выбор узла"
							        id="t_node"
							        data-live-search="true" data-size="10">
								<?= $nodes; ?>
							</select>
							<br/><br/>
							<div class="alert alert-info strong">
								<div class="pull-right badge" id="dev-cnt">0</div>
								<span class="text-info">Выбрано механизмов:</span>
							</div>
							<a href="/devices/" class="btn btn-default btn-block strong">Новый узел и механизм</a>
						</div>
						<div class="col-xs-8">
							<label class="control-label">Механизмы :</label>

							<div id="devices" class="well"><?= $devlist; ?></div>
						</div>
					</div>
					<div class="form-group no-pad">
						<label for="ticket-message" class="control-label">Содержание заявки :</label>
						<textarea name="t_message" id="ticket-message" class="form-control"
						          placeholder="Текстовое сообщение, описывающее характер работ"
						          spellcheck="false"><?= $t_message; ?></textarea>
						<input type="hidden" name="confirm" value="" id="confirm">
					</div>
				</form>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-xs-12">
						<div class="pull-right">
							<div class="btn-toolbar">
								<?= $buttons; ?>
								<a href="/contents/" class="btn btn-default" title="Без сохранения">Закрыть</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
