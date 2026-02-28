function obtenerUbicacionUsuario() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Actualizar el mapa con la ubicación del usuario
                const mapaIframe = document.getElementById('mapaGoogle');
               iniciarMapa(lat, lng, "Tu ubicación actual");
                
                document.getElementById('tituloMapa').textContent = 'Tu ubicación actual';
            },
            function(error) {
                console.log("Error al obtener ubicación:", error.message);
               //Ubicion por defecto de la CDMX
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

            // Guardar todas las ciudades
            todasLasCiudades = ciudades;
            
            // Mostrar todas las ciudades
            mostrarCiudades(todasLasCiudades);
        })
        .catch(error => console.error("Error al cargar:", error));
}

function mostrarCiudades(ciudades) {
    const tbody = document.getElementById("tbodyCiudad");
    const sinResultados = document.getElementById("sinResultados");
    
    tbody.innerHTML = "";
    
    if (ciudades.length === 0) {
        if (sinResultados) {
            sinResultados.style.display = "block";
        }
        return;
    }
    
    if (sinResultados) {
        sinResultados.style.display = "none";
    }
    
    ciudades.forEach(ciudad => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>
                <button class="btn-map btn btn-link text-primary text-decoration-none p-0"
                        data-lat="${ciudad.latitud}" 
                        data-lon="${ciudad.longitud}" 
                        data-nombre="${ciudad.nombre}"
                        data-pais="${ciudad.pais}">
                        ${ciudad.nombre}
                </button>
            </td>
            <td>${ciudad.pais}</td>
            <td>${ciudad.latitud}</td>
            <td>${ciudad.longitud}</td> 
            <td class="text-center">
                <button data-id="${ciudad.id_ciudad}" 
                        data-nombre="${ciudad.nombre}"
                        data-pais="${ciudad.pais}"
                        data-latitud="${ciudad.latitud}"
                        data-longitud="${ciudad.longitud}"
                        class="btn btn-warning btn-sm btn-editar">
                     Editar
                </button>
            </td>`;
        tbody.appendChild(fila);
    });

    // Boton para cambiar mapa
    document.querySelectorAll(".btn-map").forEach(boton => {
        boton.addEventListener("click", function(){
            const lat = this.getAttribute("data-lat");
            const lng = this.getAttribute("data-lon");
            const nombre = this.getAttribute("data-nombre");
            const pais = this.getAttribute("data-pais");

            // Actualizar el mapa
            const mapaIframe = document.getElementById('mapaGoogle');
            iniciarMapa(lat, lng, `${nombre}, ${pais}`);

            document.getElementById('tituloMapa').textContent = `${nombre}, ${pais}`;
        });     
    });
}

// Buscador en tiempo real
document.getElementById("buscarCiudad").addEventListener("input", function(e){
    const terminoBusqueda = e.target.value.toLowerCase().trim();

    if(terminoBusqueda === ""){
        mostrarCiudades(todasLasCiudades);
    } else {
        const ciudadesFiltradas = todasLasCiudades.filter(ciudad => {
            return ciudad.nombre.toLowerCase().includes(terminoBusqueda) ||
                   ciudad.pais.toLowerCase().includes(terminoBusqueda);
        });
        mostrarCiudades(ciudadesFiltradas);
    }
});

// Inicializar
obtenerUbicacionUsuario();
cargarCiudad();


// Variable para saber si estamos editando o agregando
let modoEdicion = false;

// AGREGAR nueva ciudad
document.getElementById("btnNuevaCiudad").addEventListener("click", function() {
    modoEdicion = false;
    
    // Limpiar el formulario
    document.getElementById("frmCiudad").reset();
    document.getElementById("txtIdCiudad").value = "";
    
    // Cambiar título del modal
    document.getElementById("tituloModal").textContent = "Agregar Ciudad";
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('mostrarForm'));
    modal.show();
});

// EDITAR ciudad
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("btn-editar")) {
        modoEdicion = true; 
        
        // Cambiar título del modal
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

// submit formulario
document.getElementById("frmCiudad").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = modoEdicion ? "servicio.php?editarCiudad" : "servicio.php?nuevaCiudad";
    
    fetch(url, {
        method: "POST",
        body: formData
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

//iniciar mapa meteorogico
const apiKey = "add6fb2d4a56082bd574efe3e676da6c";
let capa = "";

let mapa = null;
let capaMeteo = null;

let marcador = null;
function iniciarMapa(lat, lng, titulo){
    lat = parseFloat(lat);
    lng = parseFloat(lng);

    const timeStamp = Math.floor(Date.now() / 1000);
    if(!mapa){
        mapa = L.map('mapaMeteo').setView([lat, lng], 6);

         L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapa);

        
        
        capaMeteo = L.tileLayer(
            `https://tile.openweathermap.org/map/${capa}/{z}/{x}/{y}.png?appid=${apiKey}&ts=${timeStamp}`,
            { opacity: 1,
                className: 'capa-meteo'
             }
        ).addTo(mapa);

              cargarMarcadoresMapa(); 
    } else {
        
        mapa.setView([lat, lng], 6);
    }
    if(marcador) mapa.removeLayer(marcador);
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
document.getElementById("selectCapa").addEventListener("change", function(){
    capa = this.value;

    if(capaMeteo) mapa.removeLayer(capaMeteo);

    capaMeteo = L.tileLayer(
            `https://tile.openweathermap.org/map/${capa}/{z}/{x}/{y}.png?appid=${apiKey}`,
            { opacity: 1 }
        ).addTo(mapa);

    
});

//Obtener datos del clima
function obtenerDatosClima(lat, lng){
    
    fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lng}&appid=${apiKey}&units=metric&lang=es`)
    .then(r => r.json())
    .then(data => mostrarInfo(data, lng, lat));

    fetch(`https://api.openweathermap.org/data/2.5/air_pollution?lat=${lat}&lon=${lng}&appid=${apiKey}`)
    .then(r => r.json())
    .then(data => calidadAire(data));

    }

    function mostrarInfo(data, lng, lat){
        document.getElementById("contenidoInfo").innerHTML = `
            <h5>${data.name}, ${data.sys.country}</h5>
            <p><strong>Clima:</strong> ${data.weather[0].description}</p>
            <p><strong>Temperatura:</strong> ${data.main.temp} °C</p>
            <p><strong>Humedad:</strong> ${data.main.humidity} %</p>
            <p><strong>Viento:</strong> ${data.wind.speed} m/s</p>
        `;
        document.getElementById("panelInfo").style.display = "block";

    }

    function calidadAire(data){
        const niveles = ["Buena", "Moderada", "Dañina para grupos sensibles", "Dañina", "Muy dañina"];
        const colores = ["#009966", "#ffde33", "#ff9933", "#cc0033", "#660099"];
        const nivel = data.list[0].main.aqi;
        const componente = data.list[0].components;

        document.getElementById("contenidoInfo").innerHTML += `
            <h5>Calidad del aire: <span style="color:${colores[nivel-1]}">${niveles[nivel-1]}</span></h5>
            <p><strong>PM2.5:</strong> ${componente.pm2_5} μg/m³</p>
            <p><strong>PM10:</strong> ${componente.pm10} μg/m³</p>
            <p><strong>O3:</strong> ${componente.o3} μg/m³</p>
            <p><strong>NO2:</strong> ${componente.no2} μg/m³</p>
            <p><strong>SO2:</strong> ${componente.so2} μg/m³</p>
            <p><strong>CO:</strong> ${componente.co} μg/m³</p>
        `;

    }

    function cerrarPanel(){
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

    

