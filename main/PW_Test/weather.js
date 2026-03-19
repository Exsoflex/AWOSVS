function apiFetching(city, cityId, dataId, descId, humId, imgId) {
    const api = `https://awosvs.onrender.com/api/clima/${city}`;

    fetch(api)
        .then((res) => res.json())
        .then((data) => {
            if (data.error) {
                document.getElementById(dataId).innerHTML = 'No hay datos';
                return;
            }

            const temperatura = data.temperatura;
            const descripcion = data.descripcion;
            const humedad = data.humedad;
            const icon = data.icono;
            const nombreCiudad = data.ciudad || city;

            const iconUrl = `https://openweathermap.org/img/wn/${icon}@2x.png`;

            document.getElementById(cityId).innerText = nombreCiudad;
            document.getElementById(dataId).innerHTML = `<strong>Temperatura:</strong> ${temperatura} °C`;
            document.getElementById(descId).innerHTML = `<strong>Descripción:</strong> ${descripcion}`;
            document.getElementById(humId).innerHTML = `<strong>Humedad:</strong> ${humedad}%`;
            document.getElementById(imgId).src = iconUrl;
            document.getElementById(imgId).alt = descripcion;
        })
        .catch((error) => {
            console.log("Error:", error);
            document.getElementById(dataId).innerHTML = 'Error al obtener clima';
        });
}

apiFetching("Nava", "city1", "data1", "desc1", "hum1", "img1");
apiFetching("Monclova", "city2", "data2", "desc2", "hum2", "img2");
apiFetching("Piedras Negras", "city3", "data3", "desc3", "hum3", "img3");
apiFetching("New York", "city4", "data4", "desc4", "hum4", "img4");
apiFetching("Tokyo", "city5", "data5", "desc5", "hum5", "img5");
apiFetching("Alexandria", "city6", "data6", "desc6", "hum6", "img6");

const searchBtn = document.getElementById("searchBtn");
searchBtn.addEventListener("click", () => {
    const searchCity = document.getElementById("searchCity").value.trim();
    if (!searchCity) return;

    apiFetching(searchCity, "searchCityName", "searchData", "searchDesc", "searchHum", "searchImg");
    document.getElementById("searchResult").style.display = "block";
});


