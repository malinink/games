function changeStatus() {

        var element = document.getElementById('statusContent');
        if(count===0){
        element.innerHTML = "search";
        count++;
    }
    else{
        element.innerHTML = "live";
    }
}
window.onload = changeStatus;

