/**
 *
 * @author Ananskelly
 */

define(['GameControl/turnControl'], function(turnControl) {
    return {
        compile: function(data) {
            console.log(data);
            turnControl.apply(data);
        }
    };
});
