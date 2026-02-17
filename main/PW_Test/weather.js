const apikey = "add6fb2d4a56082bd574efe3e676da6c";
let city = "Nava, MX";
let units = "metric";
const api = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apikey}&units=${units}&lang=es`;
console.log(api);
let pageData = document.getElementById("data");
let img = document.getElementById("img");
console.log(pageData);


function apiFetching() {
    fetch(api)
    .then((res) => res.json())
    .then((data) => {
        console.log(data);
        let temp = data.main.temp;
        let icon = data.weather[0].icon;
        console.log(data.weather[0].icon);
        let iconUrl = `https://openweathermap.org/img/wn/${icon}@2x.png`;
        pageData.innerHTML = temp;
        img.src = iconUrl;
        console.log(iconUrl);
    })
}

apiFetching();