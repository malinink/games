function changeStatus(status) {
    var element = document.getElementById('statusContent');
    element.innerHTML = status;
}
window.onload = changeStatus(document.getElementById('status').innerHTML);

