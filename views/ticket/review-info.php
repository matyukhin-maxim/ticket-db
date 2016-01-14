<div class="resolution alert text-center <?= $ag_class; ?>">
	<div class="clearfix">
		<div class="col-md-4"><?= $ag_date; ?></div>
		<div class="col-md-3"><?= $ag_user; ?></div>
		<div class="col-md-5"><?= $ag_res; ?></div>
		<?php if (isset($ag_reason)) :?>
			<div class="col-md-12 strong h4"><?= $ag_reason; ?></div>
		<?php endif;?>
	</div>
</div>