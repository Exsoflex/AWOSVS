const API = "http://localhost/AWOSVS/main";
const SERVICIO_PREF = "/AWOSVS/preferencias_usuario/sofia/servicio.php";

const token = localStorage.getItem("jwt");
const paginasPublicas = ["login.html", "index_no_session.html"];

const paginaActual = window.location.pathname.split("/").pop();

if (token) {
    $.ajaxSetup({
        headers: {
            Authorization: `Bearer ${token}`
        }
    });
} else {
    if (!paginasPublicas.includes(paginaActual)) {
    window.location.href = "/AWOSVS/main/index_no_session.html";
    }
}

// =============================================
// LOGIN
$("#frmLogin").submit(function (event) {
    event.preventDefault();

    $.post(`${API}/servicioInicioSesion.php?iniciarSesion`, $(this).serialize(), function (respuesta) {

        if (respuesta === "error") {
            modalErrorLogin.show();
            return;
        }

        if (respuesta === "error_bd" || respuesta === "error_conexion") {
            console.log("Problema con la base de datos");
            return;
        }

        localStorage.setItem("jwt", respuesta);
        window.location = "index.html";

    }).fail(function (error) {
        console.log("ERROR AJAX:", error);
    });
});

//* BOTONES SESIÓN + TEMA EN TODAS LAS PÁGINAS
document.addEventListener("DOMContentLoaded", function () {

    //  si hay sesión activa
    const token = localStorage.getItem("jwt");
    const temaGuardado = localStorage.getItem("pref_tema");
    if (token && temaGuardado) {
        $('body').removeClass('claro oscuro').addClass(temaGuardado);
    }

    actualizarBotonesSesion();

    const btnCerrar = document.getElementById("btnCerrarSesion");
    if (btnCerrar) {
        btnCerrar.addEventListener("click", function () {
            // Login - limpiar sesión
            localStorage.removeItem("jwt");
            // Preferencias - limpiar tema al cerrar sesión
            localStorage.removeItem("pref_tema");
            localStorage.removeItem("pref_unidad");
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

function aplicarTema(tema) {
    $('body').removeClass('claro oscuro').addClass(tema);
}

document.addEventListener("DOMContentLoaded", function () {

    const formPref = document.getElementById("formPreferencias");
    if (!formPref) return; 

    const token = localStorage.getItem("jwt");

    if (token) {
        $.get(SERVICIO_PREF + "?obtener_preferencias", function (datos) {
            if (datos.logueado) {
                aplicarTema(datos.tema);
                localStorage.setItem("pref_tema", datos.tema);
                localStorage.setItem("pref_unidad", datos.unidad_temperatura);
                $('input[name="tema"][value="' + datos.tema + '"]').prop("checked", true);
                const unidadValor = datos.unidad_temperatura === "Celsius" ? "C" : "F";
                $('input[name="unidad"][value="' + unidadValor + '"]').prop("checked", true);
            }
        }, "json");
    } else {
        // solo la carga porque la guarda en localsorage
        const temaLocal   = localStorage.getItem("pref_tema");
        const unidadLocal = localStorage.getItem("pref_unidad");
        if (temaLocal) {
            aplicarTema(temaLocal);
            $('input[name="tema"][value="' + temaLocal + '"]').prop("checked", true);
        }
        if (unidadLocal) {
            const unidadValor = unidadLocal === "Celsius" ? "C" : "F";
            $('input[name="unidad"][value="' + unidadValor + '"]').prop("checked", true);
        }
    }

    // Preferencias - cambio de tema en tiempo real
    $('input[name="tema"]').change(function () {
        aplicarTema($(this).val());
    });

    // Preferencias - guardar al dar clic en botón
    $("#btnGuardar").click(function () {
        const token = localStorage.getItem("jwt");
        const unidad = $('input[name="unidad"]:checked').val() === "C" ? "Celsius" : "Fahrenheit";
        const tema   = $('input[name="tema"]:checked').val();

        if (!token) {
            // Preferencias - guardar temporal en localStorage
            localStorage.setItem("pref_unidad", unidad);
            localStorage.setItem("pref_tema", tema);
            $('#btnGuardar').text('¡Guardado!').removeClass('btn-primary').addClass('btn-success');
            setTimeout(() => {
                $('#btnGuardar').text('Guardar Cambios').removeClass('btn-success').addClass('btn-primary');
            }, 2000);
            return;
        }

        // Preferencias - guardar en BD si hay sesión
        $.ajax({
            url: SERVICIO_PREF + "?guardar_preferencias",
            method: "POST",
            headers: { Authorization: `Bearer ${token}` },
            data: { unidad: unidad, tema: tema },
            success: function (respuesta) {
                if (respuesta === "correcto") {
                    localStorage.setItem("pref_tema", tema);
                    localStorage.setItem("pref_unidad", unidad);
                    $('#btnGuardar').text('¡Guardado!').removeClass('btn-primary').addClass('btn-success');
                    setTimeout(() => {
                        $('#btnGuardar').text('Guardar Cambios').removeClass('btn-success').addClass('btn-primary');
                    }, 2000);
                } else {
                    alert("Error al guardar: " + respuesta);
                }
            },
            error: function(err) {
                console.log("ERROR:", err);
            }
        });
    });
});