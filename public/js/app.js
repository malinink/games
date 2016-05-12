/**
 *
 * @Ananaskelly
 */
require(['./GameControl/highlight', './GameControl/gameConfig', './Ajax/sendTurn'], function(highlight, gameConfig, sendTurn){
    $(document).ready(function(){
        $('.user-info').popover({
            title: 'User info',
            content: '<img class="img-user" width="48px" src="alien.png">maybe rating or something else',
            template: '<div class="popover" role="tooltip"><div class="arrow"></div>' +
            '<h3 class="center font popover-title"></h3><div class="font center popover-content"></div></div>',
            html: true,
            trigger: 'hover'
        });
        /**
        *
        * SIZE PROBLEM
        */
        var $cellObj = $('.cell');
        var cellSize = $cellObj.width();
        gameConfig.setFigureSize(cellSize);
        $cellObj.css({height: cellSize});
        $('.cell-corner').css({height: ($('.cell-bottom').height()*1.15) });
        $('.user1,.user2').css({height: cellSize});
        var border = $(window).height() * 0.04;
        var config = ['pawn', 'rook', 'knight', 'bishop', 'queen', 'king'];
        /*
        *
        * DO RESIZE
        */
        $(window).resize(function(){
            $cellObj.css({height: $cellObj.width()});
            $('.cell-corner').css({height: ($('.cell-bottom').height()*1.15) });
        });
        
        
        var currentFigure = '';
        
        $(document).on('click', '.figure', function(){
            currentFigure = this;
            $(this).parent().addClass('cell-key');
        });
        $(document).on('mouseenter', '.figure', function() {
            highlight.compile(this);
        });
        $cellObj.click(function(){
            
            $cellObj.removeClass('cell-highlighted')
            var attr;
            if (gameConfig.getRevert()) {
                attr = 'data-revert-id'
            } else {
                attr = 'data-id'
            }
            var newFigure = null;
            if (currentFigure!=='' && currentFigure!==$(this).children()) {
                $(this).addClass('cell-highlight-aim');
                var position = $(currentFigure).parent().attr(attr);
                var gameId = $('.game-info').attr('data-game');
                var y = $(this).attr(attr)[0];
                if ($(currentFigure).children().attr('data-type') === 'pawn' && (y === '1' || y === '8')){
                    $('input[type=checkbox').change(function(){
                        if ($(this).is(':checked')){
                            newFigure = config.indexOf($(this).attr('id'));
                            $('.custom').not(this).attr('disabled', true)
                        }
                        else {
                            $('.custom').attr('disabled', false);
                            newFigure = null;
                        }
                    })
                }
                var token = $('meta[name=csrf-token]').attr('content');
                var data = {
                    'game': gameId,
                    'figure': $(currentFigure).attr('id'),
                    'y': position[0],
                    'x': position[1],
                    'typeId': newFigure
                }
                sendTurn.send(data, token);
            }
            $(currentFigure).parent().removeClass('cell-key');
            currentFigure = '';
        });
        $('.giveUp').click(function(){
            $('.winner').text(opposite + ' are win!!!');
            $('.cover').show();
            $('.cover-content').show();
        });
        $('.close-win-window').click(function(){
           $('.cover').hide();
           $('cover-content').hide();
        });
    });
})