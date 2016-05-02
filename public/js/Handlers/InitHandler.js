/**
 *
 * @author Ananskelly
 */
define(function() {
    var config = ['pawn', 'rook', 'knight', 'bishop', 'queen', 'king'];
    var colors = ['white', 'black']
    return {
        compile: function(data) {
            if (data.game !== parseInt($('.game-info').attr('data-game')))
                return;
            $('.game-info').attr('data-turn', data.turn);
            for (var k=0; k<data.users.length; k++){
                $('#'+colors[parseInt(data.users[k].color)]+'-user-info').attr('data-id', data.users[k].id);
                $('#'+colors[parseInt(data.users[k].color)]+'-user-info').text(data.users[k].login);
            }
            for (var j=0; j<colors.length; j++){
                for (var i=0; i<data[colors[j]].length; i++){
                    var $img = $('<img>');
                    $img.attr('src','figure/'+config[data[colors[j]][i].type]+'-'+colors[j]+'.png');
                    $img.attr('data-type', config[data[colors[j]][i].type]);
                    $('<div class="figure '+colors[j]+'">').appendTo('#'+data[colors[j]][i].position).append($img);
                    $img.addClass('img-content');
                }
            }
        }
    };
});

