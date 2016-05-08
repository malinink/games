
function changeStatus(status,ids) {
    var element = document.getElementById('statusContent');
    //check what page we have
    if (typeof ids[0] !== "undefined") {
        if (parseInt(ids[0]) === parseInt(ids[1]) || parseInt(ids[0]) === parseInt(ids[2])) {
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
    if (typeof $('.inf').attr('data-user') !== "undefined"){
    changeStatus($('.stateGame').attr('value'),
            [
                $('.inf').attr('data-user'),
                $('.inf').attr('data-player-white'),
                $('.inf').attr('data-player-black')
            ]
            );
    }else{
        changeStatus($('.state').attr('value'),
            [
                $('.inf').attr('data-user'),
                $('.inf').attr('data-player-white'),
                $('.inf').attr('data-player-black')
            ]
            );
        
    }
});


