/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

function wrapSelection(tag) {
    var textArea = document.getElementById("text");
    if (!textArea) { return; }
    var start = textArea.selectionStart;
    var end = textArea.selectionEnd;
    var selected = textArea.value.substring(start, end);
    var snippet = (tag === 'url') ? '[url=]' + selected + '[/url]' : '[' + tag + ']' + selected + '[/' + tag + ']';
    textArea.value = textArea.value.substring(0, start) + snippet + textArea.value.substring(end);
    var cursor = (tag === 'url') ? start + 5 : start + tag.length + 1;
    textArea.focus();
    textArea.selectionStart = cursor;
    textArea.selectionEnd = cursor + selected.length;
}

var boldButton = document.getElementById("boldButton");
if (boldButton) {
    boldButton.addEventListener("click", function () { wrapSelection("b"); });
}

var italicButton = document.getElementById("italicButton");
if (italicButton) {
    italicButton.addEventListener("click", function () { wrapSelection("i"); });
}

var underlineButton = document.getElementById("underlineButton");
if (underlineButton) {
    underlineButton.addEventListener("click", function () { wrapSelection("u"); });
}

var linkButton = document.getElementById("linkButton");
if (linkButton) {
    linkButton.addEventListener("click", function () { wrapSelection("url"); });
}

function togglePasswordHint() {
    var password = document.getElementById("password");
    var confirmation = document.getElementById("password-confirmation");
    var hint = document.getElementById("password-hint");
    if (!password || !confirmation || !hint) { return; }
    if (confirmation.value.length === 0) {
        hint.classList.remove("visible", "hint-error", "hint-ok");
        return;
    }
    var match = password.value === confirmation.value;
    hint.textContent = match ? "Senhas coincidem." : "As senhas não coincidem.";
    hint.classList.add("visible");
    hint.classList.toggle("hint-error", !match);
    hint.classList.toggle("hint-ok", match);
}

var passwordField = document.getElementById("password");
var confirmationField = document.getElementById("password-confirmation");
if (passwordField && confirmationField) {
    passwordField.addEventListener("input", togglePasswordHint);
    confirmationField.addEventListener("input", togglePasswordHint);
}

var avatarRadios = document.querySelectorAll("#avatar-list input[name=avatar]");
avatarRadios.forEach(function (radio) {
    radio.addEventListener("change", function () {
        avatarRadios.forEach(function (r) {
            r.closest(".avatar-option").classList.toggle("selected", r.checked);
        });
    });
    if (radio.checked) {
        radio.closest(".avatar-option").classList.add("selected");
    }
});