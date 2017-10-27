<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Доктор</title>
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
    <script src="/socket.io.js"></script>

    <style>
        #end-call-btn {
            display: none;
        }
        .call-btn img {
            height: 50px;
            cursor: pointer;
        }
        .end-call-btn img {
            height: 50px;
            cursor: pointer;
        }
        #logger {
            color: red;
        }
        .btn-item {
            display:inline-block;
        }

        .btn-item img {
            display:block;
        }
        .btn-group, h3 {
            text-align: center;
        }
    </style>
</head>
<body>
<h3>Доктор</h3>
<div id="chat-frame">
    <!--<iframe src="http://localhost:1337/z2c51z"; width="100%"; height="700"></iframe>-->
</div>
<div class="btn-group">
    <div class="call-btn btn-item">
        <img src="call-btn.png" id="call-btn">
    </div>
    <div class="end-call-btn btn-item">
        <img src="end-call-btn.png" id="end-call-btn">
    </div>
</div>
<div id="stopwatch"></div>
<div id="logger"></div>
<audio id="myaudio" loop>
    <source src='gudok.mp3'>
</audio>


<script>
    $(function(){
        var socket = new WebSocket('ws://localhost:8080');
        var id = 1306;

        window.onbeforeunload = function() {
            let message = {
                command: 'end call',
                id: id
            }
            socket.send(JSON.stringify(message));
        };
        // По нажатию на кнопку вызова
        $('#call-btn').on("click", function(){
            // Включаем анимацию кнопки и отключаем ее кликабельность на стороне доктора
            $("#call-btn").attr("src","call-btn.gif").css("pointer-events", "none");

            // Добавляем кнопку отмены вызова
            $("#end-call-btn").css("display", "inline");

            // Включаем звук гудков
            document.getElementById("myaudio").play();

            // Включаем анимацию звонка и звук вызова на стороне пациента
            // ----------socket.emit('send call', id);

            let message = {
                command: 'send call',
                id: id
            }

            socket.send(JSON.stringify(message));

            var stopwatch = 1;
            var timerId = setInterval(function() {
                let message = {
                    command: 'send call',
                    id: id
                }
                socket.send(JSON.stringify(message));
                // ------------ socket.emit('send call', id);
                $("#stopwatch").append(stopwatch + ' ');
                stopwatch++;
            }, 1000);

            // Задаем время дозвона после чего идет сброс
            var timeOut = setTimeout(function() {
                clearInterval(timerId);

                let message = {
                    command: 'end call',
                    id: id
                }
                socket.send(JSON.stringify(message));
            }, 15000);

            socket.onmessage = function(event){
                let mess = JSON.parse(event.data);
                if(mess.event == 'end' && mess.id == id){
                    $("#chat-frame").empty();
                    // Отключаем звук гудков
                    document.getElementById("myaudio").pause();
                    // Отключаем анимацию кнопки делаем ее кликабельной и включаем в случае необходимости
                    $("#call-btn").attr("src","call-btn.png").css("display", "inline").css("pointer-events", "auto");
                    // Убираем кнопку отмены звонка
                    $("#end-call-btn").css("display", "none");
                    // Останавливаем запрос на вызов
                    clearInterval(timerId);
                    clearTimeout(timeOut);

                }else if (mess.event == 'answer accept' && mess.id == id){
                    clearTimeout(timeOut);
                    // Отключаем звук гудков
                    document.getElementById("myaudio").pause();

                    // Включаем видео чат
                    $("#chat-frame").empty().append("<iframe src='http://localhost:1337/" + id + "'; width='100%'; height='500'>");

                    // Убираем кнопку звонка
                    $("#call-btn").css("display", "none");

                    clearInterval(timerId);
                }
            };

        });

        $('#end-call-btn').on("click", function(){

            let message = {
                command: 'end call',
                id: id
            }
            socket.send(JSON.stringify(message));

            // ----------- socket.emit('end call', id);
        });



    });
</script>
</body>
</html>