</div>

<footer class="navbar navbar-default navbar-fixed-bottom">
	<div class="container">
		<div class="row">
			<p class="text-muted text-center">
				АО "ДГК" НГРЭС.
				<abbr title="Подробнее..." id="asu-info">Отдел информационных технологий</abbr>. 2015-2016г.
			</p>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class=" col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-3 col-lg-offset-5"
			     id="info-block">
				<div class="alert alert-info">
					<div class="row">
						<div class="col-xs-8">
							<a class="alert-link" href="mailto:kolevatova-ts@dvgk.rao-esv.ru" title="Написать письмо">
								<i class="glyphicon glyphicon-envelope"></i>
								Колеватова Т.С.
							</a>
						</div>
						<div class="col-xs-4 text-right strong">
							<i class="glyphicon glyphicon-earphone"></i>
							55-88
						</div>
					</div>
					<div class="row">
						<div class="col-xs-8">
							<a class="alert-link" href="mailto:matyukhin-mp@dvgk.rao-esv.ru" title="Написать письмо">
								<i class="glyphicon glyphicon-envelope"></i>
								Матюхин М.П.
							</a>
						</div>
						<div class="col-xs-4 text-right strong">
							<i class="glyphicon glyphicon-earphone"></i>
							51-30
						</div>
					</div>
					<div class="row">
						<div class="col-xs-8">
							<a class="alert-link" href="mailto:vinogradov-ea@dvgk.rao-esv.ru" title="Написать письмо">
								<i class="glyphicon glyphicon-envelope"></i>
								Виноградов Е.А.
							</a>
						</div>
						<div class="col-xs-4 text-right strong">
							<i class="glyphicon glyphicon-earphone"></i>
							50-98
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class="alert alert-danger col-sm-6 col-sm-offset-3" id="status-footer">
				<div class="strong" id="status-text"><?= get_param($elist); ?></div>
			</div>
		</div>
	</div>
</footer>

<?php foreach ($this->scripts as $filename) {
	if (file_exists("./public/js/$filename.js"))
		printf(PHP_EOL . '<script type="text/javascript" src="%s"></script>', "/public/js/$filename.js");
} ?>

</body>
</html>