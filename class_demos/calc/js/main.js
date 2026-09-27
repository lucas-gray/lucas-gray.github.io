const h1 = document.querySelector("h1");

const msg = document.getElementById("message");
const add_btn = document.getElementById("plus")
const sub_btn = document.getElementById("minus")
const mult_btn = document.getElementById("times")
const div_btn = document.getElementById("divide")
const output = document.getElementById("answer")

function hello() {
    // alert("Hello, DOM!")
    // alert("Hello, " + h1.innerHTML)
    msg.innerHTML = "Hello, " + h1.innerHTML;
}

// Loads NaN when page loads if placed here:
//const num1 = parseFloat(document.getElementById("val1").value)

add_btn.addEventListener("click", add)
sub_btn.addEventListener("click", subtract)
mult_btn.addEventListener('click', ()=>{
    let nums = getOperands();
    output.innerHTML = nums!=undefined ? nums[0] * nums[1] : '?';
})
div_btn.addEventListener("click", divide)

// Get the values as number types (so math can happen without NaN)
function getOperands(){
    const num1 = parseFloat(document.getElementById("val1").value)
    const num2 = parseFloat(document.getElementById("val2").value)

    if (!isNaN(num1) && !isNaN(num2)) { // both are legit numbers
        return [num1, num2];
    }
    else {
        msg.innerHTML = "Invalid input";
        return undefined;
    }
}

function add() {
    // let num1 = parseFloat(document.getElementById("val1").value)
    // let num2 = parseFloat(document.getElementById("val2").value)
    // let result = num1 + num2;
    // output.innerHTML = result; 


    // but what if there's nothing a box??
    let nums = getOperands();
    output.innerHTML = nums!=undefined ? nums[0] + nums[1] : 'say wha..?';
}

function subtract() {
    let nums = getOperands();
    output.innerHTML = nums!=undefined ? nums[0] - nums[1] : '?';
}

function divide() {
    let nums = getOperands();
    output.innerHTML = nums!=undefined ? nums[0] / nums[1] : '?';
}
