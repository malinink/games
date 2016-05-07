/**
 *
 * @author Ananskelly
 */
define(['/GameControl/turnContorl'], function(turnControl) {
    return {
        compile: function(data) {
            turnControl.apply(data);
        }
    };
});
