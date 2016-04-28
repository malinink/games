/**
 *
 * @author malinink
 */
var conn = new ReconnectingWebSocket('ws://games:8080');
conn.onopen = function(e) {
    console.log('connection established');
}
conn.onmessage = function(e) {
    console.log('data received: ' + e.data);
}
function send(data) {
    conn.send(data);
    console.log('data send: ' + data);
}

