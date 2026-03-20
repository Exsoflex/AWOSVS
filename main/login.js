const API = "http://localhost/AWOSVS/main";

// 🔥 NO mandar JWT si no existe o es inválido
// 🔴 DESACTIVADO TEMPORALMENTE PARA LOGIN

const token = localStorage.getItem("jwt");

if (token) {
    $.ajaxSetup({
        headers: {
            Authorization: `Bearer ${token}`
        }
    });
}

// Modal
const modalErrorLogin = new bootstrap.Modal("#exampleModal");

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