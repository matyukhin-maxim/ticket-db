<div class="row">
	<br/><br/><br/><br/>
	<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
		<div class="panel panel-primary">
			<div class="panel-heading strong">Авторизация пользователя</div>
			<div class="panel-body">
				<form id="login-form" method="post" autocomplete="off"
				      action="http://bid-journal.ru/auth/login/">
					<fieldset>
						<div class="form-group">
							<label>Фамилия или табельный номер</label>
							<input class="form-control" placeholder="Фамилия / табельный номер"
							       name="login" type="text" id="user-field"
							       autofocus required autocomplete="off">
							<input id="user-id" name="tabel" type="hidden"/>
						</div>
						<div class="form-group">
							<label>Пароль пользователя</label>
							<input class="form-control" placeholder="Пароль"
							       name="password" type="password" required autocomplete="off">
						</div>
						<button id="btn-login" class="btn btn-primary btn-block" type="button" disabled>Войти</button>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
</div>
