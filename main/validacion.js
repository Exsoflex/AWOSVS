$(function() {
    const token = localStorage.getItem("jwt");
    const paginasPublicas = ["login.html", "index_no_session.html"];
    const paginaActual = window.location.pathname.split("/").pop();

    if (!token) {
        if (!paginasPublicas.includes(paginaActual)) {
            window.location.href = "/AWOSVS/main/index_no_session.html";
        }
        return;
    }
    $.ajaxSetup({
        headers: { Authorization: `Bearer ${token}` }
    });
});
