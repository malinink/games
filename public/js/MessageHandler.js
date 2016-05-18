/**
 *
 * @author Ananskelly
 */
define(['./Handlers/TurnHandler', './Handlers/InitHandler', './Handlers/SynchronizeHandler', './Handlers/AuthenticationHandler'], function(TurnHandler, InitHandler, SynchronizeHandler, AuthenticationHandler){
    var scope = {
        'TurnHandler' : TurnHandler,
        'InitHandler' : InitHandler,
        'SynchronizeHandler': SynchronizeHandler,
        'AuthenticationHandler': AuthenticationHandler
    }    
    return {
        handle: function(data) {
            try {
                var message = JSON.parse(data);
                console.log(message);
                if (!message.hasOwnProperty('name') || !message.hasOwnProperty('data'))
                    throw new Error("Invalid data");
                var name = message.name.slice(0,1).toUpperCase() + message.name.slice(1) + "Handler";
                if (!require.defined("Handlers/"+name))
                    throw new Error("Invalid data name");
                scope[name].compile(message.data);
            } catch (e){
                console.log(e.message);
            }
        }
    }
});
