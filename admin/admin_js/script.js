document.getElementById('toggle-btn').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.getElementById('content').classList.toggle('collapsed');
});

if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href.split('?')[0]);
}