/**
 *
 * @Ananaskelly
 */

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
    $cellObj.css({height: cellSize});
    $('.cell-corner').css({height: ($('.cell-bottom').height()*1.15) });
    $('.user1,.user2').css({height: cellSize});
    var border = $(window).height() * 0.04;
    /*
    *
    * DO RESIZE
    */
    $(window).resize(function(){
        $cellObj.css({height: $cellObj.width()});
        $('.cell-corner').css({height: ($('.cell-bottom').height()*1.15) });
    });
    /*
    *
    * CURRENT SITUATION
    */
    var currentPlayer = 'black';
    var opposite = 'white';
    /*
    *
    * IMPORTANT THINGS
    */
    var switchFlag = false;
    var figure_height = cellSize;
    var figure_width = cellSize;
    var minFigure = 0.4;
    var pawn_special = '';
    var pawn_special_high = '';
    var RockyHorror = {'black' : false, 'white': false};
    var newFigure = {'black': [], 'white': []}; // maybe it will help someday

    /*
     HIGHLIGHT
     */
    function highlight(currentObj)
    {
        if (currentFigure !== '' && !switchFlag) {
            return;
        }
        availableCell = [];
        switchFlag = false;
        $cellObj.removeClass('cell-highlight-enemy');
        $cellObj.removeClass('cell-highlighted');
        if (!$(currentObj).hasClass(currentPlayer))
            return;
        var type = $(currentObj).children().attr('data-type');
        var figure = $(currentObj).attr('id');
        var cell = '';
        if (currentPlayer === 'black') {
            cell = initialBlack[figure];
            direction = 1;
        }
        else {
            direction = -1;
            cell = initialWhite[figure];
        }
        var cell_y = cell[0];
        var cell_x = cell[1];

        switch (type) {
            case 'pawn' : {
                var goOnFlag = simpleAdd(Number(cell_y)+direction,Number(cell_x),'pawn');
                if (Number(cell_y)===2 && currentPlayer === 'black' && goOnFlag)
                    simpleAdd(Number(cell_y)+2,Number(cell_x),'pawn');
                else if (Number(cell_y)===7 && currentPlayer === 'white' && goOnFlag)
                    simpleAdd(Number(cell_y)-2,Number(cell_x),'pawn');

                checkEnemy(Number(cell_y)+direction,Number(cell_x)+direction);
                checkEnemy(Number(cell_y)+direction,Number(cell_x)-direction);
                checkPawnSpecial(cell_y,cell_x);
                break;
            }
            case 'bishop' : {
                diag(Number(cell_y),Number(cell_x),-1,1);
                diag(Number(cell_y),Number(cell_x),1,1);
                diag(Number(cell_y),Number(cell_x),1,-1);
                diag(Number(cell_y),Number(cell_x),-1,-1);
                break;
            }
            case 'rook' : {
                parall(Number(cell_y),Number(cell_x),-1,true);
                parall(Number(cell_y),Number(cell_x),1,true);
                parall(Number(cell_y),Number(cell_x),1,false);
                parall(Number(cell_y),Number(cell_x),-1,false);
                break;
            }
            case 'knight': {
                simpleAdd(Number(cell_y)-2,Number(cell_x)+1);
                simpleAdd(Number(cell_y)-2,Number(cell_x)-1);
                simpleAdd(Number(cell_y)-1,Number(cell_x)-2);
                simpleAdd(Number(cell_y)+1,Number(cell_x)-2);
                simpleAdd(Number(cell_y)+2,Number(cell_x)-1);
                simpleAdd(Number(cell_y)+2,Number(cell_x)+1);
                simpleAdd(Number(cell_y)+1,Number(cell_x)+2);
                simpleAdd(Number(cell_y)-1,Number(cell_x)+2);
                break;
            }
            case 'king': {
                simpleAdd(Number(cell_y),Number(cell_x)+1);
                simpleAdd(Number(cell_y),Number(cell_x)-1);
                simpleAdd(Number(cell_y)+1,Number(cell_x)+1);
                simpleAdd(Number(cell_y)-1,Number(cell_x)-1);
                simpleAdd(Number(cell_y)+1,Number(cell_x)-1);
                simpleAdd(Number(cell_y)-1,Number(cell_x)+1);
                simpleAdd(Number(cell_y)-1,Number(cell_x));
                simpleAdd(Number(cell_y)+1,Number(cell_x));

                break;
            }
            case 'queen': {
                parall(Number(cell_y),Number(cell_x),-1,true);
                parall(Number(cell_y),Number(cell_x),1,true);
                parall(Number(cell_y),Number(cell_x),1,false);
                parall(Number(cell_y),Number(cell_x),-1,false);
                diag(Number(cell_y),Number(cell_x),-1,1);
                diag(Number(cell_y),Number(cell_x),1,1);
                diag(Number(cell_y),Number(cell_x),1,-1);
                diag(Number(cell_y),Number(cell_x),-1,-1);
                break;
            }
        }
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
        if (currentPlayer === 'black') {
            $('.state').text('WHITE IN GAME');
            currentPlayer = 'white';
            opposite = 'black';
        }
        else {
            $('.state').text('BLACK IN GAME');
            currentPlayer = 'black';
            opposite = 'white';
        }
        setIntervalTimes(function(color){$('.status').css({'background-color': color});},300,6,['yellow','#ffffff']);
    }
    /*
    *
    * RENDER FIGURE
    */
    var initialBlack = {
        'b_pawn1': '21', 'b_pawn2': '22', 'b_pawn3': '23', 'b_pawn4': '24', 'b_pawn5': '25', 'b_pawn6': '26', 'b_pawn7': '27', 'b_pawn8': '28',
        'b_rook1': '11', 'b_knight1': '12', 'b_bishop1': '13', 'b_king': '14', 'b_queen': '15', 'b_bishop2': '16', 'b_knight2': '17', 'b_rook2': '18'
    };
    var set = ['rook1','knight1','bishop1','king','queen','bishop2','knight2','rook2'];
    var initialWhite = {
        'w_pawn1': '71', 'w_pawn2': '72', 'w_pawn3': '73', 'w_pawn4': '74', 'w_pawn5': '75', 'w_pawn6': '76', 'w_pawn7': '77', 'w_pawn8': '78',
        'w_rook1': '81', 'w_knight1': '82', 'w_bishop1': '83', 'w_king': '84', 'w_queen': '85', 'w_bishop2': '86', 'w_knight2': '87', 'w_rook2': '88'
    };
    for (var i=1; i<9;i++) {
        var white_figure = 'w_'+set[i-1];
        var black_figure = 'b_'+set[i-1];
        $('#'+initialBlack[black_figure]).addClass('busy black');
        $('#'+initialWhite[white_figure]).addClass('busy white');
        white_figure = 'w_pawn'+i;
        black_figure = 'b_pawn'+i;
        $('#'+initialBlack[black_figure]).addClass('busy black');
        $('#'+initialWhite[white_figure]).addClass('busy white');
    }
    /*
    *
    * IMPORTANT VARIABLES
    */
    var currentFigure = '';
    var availableCell = [];
    var fightCell = [];
    function checkEnemy(y,x){
        console.log(String(y)+x)
        if ($('#'+y+x).hasClass(opposite)) {
            $('#'+y+x).addClass('cell-highlight-enemy');
            fightCell.push(String(y)+x);
        }
    }
    /*
    *
    * Find by key in obj
    */
    function getKey(obj, value){
        for(var key in obj){
            if(obj[key] == value){
                return key;
            }
        }
        return null;
    }
    /*
    *
    * HELPER FUNCTION FOR HIGHLIGHT
    */
    function checkPawnSpecial(y,x) {
        if (pawn_special !== '')
        {
            var z1=0,z2=0;
            if (pawn_special[0] === y && Math.abs(Number(pawn_special[1])-Number(x))===1) {
                if (currentPlayer === "black")
                    z1 = Number(y) + 1;
                else
                    z1 = Number(y) - 1;
                if (pawn_special[1] - x > 0)
                    z2 = Number(x) + 1;
                else
                    z2 = Number(x) - 1;
                pawn_special_high = String(z1)+z2;
                $('#' + z1 + z2).addClass('cell-highlight-enemy');
            }
        }
    }
    function getObj(type) {
        if (type === "black")
            return initialBlack;
        else
            return initialWhite;
    }
    function simpleAdd(y,x,type){
        if (x>8 || x<1 || y>8 || y<1)
            return false;
        if (!$('#'+y+x).hasClass('busy')){
            console.log(String(y)+x);
            $('#'+y+x).addClass('cell-highlighted');
            availableCell.push(String(y)+x);
            return true;
        }
        else if (type !== 'pawn'){
            checkEnemy(y,x);
        }

    }
    function diag(y,x,direction_y,direction_x) {
        y += direction_y;
        x += direction_x;
        console.log(y,x);
        while (!$('#'+y+x).hasClass('busy') && y < 9 && y > 0 && x > 0 && x < 9){
            $('#'+y+x).addClass('cell-highlighted');
            availableCell.push(String(y)+x);
            y += direction_y;
            x += direction_x;
        }
        if (checkEnemy(y,x))
                fightCell.push(String(y)+x);
            console.log('opposite');
            console.log(fightCell);
        }
        function parall(y,x,direction,flag) {
        if (flag === true) {
            x += direction;
            while (!$('#' + y + x).hasClass('busy') && x < 9 && x > 0) {
                $('#' + y + x).addClass('cell-highlighted');
                availableCell.push(String(y) + x);
                x += direction;
            }
        }
        else {
            y += direction;
            while (!$('#'+y+x).hasClass('busy') && y<9 && y>0) {
                $('#'+y+x).addClass('cell-highlighted');
                availableCell.push(String(y)+x);
                y += direction;
            }
        }
        (checkEnemy(y,x));
    }
    $('.figure').click(function(){
        if ($(this).attr('id')!==currentFigure){
            $('.cell').removeClass('cell-key');
            switchFlag = true;
            highlight(this);
        }
        if (!$(this).hasClass(currentPlayer))
            return;
        currentFigure = $(this).attr('id');
        var currentId = getObj(currentPlayer)[currentFigure];
        $('#'+currentId).addClass('cell-key');
    });
    var direction = 1;

    $('.figure').hover(function() {
        highlight(this);
    });
    /*
    * HELPER FUNCTION FOR STEP
    */
    function abroad(color, figureId){
        $('#'+figureId).children().css({
            width : figure_width*minFigure,
            height: figure_height*minFigure
        }).appendTo('.hit-'+opposite);
        $('#'+figureId).remove();
    }
    function endStep(currentFigure,figureCell,currentCell,cell_y,cell_x,typeFigure) {
        $('#'+currentFigure).appendTo('#'+currentCell);
        /*
        *
        * if it's first pawns step we can get some special in future
        */
        if (typeFigure === 'pawn' && Math.abs(figureCell[0]-cell_y) == 2)
        {
            pawn_special = currentCell;
        }
        getObj(currentPlayer)[currentFigure] = currentCell;
        $('#'+figureCell).removeClass('busy');
        $('#'+figureCell).removeClass(currentPlayer);
        $('#'+currentCell).addClass('busy');
        $('#'+currentCell).addClass(currentPlayer);
        changeState();
    }
    /*
    *
    * STEP
    */
    $cellObj.click(function(){
        /*
            stepInfo - recording step
            currentFigure - now *clicked* figure
            currentCell - id of current cell
            cell_x - current x position
            cell_y - current y position
            typeFigure - type of current figure
            figureCell - id of current figure
         */
        if (currentFigure!=='' && currentFigure!==$(this).children().attr('id')) {
            /*
             REMOVE HIGHLIGHT
             */
            $('.cell').removeClass('cell-highlight-enemy');
            $('.cell').removeClass('cell-highlighted');
            var lastStepInfo = '';
            var stepInfo = '';
            var currentCell = $(this).attr('id');
            var cell_x = currentCell[1];
            var cell_y = currentCell[0];
            // actually work only with id with number -__-
            var typeFigure = currentFigure.substring(2,currentFigure.length-1);
            var figureCell = getObj(currentPlayer)[currentFigure];

            /*
                ! CASE OF EN PASSANT !
                pawn_special - cell where is enemy pawn
                pawn_special_high - available cell for current player hit
                getObj() - function return ref to initialValue obj
                deadFigure - id of enemy figure
             */
            if (pawn_special_high[0]===cell_y && pawn_special_high[1]===cell_x && typeFigure === 'pawn') {
               $('#' + currentFigure).animate({
                   top: (Number(cell_y)-1) * step + border,
                   left: (Number(cell_x)+1) * step + 10
               });
               var deadFigure = getKey(getObj(opposite), pawn_special);
                /*
                    Make free enemy cell, and current figure cell;
                 */
                $('#'+pawn_special).removeClass('busy');
                $('#'+pawn_special).removeClass(opposite);

                $('#'+figureCell).removeClass('busy');
                $('#'+figureCell).removeClass(currentPlayer);
               /*
                    Change current cell of current figure in initialValue obj
                    Change current cell killed figure to null;
                 */
                getObj(currentPlayer)[currentFigure] = currentCell;
                getObj(opposite)[deadFigure] = null;
               /*
                    Make current cell busy
                 */
                $('#'+currentCell).addClass('busy');
                $('#'+currentCell).addClass(currentPlayer);
               /*
                    abroad killed figure
                 */
                abroad(opposite,deadFigure);
                endStep(currentFigure,figureCell,currentCell);

           }
            /*
                Clear pawn_special var
             */
            pawn_special = '';
            pawn_special_high = '';
           /*
                ! CASE OF MOVING OR KILLING !
             */
           if (availableCell.indexOf(currentCell)!=-1 || fightCell.indexOf(currentCell)!=-1) {
               console.log(availableCell);
               /*
                ! IT'S KILLING !
                */
               if (fightCell.indexOf(currentCell) != -1) {
                   var enemy = getKey(getObj(opposite), currentCell);
                   /*
                    If it's king - game over
                    */

                   if (enemy.substring(2) === 'king') {
                       $('.winner').text(currentPlayer + ' are win!!!');
                       $('.cover').show();
                       $('.cover-content').show();
                   }
                   abroad(opposite, enemy);
                   $('#' + currentCell).removeClass(opposite);
                   getObj(opposite)[enemy] = null;
               }
               /*
                PAWN CASE AGAIN !
                */
               if (newFigure[currentPlayer].indexOf(currentFigure) === -1 && typeFigure === 'pawn' && ((currentPlayer === 'white' && cell_y === '1') || (currentPlayer === 'black' && cell_y === '8'))) {
                   var current = currentFigure;
                   var currentColor = currentPlayer;
                   var newType = '';
                   $('.cover').show();
                   $('.list-content').show();
                   $('input[type=checkbox]').change(function () {
                       if ($(this).is(':checked')) {
                           newType = $(this).attr('id');
                           $('.custom').not(this).attr('disabled', true);
                       }
                       else{
                           $('.custom').attr('disabled', false);
                           newType = '';
                       }
                   });
                   $('.alert-button').on('click', function() {
                       $('.warn-alert').hide();
                   });
                   $('.accept').click(function () {
                       if (newType !== '') {
                           $('#' + current).children().attr('data-type', newType);
                           $('#' + current).children().attr('src', 'figure/'+newType+'-'+currentColor+'.png');
                           $('.cover').hide();
                           $('.list-content').hide();
                           newFigure[currentColor].push(current);
                       }
                       else
                           $('.warn-alert').show();
                   });
               }
               endStep(currentFigure, figureCell, currentCell,cell_y,cell_x,typeFigure);
               stepInfo = $('#letter'+Number(figureCell[1])).text() + (9-Number(figureCell[0])) + "->"
                   + $('#letter'+Number(cell_x)).text() + (9-Number(cell_y));
               $('.current-step').text(stepInfo);
               $('.history').text($('.history').text()+' '+stepInfo);

           }
            availableCell = [];
            fightCell = [];
            fightCell = [];
            currentFigure = '';
            $('#'+figureCell).removeClass('cell-key');
       }
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