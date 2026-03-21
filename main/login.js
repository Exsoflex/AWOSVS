const API = "http://localhost/AWOSVS/main";


const token = localStorage.getItem("jwt");

if (token) {
    $.ajaxSetup({
        headers: {
            Authorization: `Bearer ${token}`
        }
    });
}



// 🔍 Login
$("#frmLogin").submit(function (event) {
    event.preventDefault();

    console.log("Intentando iniciar sesión");
    console.log("DATOS:", $(this).serialize());

    $.post(`${API}/servicioInicioSesion.php?iniciarSesion`, $(this).serialize(), function (respuesta) {

        console.log("RESPUESTA:", respuesta);

        if (respuesta === "error") {
            modalErrorLogin.show();
            return;
        }

        if (respuesta === "error_bd" || respuesta === "error_conexion") {
            console.log("Problema con la base de datos");
            return;
        }

        console.log("Inicio de sesión exitoso");

        // ✔ Guardar token limpio
        localStorage.setItem("jwt", respuesta);
        window.location = "index.html"
    }).fail(function (error) {
        console.log("ERROR AJAX:", error);
    });
});


// botoens index
 document.addEventListener("DOMContentLoaded", function () {

    actualizarBotonesSesion();

    const btnCerrar = document.getElementById("btnCerrarSesion");
    if (btnCerrar) {
        btnCerrar.addEventListener("click", function () {
            localStorage.removeItem("jwt");
            window.location = "login.html";
        });
    }
});

function actualizarBotonesSesion() {
    const token = localStorage.getItem("jwt");
    const btnIniciar = document.getElementById("btnIniciarSesion");
    const btnCerrar  = document.getElementById("btnCerrarSesion");

    if (!btnIniciar || !btnCerrar) return;

    if (token) {
        btnIniciar.style.visibility = "hidden";
        btnCerrar.style.visibility  = "visible";
    } else {
        btnIniciar.style.visibility = "visible";
        btnCerrar.style.visibility  = "hidden";
    }
}