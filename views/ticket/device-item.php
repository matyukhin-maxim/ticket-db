<div class="device-box <?= $dev_class; ?>">
	<label>
		<button type="button" class="btn btn-default btn-ls btn-check" data-checked="<?= $mark?>">
			<i class="glyphicon">&nbsp;</i>
		</button>
		<input type="checkbox" class="dev-check" name="devices[]" value="<?= $dev_id; ?>"/>
		<?= $dev_name; ?>
	</label>
</div>
