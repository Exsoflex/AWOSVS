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

apiFetching("Nava", "data1", "img1");
apiFetching("Monclova", "data2", "img2");
apiFetching("Piedras Negras", "data3", "img3");
apiFetching("New York", "data4", "img4");
apiFetching("Tokyo", "data5", "img5");
apiFetching("Alexandria", "data6", "img6");


