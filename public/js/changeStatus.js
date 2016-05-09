
function changeStatus(status,userId,playersId) {
    var element = document.getElementById('statusContent');
    //check what page we have
    if (typeof userId !== "undefined") {
        if (playersId.indexOf(parseInt(userId))>=0) {
            //if gamer
            element.innerHTML = status;
        }
        else {
            //if viewer
            element.innerHTML = "nogame";
        }
    }
    else {
            element.innerHTML = status;
    }
}
$(document).ready(function () {
    if (typeof $('.board').attr('data-user') !== "undefined") {
        changeStatus($('.stateGame').attr('value'),
                $('.board').attr('data-user'),
                [
                    parseInt($('.board').attr('data-player-white')),
                    parseInt($('.board').attr('data-player-black'))
                ]
                );
    } else {
        changeStatus($('.state').attr('value'),
                $('.board').attr('data-user'),
                [
                    parseInt($('.board').attr('data-player-white')),
                    parseInt($('.board').attr('data-player-black'))
                ]
                );

    }
});


