<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	<h4 class="modal-title strong">Укажите причину удаления <span
			class="text-danger text-uppercase">согласованной</span> заявки</h4>
</div>
<form action="/ticket/reject/" method="post">
	<div class="modal-body compact">
		<div class="form-group no-pad">
			<input type="hidden" name="ticket_id" value="<?= $ticket; ?>">
			<label for="message-text" class="control-label">Причина удаления</label>
			<textarea class="form-control" id="message-text" name="reason" required
			          placeholder="Описание причины удаления согласованной заявки"></textarea>
		</div>
	</div>
	<div class="modal-footer compact">
		<div class="btn-group">
			<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
			<button type="submit" class="btn btn-primary">Подтвердить</button>
		</div>
	</div>
</form>