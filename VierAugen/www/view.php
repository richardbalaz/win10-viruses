<?php

$username = $_GET["username"];
$computername = $_GET["computername"];

?>

<!DOCTYPE html>
<html>
<head>
    <title>VierAugen</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <link rel="stylesheet" href="lightbox/css/lightbox.min.css">
    <meta charset="utf-8">

    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

    <style>
        .arrow-anchor {
            left: -7em;
            top: 0.5em;
            text-decoration: none;
        }
        .arrow {
            max-width: 50px;
        }
    </style>
</head>
<body>

    <div id="app" class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-xl-9 col-sm-12">
                <a href="./" class="position-absolute text-secondary arrow-anchor"><img src="left-arrow.png" class="arrow"></a>
                <h1 class="display-4 font-weight-bold mb-3">
                    {{ username }}@{{ computername }}
                </h1>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-xl-9 col-sm-12">
                <h5>Počet snímkov: {{ header.screenshots_count }}</h5>
                <h5>Posledná aktualizácia: {{ header.last_screenshot_at }}</h5>
                <a v-bind:href="header.last_screenshot_path" data-lightbox="header">
                    <img v-bind:src="header.last_screenshot_path" id="last_screenshot" class="img-fluid" alt="">
                </a>
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            <div class="col-xl-9 col-sm-12">
                <div class="form-group">
                    <div class="input-group">
                        <!-- <input v-on:keyup.enter="search_screenshots" v-model.lazy="ocr_data" type="text" class="form-control shadow-none" id="search-input" placeholder="Kľúčové slovo..."> -->
                        <input v-on:keyup.enter="search_screenshots" type="text" class="form-control shadow-none" id="search-input" placeholder="Kľúčové slovo...">
                        <span class="input-group-append">
                            <button v-on:click="search_screenshots" class="btn btn-primary shadow-none" id="search" type="button">Vyhladať</button>
                        </span>
                    </div><!-- /input-group -->
                </div>
            </div>
        </div>
        <div id="screenshots" class="row mt-4">
            <div v-for="screenshot in screenshots" class="col-xl-3 col-sm-12">
                <a v-bind:href="screenshot.screenshot_path" data-lightbox="screenshot">
                    <img v-bind:src="screenshot.thumbnail_path" class="img-fluid"></a>
                <p class="text-center">{{ screenshot.created_at }}</p>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
			integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
			crossorigin="anonymous"></script>
		
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <script src="lightbox/js/lightbox.min.js"></script>
        
    <script>
        var updateInterval;

        var app = new Vue({
			el: '#app',
			data: {
                username: "<?php echo $username ?>",
                computername: "<?php echo $computername ?>",
                ocr_data: "",
                header: [],
                screenshots: [],
			},
			created() {                
                this.update_header();
                this.update_screenshots();

                setInterval(this.update_header, 3000);
                setInterval(this.update_screenshots, 3000);
                
			},
			methods: {
                update_header() {
					fetch("api.php?select=header&username=" + this.username + "&computername=" + this.computername)
						.then(response => response.json())
						.then(json => this.header = json)
                },
                update_screenshots() {
                    fetch("api.php?select=screenshots&username=" + this.username + "&computername=" + this.computername + "&ocr_data=" + this.ocr_data + "&from_id=" + this.get_last_id())
						.then(response => response.json())
						.then(json => {
                            if(json.length > 0)
								json.forEach(screenshot => this.screenshots.unshift(screenshot))
                        })
                },
                search_screenshots() {
                    this.ocr_data = $("#search-input").val();
                    this.screenshots = [];
                    this.update_screenshots();
                },
                get_last_id() {
                    if(this.screenshots.length == 0)
                        return 0;

                    return this.screenshots[0].id;
                }
			}
		});
    </script>

</body>
</html>