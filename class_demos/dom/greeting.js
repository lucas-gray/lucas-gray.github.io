const form = document.querySelector("#greeting-form");
const nameInput = document.querySelector("#name");
const message = document.querySelector("#message");

function greet(event) {
    event.preventDefault();
    const name = nameInput.value.trim();
    if (name === "") {
        message.textContent = "Please enter your name.";
        return;
    }
    message.textContent = "Hello, " + name;
}

form.addEventListener("submit", greet);
