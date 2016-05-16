/**
 *
 * @author Ananaskelly
 */
define(['./gameConfig', 'WSQueries/sync'], function(gameConfig, sync){
    var figureSize = gameConfig.getConfig('figureSize');
    var coeff = gameConfig.getConfig('coeff');
    var colors = ['white', 'black'];
    var opposite;
    var current;
    var revert;
    var attr = '';
    
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
        gameConfig.setConfig('current', colors.indexOf(opposite));
        gameConfig.setConfig('opposite', colors.indexOf(opposite));
        gameConfig.setConfig('activeState', true);
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
        var last = $('#'+figureId).parent().attr(attr);
        $('#'+figureId).appendTo('['+attr+'='+y+x+']');
        $('['+attr+'='+last+']').removeClass('busy');
        $('['+attr+'='+last+']').removeClass(current);
        $('['+attr+'='+y+x+']').addClass(current);
        $('['+attr+'='+y+x+']').addClass('busy');
        changeState();
    }
    return {
        apply: function(turnParameters) {
            opposite = colors[gameConfig.getConfig('opposite')];
            current = colors[gameConfig.getConfig('current')];
            revert = gameConfig.getConfig('revert');
            attr = '';
            if (revert) {
                attr = 'data-revert-id';
            } else {
                attr = 'data-id';
            }
            var game = parseInt($('.game-info').attr('data-game'));
            var turn = parseInt($('.game-info').attr('data-turn'));
            if (turnParameters.game !== game)
                return;
            /**if (turnParameters.prev !== turn) {
                sync.send(game, turn);
                return;
            }*/
            move(turnParameters.move.figure, turnParameters.move.x, turnParameters.move.y );
            var figureType = $('#'+turnParameters.move.figure).children().attr('data-type');
            if (figureType === 'pawn' && ((turnParameters.move.y === 5 && current === 'white')||
                    (turnParameters.move.y === 4 && current === 'black'))) {
                gameConfig.setConfig('pawnSpecial', turnParameters.move.y.toString()+turnParameters.move.x);
            }
            else {
                gameConfig.setConfig('pawnSpecial', null)
            }
            if (turnParameters.hasOwnProperty('remove') && turnParameters.remove.length !== 0) {
                abroad(turnParameters.remove[0].figure);
                var newPosition = turnParameters.move.x + turnParameters.move.y;
                var oldPosition =  $('#'+turnParameters.remove[0].figure).parent().attr('id');
                if (newPosition !== oldPosition) {
                    $('['+attr+'='+oldPosition+']').removeClass('busy');
                }
            }
            if (turnParameters.hasOwnProperty('change') && turnParameters.change.lenght !== 0 ) {
                var $img = $('<img>');
                $img.attr('src','/figure/'+turnParameters.change[0].type+'-'+current+'.png');
                $img.attr('data-type', turnParameters.change[0].type);
                $('<div class="figure '+current+'" id="' + turnParameters.change[0].figure +'">').appendTo('['+attr+'='+turnParameters.move.y+turnParameters.move.x+']').append($img);
                $img.addClass('img-content');
                $('['+attr+'='+turnParameters.move.y+turnParameters.move.x+']').addClass('busy');
            }
            $('.cell').removeClass('cell-highlight-aim');
            $('.cell').removeClass('cell-key');
            $('.cell').removeClass('cell-highlight-enemy');
            $('.game-info').attr('data-turn', turnParameters.turn);
            if (turnParameters.event !== 'none') {
                $('#error').text(turnParameters.event);
                $('.error-alert').show();
            }
        }
    }
})


