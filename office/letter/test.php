<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <script type="text/javascript">
        function connect() {
            var ws = new WebSocket("ws://127.0.0.1:13000");
            ws.onopen = function () {
                
            };

            ws.onmessage = function (evt) {
				var received_msg = evt.data;
                alert("Message received = "+received_msg);
            };
            ws.onclose = function () {
                alert("Connection is closed...");
            };
        };


    </script>
</head>
<body style="font-size:xx-large" >
    <div>
    <a href="#" onclick="connect()">Click here to start</a></div>
</body>
</html>