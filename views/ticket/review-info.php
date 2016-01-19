<div class="resolution text-center alert <?= $ag_class; ?>">
	<div class="row">
		<div class="col-sm-6 strong">
			<span data-placement="bottom" data-toggle="popover" data-original-title="Время согласования"
			      data-content="<?= $ag_date; ?>" data-trigger="hover">
				<?= $ag_user; ?>
			</span>
		</div>
		<div class="col-sm-6 strong"><?= $ag_res; ?></div>
		<?php if (isset($ag_reason)) : ?>
			<div class="col-md-12">
				<blockquote class="no-pad blockquote-reverse">
					<?= $ag_reason; ?>
				</blockquote>
			</div>
		<?php endif; ?>
	</div>
</div>