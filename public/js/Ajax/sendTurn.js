/**
 *
 * @author Ananaskelly 
 */
define(function(){
    return {
        send : function(data, csrf_token) {
            var request = new XMLHttpRequest();
            request.open('POST', '/ajax/send/turn', true);
            request.setRequestHeader('Content-Type', 'application/json');
            request.setRequestHeader('X-CSRF-Token', csrf_token);
            request.send(JSON.stringify(data));
            request.onreadystatechange = function(){
                alert(request.responseText);
            }
        }
    }
})

