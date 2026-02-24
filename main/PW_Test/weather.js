const apikey = "add6fb2d4a56082bd574efe3e676da6c";
let units = "metric";

function apiFetching(city, dataId, imgId) {

    const api = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apikey}&units=${units}&lang=es`;

    fetch(api)
    .then((res) => res.json())
    .then((data) => {
        console.log(data);

        let temp = data.main.temp;
        let icon = data.weather[0].icon;

        let iconUrl = `https://openweathermap.org/img/wn/${icon}@2x.png`;

        document.getElementById(dataId).innerHTML = `${temp} °C`;
        document.getElementById(imgId).src = iconUrl;
    })
    .catch((error) => {
        console.log("Error:", error);
    });
}

apiFetching("Nava, MX", "data1", "img1");
apiFetching("Monclova, MX", "data2", "img2");
apiFetching("Piedras Negras, MX", "data3", "img3");