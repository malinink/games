/**
 *
 * @author Ananskelly
 */
require(['./MessageHandler'/*, './Ajax/sendTurn'*/], function(messageHandler, sendTurn) {

    var conn = new ReconnectingWebSocket('ws://games:8080', null,
            {reconnectInterval: 5000, reconnectDecay: 1, maxReconnectAttempts: 10});
    conn.onopen = function (e) {
        console.log('connection established');
    };

    conn.onmessage = function(e) {
        messageHandler.handle(e.data);
    };

    function send(data) {
        conn.send(data);
        console.log('data send: ' + data);
    }
});
