<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading strong">
				<a href="/contents/" class="close" title="Закрыть">&times;</a>
				<?= $title; ?>
				<input type="hidden" value="<?= $t_id; ?>" id="tid">
			</div>
			<div class="panel-body">
				<table class="table table-bordered text-center">
					<tr class="strong">
						<td class="col-xs-2">№ заявки</td>
						<td class="col-xs-3">Дата подачи</td>
						<td class="col-xs-2">Цех</td>
						<td class="col-xs-5">Заявку подал</td>
					</tr>
					<tr>
						<td>
							<input type="text" class="form-control text-center" disabled
							       value="<?= $t_number; ?>">
						</td>
						<td>
							<input id="dcurrent" type="text" class="form-control text-center"
							       disabled
							       value="<?= $t_cdate; ?>">
						</td>
						<td>
							<input type="text" class="form-control" disabled
							       value="<?= $t_department; ?>">
						</td>
						<td>
							<input type="text" class="form-control" disabled value="<?= $t_user; ?>">
						</td>
					</tr>
				</table>
				<div class="row form-group no-pad">
					<div class="col-md-6">
						<div class="row">
							<div class="col-sm-6">
								<label class="control-label">Дата начала работ :</label>
								<div class="input-group">
	                                <span class="input-group-addon">
	                                    <i class="glyphicon glyphicon-calendar"></i>
	                                </span>
									<input id="sdate" type="text" class="form-control" readonly
									       value="<?= $tstart; ?>"/>
								</div>
							</div>
							<div class="col-sm-6">
								<label class="control-label">Дата окончания работ :</label>
								<div class="input-group">
                                    <span class="input-group-addon">
	                                    <i class="glyphicon glyphicon-calendar"></i>
                                    </span>
									<input id="edate" type="text" class="form-control" readonly
									       value="<?= $tstop; ?>"/>
								</div>
							</div>
							<div class="form-group">
								<div class="col-xs-12">
									<label class="control-label">Узел :</label>
									<input type="text" class="form-control" readonly value="<?= $nodename; ?>">
								</div>
								<div class="col-xs-12">
									<label class="control-label">Механизмы :</label>
									<div id="devices" class="well"><?= $devlist; ?></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<?= $resolutions; ?>
					</div>
				</div>
				<div class="form-group no-pad">
					<label for="ticket-message" class="control-label">Содержание заявки :</label>
						<textarea name="t_message" id="ticket-message" class="form-control" readonly
						          placeholder="Текстовое сообщение, описывающее характер работ"
						          spellcheck="false"><?= $t_message; ?></textarea>
				</div>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-xs-12">
						<div class="pull-left">
							<?= $navbtn; ?>
						</div>
						<div class="pull-right">
							<?= $buttons; ?>
							<a href="/contents/" class="btn btn-default">Закрыть</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
