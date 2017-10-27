<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Пациент</title>
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
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
<h3>Пациент</h3>
<div id="chat-frame">

</div>
<div class="btn-group">
    <div class="call-btn btn-item">
        <img src="call-btn.png" id="call-btn">
    </div>
    <div class="end-call-btn btn-item">
        <img src="end-call-btn.png" id="end-call-btn">
    </div>
</div>
<div id="logger"></div>
<audio id="myaudio" loop>
    <source src='call-ring.mp3'>
</audio>
<script>
    $(function(){
        var socket = new WebSocket('ws://localhost:8080');
        var idPatient = 1306;

        var flag = false;

        // Флаг для решения проблеммы обрыва звонка при закрытие и обновлении страницы на стороне пациената
        // Во время дозвона при таком событии не происходит разрыва связи. А если пациент обновит страницу при
        // включенном видео чате, то чат закроется и на стороне доктора
        var onCloseFlag = false;

        // При закрытии или обновлении вкладки
        window.onbeforeunload = function() {
            if(onCloseFlag) {
                let message = {
                    command: 'end call',
                    id: idPatient
                }
                socket.send(JSON.stringify(message));
            }
        };

        socket.onmessage = function(event){
            let mess = JSON.parse(event.data);
            if(mess.event == 'end' && mess.id == idPatient) {
                $("#chat-frame").empty();
                // Отключаем звук гудков
                document.getElementById("myaudio").pause();
                // Убираем кнопку отмены звонка
                $("#end-call-btn").css("display", "none");
                // Возвращаем кнопку звонка Отключаем анимацию кнопки
                $("#call-btn").attr("src", "call-btn.png").css("display", "inline").css("pointer-events", "none");
                flag = false;
                onCloseFlag = false;
            } else if (mess.event == 'call accept' && mess.id == idPatient){
                // Сработает когда доктор отправит вызов
                if(flag == false){
                    if(flag == false){
                        sendNotification('Дзвінок від лікаря', {
                            body: 'Вам намагаються дозвонитися',
                            icon: 'icon_notif.png',
                            dir: 'auto'
                        });
                    }
                    flag = true;
                    // Включаем анимацию кнопки и ее кликабельность на стороне пациента
                    $("#call-btn").attr("src","call-btn.gif").css("pointer-events", "auto");

                    // Включаем звук вызова
                    document.getElementById("myaudio").play();

                    // Добавляем кнопку отмены вызова
                    $("#end-call-btn").css("display", "inline");
                }
            }
        }

        // Сработает когда пациент нажмет кнопку принять
        $('#call-btn').css("pointer-events", "none").on("click", function(){
            onCloseFlag = true;
            // Отключаем звук звонка
            document.getElementById("myaudio").pause();

            // Запускаем видео чат на стороне доктора
            let message = {
                command: 'send answer',
                id: idPatient
            }
            socket.send(JSON.stringify(message));

            // Включаем видео чат
            $("#chat-frame").append("<iframe src='http://localhost:1337/" + 434 + "'; width='100%'; height='500'>");

            // Убираем кнопку звонка
            $("#call-btn").css("display", "none");
        });

        $('#end-call-btn').on("click", function(){
            // Запускаем видео чат на стороне доктора
            let message = {
                command: 'end call',
                id: idPatient
            }
            socket.send(JSON.stringify(message));
        });

        function sendNotification(title, options) {
            // Проверим, поддерживает ли браузер HTML5 Notifications
            if (!("Notification" in window)) {
                alert('Ваш браузер не поддерживает HTML Notifications, его необходимо обновить.');
            }

            // Проверим, есть ли права на отправку уведомлений
            else if (Notification.permission === "granted") {
                // Если права есть, отправим уведомление
                var notification = new Notification(title, options);

                function clickFunc() {
                    // Переходим на страницу звонка
                }

                notification.onclick = clickFunc;
            }

            // Если прав нет, пытаемся их получить
            else if (Notification.permission !== 'denied') {
                Notification.requestPermission(function (permission) {
                    // Если права успешно получены, отправляем уведомление
                    if (permission === "granted") {
                        var notification = new Notification(title, options);

                    } else {
                        alert('Вы запретили показывать уведомления'); // Юзер отклонил наш запрос на показ уведомлений
                    }
                });
            } else {
            // Пользователь ранее отклонил наш запрос на показ уведомлений
            // В этом месте мы можем, но не будем его беспокоить. Уважайте решения своих пользователей.
            }
        }
    });
</script>


</body>
</html>