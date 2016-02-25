<div class="well">
	<h3 class="strong">Редактор оборудования</h3>
	<br>
	<div class="row">
		<div class="col-md-5">
			<label for="node" class="control-label italic">УЗЛЫ / ОБОРУДОВАНИЕ</label>
			<div class="input-group">
				<input id="node" type="text" class="form-control" placeholder="Название нового узла"
				       autocomplete="off">
				<div class="input-group-btn">
					<button class="btn btn-default strong" id="newNode" title="Добавить">Добавить</button>
				</div>
			</div>
			<br>
			<select class="selectpicker show-tick form-control" data-width="100%" name="node_id"
			        title="Выберете редактируемый узел"
			        id="s-node"
			        data-live-search="true" data-size="15">
				<?= $nodes; ?>
			</select>
			<br>
			<div class="alert alert-info strong clearfix">
				<span class="badge pull-right strong font-big" id="dev-cnt">0</span>
				Количество механизмов в узле:
			</div>

			<button class="btn btn-danger btn-block italic" id="del-node" disabled>Удалить выбранный узел</button>
		</div>
		<div class="col-md-7">
			<div class="panel panel-default">
				<div class="panel-heading strong">Механизмы узла</div>
				<div class="panel-body strong compact" id="dev-list">
					<div class="alert alert-warning strong text-center">Узел не выбран</div>
				</div>
				<div class="panel-footer">
					<div class="input-group">
						<input id="device" type="text" class="form-control" placeholder="Название нового механизма"
						       title="Название нового механизма"
						       autocomplete="off">
						<div class="input-group-btn">
							<button class="btn btn-default strong" id="newGear" title="Добавить">Добавить</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
