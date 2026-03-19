if (location.search == "?reload") {
    window.location = "login.html"
}

// La URL de la API y endpoints cambialos según el tunnel, hosting, local o ubicación del proyecto de la aplicación web
// URL de la API
const API = "http://localhost/AWOSVS/main"
// Añade a toda petición que se realice, el header que contiene el JWT, obtenido de un almacenamiento muy persistente
$.ajaxSetup({
    headers: {
        Authorization: `Bearer ${localStorage.getItem("jwt")}`
    }
})

const modalErrorLogin = new bootstrap.Modal("#exampleModal", {
    keyboard: false
})

// Endpoint combinado con la API para comprobar si se inició sesión
$.get(`${API}/servicioInicioSesion.php?sesion`, function (sesion) {
    if (sesion.length) {
        // Si inició sesión

        return
    }

    // Si no inició sesión
    // Podrías añadir un redireccionamiento si lo crees prudente
})

$("#frmLogin").submit(function (event) {
   console.log("Intentando iniciar sesión")
    event.preventDefault()

    // Endpoint combinado con la API para iniciar sesión
    $.post(`${API}/servicioInicioSesion.php?iniciarSesion`, $(this).serialize(), function (respuesta) {
           console.log("hola")
        console.log(respuesta)
        if (respuesta == "error") {
            modalErrorLogin.show()
            console.log("Error al iniciar sesión")
            return
        }
        console.log("Inicio de sesión exitoso")

        // Guarda el JWT en un almacenamiento persistente
        localStorage.setItem("jwt", respuesta)
        // Cambia el redireccionamiento según tu aplicación web
     //   window.location = "index.html"
    })
    .fail(function (error) {
        console.log("Error al iniciar sesiónhhhhhh")
    })
})





$.get(`${API}/servicioInicioSesion.php?sesion`, function (sesion) {
    if (sesion.length) {
        $("#btnCerrarSesion")
        .show()
        .css("visibility", "visible")

        return
    }

    $("#btnIniciarSesion")
    .show()
    .css("visibility", "visible")

})