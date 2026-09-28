window.addEventListener("DOMContentLoaded", domLoaded);

// When the DOM has finished loading, add the event listeners.
function domLoaded() {
    const f_in = document.getElementById("F_in");
    const c_in = document.getElementById("C_in");
    const convert_button = document.getElementById("convertButton");
    const message = document.getElementById("message");

    f_in.addEventListener("input", () => { clearInput(c_in) });
    c_in.addEventListener("input", () => { clearInput(f_in) });

    convert_button.addEventListener("click", () => {
        if (f_in.value === "" && c_in.value === "") {
            message.textContent = "Enter a temperature to convert";
        } else if (f_in.value === "") {
            f_in.value = convertCtoF(c_in.value);
            message.textContent = "";
        } else if (c_in.value === "") {
            c_in.value = convertFtoC(f_in.value);
            message.textContent = "";
        }
        displayIcon(f_in.value);
    })
}

function clearInput(input) {
    input.value = "";
}

function convertCtoF(C) {
    return C * 9 / 5 + 32;
}

function convertFtoC(F) {
    return (F - 32) * 5 / 9;
}

function displayIcon(temperature) {
    const weather_icon = document.getElementById("weatherIcon");

    if (temperature === "") {
        weather_icon.src = "images/C-F.png";
    } else if (temperature <= 32 && temperature > -200) {
        weather_icon.src = "images/cold.png";
    } else if (temperature > 32 && temperature < 90) {
        weather_icon.src = "images/cool.png";
    } else if (temperature >= 90 && temperature < 200) {
        weather_icon.src = "images/hot.png";
    } else {
        weather_icon.src = "images/dead.png";
    }
}
