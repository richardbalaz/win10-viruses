<!DOCTYPE html>
<html>
<head>
	<title>DocsLocker</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<meta charset="utf-8">

    <link rel="apple-touch-icon" sizes="57x57" href="/favicons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/favicons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/favicons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/favicons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/favicons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/favicons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/favicons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/favicons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/favicons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <link rel="manifest" href="/favicons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/favicons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

	<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
</head>
<body>

	<div class="container" id="app">
		<div class="row justify-content-center mt-5">
			<div class="col-xl-10 col-sm-12">
				<h1 class="display-3">DocsLocker DB</h1>
				<div class="form-group">
					<div class="input-group">
						<input v-on:keyup.enter="search_files" v-model.lazy="search_for" type="text" class="form-control shadow-none" placeholder="Názov súboru..." autofocus>
						<span class="input-group-append">
							<button v-on:click="search_files" class="btn btn-primary shadow-none" type="button">Vyhladať</button>
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="row justify-content-center">
			<div class="col-xl-10 col-sm-12">
				<table class="table table-striped">
					<thead>
						<tr>
							<th scope="col align-middle">#</th>
							<th scope="col align-middle">Názov súboru</th>
							<th scope="col align-middle">Heslo</th>
							<th scope="col align-middle">Hash</th>
							<th scope="col align-middle">Dátum vytvorenia</th>
						</tr>
					</thead>
					<tbody id="data-table">
						<tr v-for="file in files">
							<th scope="row">{{ file.id }}</th>
							<td>{{ file.name }}</td>
							<td>{{ file.password }}</td>
							<td>{{ file.hash | truncate(10, '...') }}</td>
							<td>{{ file.created_at }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

	<script>
		var app = new Vue({
			el: '#app',
			data: {
				search_for: "",
				files: [],
			},
			created() {
				this.update_files();

				setInterval(this.update_files, 3000);
			},
			methods: {
				update_files() {
					fetch("select.php?search_for=" + this.search_for + "&from_id=" + this.get_last_id())
						.then(response => response.json())
						.then(json => {
							if(json.length > 0)
								json.forEach(file => this.files.push(file))
						})						
				},
				search_files() {
					this.files = [];
					this.update_files();
				},
				get_last_id() {
					if(this.files.length == 0)
						return 0;

					return this.files[this.files.length - 1].id;
				}
			},
			filters: {
				truncate: function (text, length, suffix) {
					return text.substring(0, length) + suffix;
				},
			}
		});
	</script>
</body>
</html>