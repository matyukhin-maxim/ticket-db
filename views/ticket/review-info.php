<div class="resolution alert <?= $ag_class; ?>">
	<div class="row">
		<div class="col-md-6 strong"><?= $ag_res; ?></div>
		<div class="col-md-6"><abbr title="<?= $ag_date; ?>"><?= $ag_user; ?></abbr></div>
		<?php if (isset($ag_reason)) :?>
			<div class="col-md-12">
				<blockquote class="no-pad">
					<?= $ag_reason; ?>
				</blockquote>
			</div>
		<?php endif;?>
	</div>
</div>