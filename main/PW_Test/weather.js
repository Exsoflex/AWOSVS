function apiFetching(city, dataId, imgId) {

    const api = `https://awosvs.onrender.com/api/clima/${city}`;

    fetch(api)
    .then(res => res.json())
    .then(data => {

        let temp = data.temperatura;
        let icon = data.icono;

        let iconUrl = `https://openweathermap.org/img/wn/${icon}@2x.png`;

        document.getElementById(dataId).innerHTML = `${temp} °C`;
        document.getElementById(imgId).src = iconUrl;
    })
    .catch(error => {
        console.log("Error:", error);
    });
}

apiFetching("Nava, MX", "data1", "img1");
apiFetching("Monclova, MX", "data2", "img2");
apiFetching("Piedras Negras, MX", "data3", "img3");
apiFetching("New York, US", "data4", "img4");
apiFetching("Tokyo, JP", "data5", "img5");
apiFetching("Alexandria, EG", "data6", "img6");


