/**
 *
 * @author Ananskelly
 */
define(['./Handlers/TurnHandler'], function(turnHandler){
    var scope = {
        'TurnHandler' : turnHandler,
    }
    return {
        handle: function(data) {
            try {
                var message = JSON.parse(data);
                if (!message.hasOwnProperty('name') || !message.hasOwnProperty('data'))
                    throw new Error("Invalid data");
                var name = message.name.slice(0,1).toUpperCase() + message.name.slice(1) + "Handler";
                if (!require.defined("Handlers/"+name))
                    throw new Error("Invalid data name");
                console.log('Valid');
                console.log(scope['TurnHandler']);
                scope['TurnHandler'].compile(message.data);
            } catch (e){
                console.log(e.message);
            }
        }
    }
});
