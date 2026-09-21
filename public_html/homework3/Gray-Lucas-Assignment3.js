function isStrongPassword(password) {

    let strongLength = false;
    let easyWords = false;
    let hasNum = false;

    if (password.length >= 8) {
        strongLength = true;
    }

    if (password.indexOf("password") === -1 && password.indexOf("1234") === -1) {
        easyWords = true;
    }

    for (let i = 0; i < password.length; i++) {
        if ((password.charCodeAt(i) >= 48 && password.charCodeAt(i) <= 57)) {
            hasNum = true;
        }
    }

    if (strongLength && easyWords && hasNum) {
        console.log("Good password!");
        return true;
    } else if (!(strongLength) && easyWords && hasNum) {
        console.log("No good. Password must be at least 8 characters long.");
        return false;
    } else if (strongLength && !(easyWords) && hasNum) {
        console.log("No good. Password cannot contain the words 'password' or '1234'.");
        return false;
    } else if (strongLength && easyWords && !(hasNum)) {
        console.log("No good. Password must contain at least one number.");
        return false;
    } else {
        return false;
    }
}

isStrongPassword("qwerty1")
isStrongPassword("qwertypassword1")
isStrongPassword("qwertyABC")
isStrongPassword("qwerty123")
