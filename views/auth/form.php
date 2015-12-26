<div class="row">
	<br/><br/><br/><br/><br/><br/>
	<div class="col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
		<div class="panel panel-primary">
			<div class="panel-heading strong">Авторизация</div>
			<div class="panel-body">
				<form method="post" autocomplete="off" action="/auth/login/">
					<fieldset>
						<div class="form-group">
							<input class="form-control" placeholder="Фамилия"
							       name="login" type="text"
							       autofocus required>
							<input name="userid" type="hidden"/>
						</div>
						<div class="form-group">
							<input class="form-control" placeholder="Пароль"
							       name="password" type="password" required>
						</div>
						<button class="btn btn-primary btn-block" type="submit">Войти</button>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
</div>
