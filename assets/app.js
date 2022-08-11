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

document.getElementById("boldButton").addEventListener("click", function(){
    var textArea = document.getElementById("text");
    textArea.value += '[b][/b]'; 
    var end = textArea.selectionEnd;
    textArea.focus();
    textArea.selectionEnd= end - 4;
});

document.getElementById("italicButton").addEventListener("click", function(){
    var textArea = document.getElementById("text");
    textArea.value += '[i][/i]'; 
    var end = textArea.selectionEnd;
    textArea.focus();
    textArea.selectionEnd= end - 4;
});

document.getElementById("underlineButton").addEventListener("click", function(){
    var textArea = document.getElementById("text");
    textArea.value += '[u][/u]'; 
    var end = textArea.selectionEnd;
    textArea.focus();
    textArea.selectionEnd= end - 4;
});

document.getElementById("linkButton").addEventListener("click", function(){
    var textArea = document.getElementById("text");
    textArea.value += '[url=][/url]'; 
    var end = textArea.selectionEnd;
    textArea.focus();
    textArea.selectionEnd= end - 7;

});


