const express = require("express");
const cors = require("cors");

const app = express();
app.use(cors());

const PORT = process.env.PORT || 3000;
const API_KEY = process.env.API_KEY;

app.get("/api/clima/:ciudad", async (req, res) => {

    const ciudad = req.params.ciudad;

    try {
        const response = await fetch(
            `https://api.openweathermap.org/data/2.5/weather?q=${ciudad}&appid=${API_KEY}&units=metric&lang=es`
        );

        const data = await response.json();

        res.json({
            ciudad: data.name,
            temperatura: data.main.temp,
            descripcion: data.weather[0].description,
            icono: data.weather[0].icon
        });

    } catch (error) {
        res.status(500).json({ error: "Error al obtener clima" });
    }
});

app.listen(PORT, () => {
    console.log(`Servidor corriendo en http://localhost:${PORT}`);
});