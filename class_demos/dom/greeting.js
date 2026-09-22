const form = document.querySelector("#greeting-form");
const nameInput = document.querySelector("#name");
const message = document.querySelector("#message");

function greet(event) {
    const name = nameInput.value;
    message.textContent = "Hello" + name;
}

form.addEventListener("submit", greet);
