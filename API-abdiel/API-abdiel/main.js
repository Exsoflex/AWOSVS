function escaparHTML(texto) {
    const div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
}

var token = localStorage.getItem("jwt");
let esAdmin = false;

if(token){
    const payload = JSON.parse(atob(token.split(".")[1]));
    const tipo = payload.sub.split("/")[2];
    esAdmin = tipo === "1";
}



function obtenerUbicacionUsuario() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                iniciarMapa(lat, lng, "Tu ubicación actual");
                document.getElementById('tituloMapa').textContent = 'Tu ubicación actual';
            },
            function(error) {
                console.log("Error al obtener ubicación:", error.message);
                document.getElementById('tituloMapa').textContent = 'Ubicación por defecto';
            }
        );
    } else {
        alert("Tu navegador no soporta geolocalización");
    }
}

let todasLasCiudades = [];

function cargarCiudad() {
    fetch("servicio.php?ciudad")
        .then(respuesta => respuesta.json())
        .then(ciudades => {
            if (ciudades.error) {
                alert("Error: " + ciudades.error);
                return;
            }
            todasLasCiudades = ciudades;
            mostrarCiudades(todasLasCiudades);
        })
        .catch(error => console.error("Error al cargar:", error));
}

function mostrarCiudades(ciudades) {
    const tbody = document.getElementById("tbodyCiudad");
    const sinResultados = document.getElementById("sinResultados");
    
    tbody.innerHTML = "";
    
    if (ciudades.length === 0) {
        if (sinResultados) sinResultados.style.display = "block";
        return;
    }
    
    if (sinResultados) sinResultados.style.display = "none";
    
    ciudades.forEach(ciudad => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>
                <button class="btn-map btn btn-link text-primary text-decoration-none p-0"
                        data-lat="${escaparHTML(ciudad.latitud)}" 
                        data-lon="${escaparHTML(ciudad.longitud)}" 
                        data-nombre="${escaparHTML(ciudad.nombre)}"
                        data-pais="${escaparHTML(ciudad.pais)}">
                        ${escaparHTML(ciudad.nombre)}
                </button>
            </td>
            <td>${escaparHTML(ciudad.pais)}</td>
            <td>${escaparHTML(ciudad.latitud)}</td>
            <td>${escaparHTML(ciudad.longitud)}</td>
            ${esAdmin ? `
            <td class="text-center">
                <button data-id="${escaparHTML(ciudad.id_ciudad)}" 
                        data-nombre="${escaparHTML(ciudad.nombre)}"
                        data-pais="${escaparHTML(ciudad.pais)}"
                        data-latitud="${escaparHTML(ciudad.latitud)}"
                        data-longitud="${escaparHTML(ciudad.longitud)}"
                        class="btn btn-warning btn-sm btn-editar">
                     Editar
                </button>
            </td>` : ""}
        `;
        tbody.appendChild(fila);
    });

    document.querySelectorAll(".btn-map").forEach(boton => {
        boton.addEventListener("click", function(){
            const lat = this.getAttribute("data-lat");
            const lng = this.getAttribute("data-lon");
            const nombre = this.getAttribute("data-nombre");
            const pais = this.getAttribute("data-pais");
            iniciarMapa(lat, lng, `${nombre}, ${pais}`);
            document.getElementById('tituloMapa').textContent = `${nombre}, ${pais}`;
        });     
    });
}

// Buscador en tiempo real
document.getElementById("buscarCiudad").addEventListener("input", function(e){
    const terminoBusqueda = e.target.value.toLowerCase().trim();

    if (terminoBusqueda === "") {
        mostrarCiudades(todasLasCiudades);
    } else {
        const ciudadesFiltradas = todasLasCiudades.filter(ciudad => {
            return ciudad.nombre.toLowerCase().includes(terminoBusqueda) ||
                   ciudad.pais.toLowerCase().includes(terminoBusqueda);
        });
        mostrarCiudades(ciudadesFiltradas);
    }
});

if (esAdmin) {
    document.getElementById("thAcciones").style.display = ""; 
} else {
    document.getElementById("btnNuevaCiudad").style.display = "none";
}

// Inicializar
obtenerUbicacionUsuario();
cargarCiudad();

// Variable para saber si estamos editando o agregando
let modoEdicion = false;

// AGREGAR nueva ciudad
document.getElementById("btnNuevaCiudad").addEventListener("click", function() {
    modoEdicion = false;
    document.getElementById("frmCiudad").reset();
    document.getElementById("txtIdCiudad").value = "";
    document.getElementById("tituloModal").textContent = "Agregar Ciudad";
    const modal = new bootstrap.Modal(document.getElementById('mostrarForm'));
    modal.show();
});

// EDITAR ciudad
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("btn-editar")) {
        modoEdicion = true; 
        document.getElementById("tituloModal").textContent = "Editar Ciudad";
        
        const id = e.target.dataset.id;
        fetch(`servicio.php?ciudad&id=${id}`)
            .then(respuesta => respuesta.json())
            .then(ciudad => {
                if (ciudad.error) {
                    alert("Error: " + ciudad.error);
                    return;
                }
                document.getElementById("txtIdCiudad").value = ciudad.id_ciudad;
                document.getElementById("txtNombre").value = ciudad.nombre;
                document.getElementById("txtPais").value = ciudad.pais;
                document.getElementById("txtLatitud").value = ciudad.latitud;
                document.getElementById("txtLongitud").value = ciudad.longitud;

                const modal = new bootstrap.Modal(document.getElementById('mostrarForm'));
                modal.show();
            });
    }
});

// Submit formulario
document.getElementById("frmCiudad").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = modoEdicion ? "servicio.php?editarCiudad" : "servicio.php?nuevaCiudad";
    
    fetch(url, {
        method: "POST",
        body: formData,
        headers: {
            Authorization: `Bearer ${token}`
        }
    })
    .then(respuesta => respuesta.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('mostrarForm'));
            modal.hide();
            alert(modoEdicion ? "Ciudad editada correctamente" : "Ciudad agregada correctamente");
            cargarCiudad(); 
        } else {
            alert("Error al guardar la ciudad");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error al procesar la solicitud");
    });
});

// Mapa meteorológico
const apiKey = "add6fb2d4a56082bd574efe3e676da6c";
let capa = "";
let mapa = null;
let capaMeteo = null;
let marcador = null;

function iniciarMapa(lat, lng, titulo) {
    lat = parseFloat(lat);
    lng = parseFloat(lng);

    if (!mapa) {
        mapa = L.map('mapaMeteo').setView([lat, lng], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapa);

        capaMeteo = L.tileLayer(
            `https://tile.openweathermap.org/map/${capa}/{z}/{x}/{y}.png?appid=${apiKey}`,
            { opacity: 1, className: 'capa-meteo' }
        ).addTo(mapa);

        cargarMarcadoresMapa(); 
    } else {
        mapa.setView([lat, lng], 6);
    }

    if (marcador) mapa.removeLayer(marcador);

    const icono = L.divIcon({
        className: '',
        html: `<div style="
            width: 16px; height: 16px;
            background: #667eea;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
        "></div>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });

    marcador = L.marker([lat, lng], { icon: icono }).addTo(mapa);
    obtenerDatosClima(lat, lng);
    document.getElementById('tituloMapa').textContent = titulo;
}

// Cambiar capa meteorológica
document.getElementById("selectCapa").addEventListener("change", function() {
    capa = this.value;

    if (capaMeteo) mapa.removeLayer(capaMeteo);

    capaMeteo = L.tileLayer(
        `https://tile.openweathermap.org/map/${capa}/{z}/{x}/{y}.png?appid=${apiKey}`,
        { opacity: 1 }
    ).addTo(mapa);

    if (ultimosDatosClima) {
        mostrarInfoPorCapa(ultimosDatosClima, ultimosDatosAire, capa);
    }
});

let ultimosDatosClima = null;
let ultimosDatosAire = null;

function obtenerDatosClima(lat, lng) {
    fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lng}&appid=${apiKey}&units=metric&lang=es`)
        .then(r => r.json())
        .then(data => {
            ultimosDatosClima = data;
            mostrarInfoPorCapa(ultimosDatosClima, ultimosDatosAire, capa);
        });

    fetch(`https://api.openweathermap.org/data/2.5/air_pollution?lat=${lat}&lon=${lng}&appid=${apiKey}`)
        .then(r => r.json())
        .then(data => {
            ultimosDatosAire = data;
        });
}

function cerrarPanel() {
    document.getElementById("panelInfo").style.display = "none";
}

function cargarMarcadoresMapa() {
    fetch("servicio.php?ciudadesUbicacion")
        .then(r => r.json())
        .then(ciudades => {
            ciudades.forEach(ciudad => {
                L.marker([ciudad.latitud, ciudad.longitud])
                 .addTo(mapa)
                 .bindPopup(`${ciudad.nombre}, ${ciudad.pais}`);
            });
        });
}

function mostrarInfoPorCapa(datosClima, datosAire, capaActual) {
    let html = `<h5>${datosClima.name}, ${datosClima.sys.country}</h5>`;

    switch(capaActual) {
        case "temp_new":
            html += `
                <p><strong>🌡️ Temperatura:</strong> ${datosClima.main.temp} °C</p>
                <p><strong>Sensación térmica:</strong> ${datosClima.main.feels_like} °C</p>
                <p><strong>Mínima:</strong> ${datosClima.main.temp_min} °C</p>
                <p><strong>Máxima:</strong> ${datosClima.main.temp_max} °C</p>
            `;
            break;
        case "wind_new":
            html += `
                <p><strong>💨 Velocidad:</strong> ${datosClima.wind.speed} m/s</p>
                <p><strong>Dirección:</strong> ${datosClima.wind.deg}°</p>
                ${datosClima.wind.gust ? `<p><strong>Ráfagas:</strong> ${datosClima.wind.gust} m/s</p>` : ""}
            `;
            break;
        case "pressure_new":
            html += `
                <p><strong>🔵 Presión:</strong> ${datosClima.main.pressure} hPa</p>
                <p><strong>Presión a nivel del mar:</strong> ${datosClima.main.sea_level ?? "N/A"} hPa</p>
                <p><strong>Presión suelo:</strong> ${datosClima.main.grnd_level ?? "N/A"} hPa</p>
            `;
            break;
        case "clouds_new":
            html += `
                <p><strong>☁️ Nubosidad:</strong> ${datosClima.clouds.all} %</p>
                <p><strong>Clima:</strong> ${datosClima.weather[0].description}</p>
                ${datosClima.visibility ? `<p><strong>Visibilidad:</strong> ${datosClima.visibility / 1000} km</p>` : ""}
            `;
            break;
        case "precipitation_new":
            html += `
                <p><strong>🌧️ Clima:</strong> ${datosClima.weather[0].description}</p>
                <p><strong>Humedad:</strong> ${datosClima.main.humidity} %</p>
                ${datosClima.rain ? `<p><strong>Lluvia 1h:</strong> ${datosClima.rain["1h"] ?? 0} mm</p>` : "<p>Sin lluvia registrada</p>"}
                ${datosClima.snow ? `<p><strong>Nieve 1h:</strong> ${datosClima.snow["1h"] ?? 0} mm</p>` : ""}
            `;
            break;
        default:
            html += `
                <p><strong>Clima:</strong> ${datosClima.weather[0].description}</p>
                <p><strong>Temperatura:</strong> ${datosClima.main.temp} °C</p>
                <p><strong>Humedad:</strong> ${datosClima.main.humidity} %</p>
                <p><strong>Viento:</strong> ${datosClima.wind.speed} m/s</p>
            `;
    }

    document.getElementById("contenidoInfo").innerHTML = html;
    document.getElementById("panelInfo").style.display = "block";
}