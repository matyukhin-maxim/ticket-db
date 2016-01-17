<div class="resolution alert <?= $ag_class; ?>">
	<div class="row">
		<div class="col-md-6 strong"><abbr title="<?= $ag_date; ?>"><?= $ag_user; ?></abbr></div>
		<div class="col-md-6 text-right strong"><?= $ag_res; ?></div>
		<?php if (isset($ag_reason)) :?>
			<div class="col-md-12">
				<blockquote class="no-pad blockquote-reverse">
					<?= $ag_reason; ?>
				</blockquote>
			</div>
		<?php endif;?>
	</div>
</div>