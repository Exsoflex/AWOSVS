function obtenerUbicacionUsuario() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Actualizar el mapa con la ubicación del usuario
                const mapaIframe = document.getElementById('mapaGoogle');
                mapaIframe.src = `https://www.google.com/maps?q=${lat},${lng}&output=embed`;
                
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
            mapaIframe.src = `https://www.google.com/maps?q=${lat},${lng}&output=embed`;

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