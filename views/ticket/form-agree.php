<form action="#" id="agreement" method="post">
	<input type="hidden" name="ticket_id" value="<?= $t_id; ?>">
	<input type="hidden" name="result" id="result" value="<?= $agree ?>">
	<div class="row">
		<div class="col-xs-12">
			<div class="btn-group btn-group-justified" id="agree-radio">
				<div class="btn-group">
					<button class="btn btn-default choice" type="button" data-agree="1">
						Согласованно
					</button>
				</div>
				<div class="btn-group">
					<button class="btn btn-default choice" type="button" data-agree="0">
						Не согласованно
					</button>
				</div>
			</div>
		</div>
	</div>
	<textarea name="agree-text" class="form-control reason" placeholder="Причина отказа"></textarea>
</form>
