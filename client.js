var host = "127.0.0.1";
var port = 9501;
var address = "ws://"+host+":"+port;

var ws = new WebSocket(address);

ws.onopen = function()
{
    console.log("[onopen]");
    var message = "onopen";
    ws.send(message);
}

ws.onmessage = function(evt)
{
    console.log("[onmessage]", evt);
    var data = evt.data;
    console.log(data);
}

ws.onclose = function(evt)
{
    console.log("[onclose]", evt);
}

ws.onerror = function(evt)
{
    console.log("[onerror]", evt); 
}

var message = "hello";
ws.send(message);

ws.close();