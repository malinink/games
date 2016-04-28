/**
 *
 * @author Ananskelly
 */
require(['./MessageHandler', './Ajax/sendTurn'], function(messageHandler, sendTurn) {
    console.log('hello');
    var conn = new WebSocket('ws://games:8080');
    conn.onopen = function(e) {
        console.log('connection established');
        send(JSON.stringify({'data' : 'hello', 'name' : 'turn'}));
    };
    
    conn.onmessage = function(e) {
        messageHandler.handle(e.data);
    };
    console.log(conn);
    function send(data) {
        conn.send(data);
        console.log('data send: ' + data);
    }
    $('.mySuperCoolButton').click(function(){
        var token = $("meta[name='csrf-token']").attr('content');
        console.log(token);
        sendTurn.send({'greeting': 'hello from js!!!!'}, token);
    });
});
