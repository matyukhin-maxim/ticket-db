<form action="#" id="review" method="post">
	<input type="hidden" name="ticket_id" value="<?= $t_id; ?>">
	<input type="hidden" name="result" id="result">
	<div class="row">
		<div class="col-xs-12">
			<div class="btn-group btn-group-justified btn-group-sm" id="agree-radio">
				<div class="btn-group btn-group-sm">
					<button class="btn btn-default choice" type="button" data-agree="1">
						Разрешить
					</button>
				</div>
				<div class="btn-group btn-group-sm">
					<button class="btn btn-default choice" type="button" data-agree="2" title="Разрешить при условии">
						Разрешить при условии
					</button>
				</div>
				<div class="btn-group btn-group-sm">
					<button class="btn btn-default choice" type="button" data-agree="3">
						Отказать
					</button>
				</div>
			</div>
		</div>
	</div>
	<textarea name="agree-text" class="form-control reason"
	          placeholder="Условие разрешения или причина отказа"
	          title="Условие разрешения или причина отказа"></textarea>
</form>
