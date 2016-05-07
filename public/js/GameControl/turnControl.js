/**
 *
 * @author Ananaskelly
 */
define(['./gameConfig', '/WSQueries/sync'], function(gameConfig, sync){
    var figureSize = gameConfig.getFigureConfig().size;
    var coeff = gameConfig.getFigureConfig().coeff;
    var opposite = gameConfig.getOpposite();
    var current = gameConfig.getCurrent();
    var revert = gameConfig.getRevert();
    var attr = '';
    if (revert) {
        attr = 'data-revert-id';
    } else {
        attr = 'data-id';
    }
        
    function setIntervalTimes(callback, delay, time, custom)
    {
        var count = 0;
        var interval = window.setInterval(function () {
            callback(custom[count%2]);
            if (++count === time) {
                window.clearInterval(interval);
            }
        }, delay);
    }
    function changeState() {
        $('.state').text(opposite.toUpperCase()+' IN GAME');
        gameConfig.changeColor(opposite);
        setIntervalTimes(function(color){$('.status').css({'background-color': color});},300,6,['yellow','#ffffff']);
    }
    function abroad(figureId){
        $('#'+figureId).parent().removeClass(opposite);
        $('#'+figureId).css({
            width : figureSize*coeff,
            height: figureSize*coeff
        }).appendTo('.hit-'+current);
    }
    function move(figureId, x, y) {
        var last = $('#'+figureId).parent().attr('id');
        $('#'+figureId).appendTo('['+attr+'='+x+y+']');
        $('['+attr+'='+last+']').removeClass('busy');
        $('['+attr+'='+last+']').removeClass(current);
        $('['+attr+'='+x+y+']').addClass(current);
        changeState();
    }
    return {
        apply: function(turnParameters) {
            var game = $('.game-info').attr('data-game');
            var turn = $('.game-info').attr('data-turn');
            if (turnParameters.game !== game)
                return;
            if (turnParameters.prev !== turn) {
                sync.sendQuery(game, turn);
                return;
            }
            move(turnParameters.move.figure, turnParameters.move.x, turnParameters.move.y );
            if (turnParameters.remove.length !== 0) {
                abroad(turnParameters.remove[0].figure);
                var newPosition = turnParameters.move.x + turnParameters.move.y;
                var oldPosition =  $('#'+turnParameters.remove[0].figure).parent().attr('id');
                if (newPosition !== oldPosition) {
                    $('['+attr+'='+newPosition+']').addClass('busy');
                    $('['+attr+'='+oldPosition+']').removeClass('busy');
                }
            }
            $('.game-info').attr('data-turn', turnParameters.turn);
            if (turnParameters.event !== 'none') {
                $('#error').text(turnParameters.event);
                $('.error-alert').show();
            }
        }
    }
})


