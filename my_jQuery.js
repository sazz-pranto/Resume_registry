var imported = document.createElement('script');
imported.src = 'jquery.min.js';
document.head.appendChild(imported);

countPos = 0;

// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function() {
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event) {
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if (countPos >= 9) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position " + countPos);
        $('#position_fields').append(
            '<div id="position' + countPos + '"> \
            <p>Year: <input type="text" name="year' + countPos + '" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position' + countPos + '\').remove();return false;"></p> \
            <textarea name="desc' + countPos + '" rows="8" cols="80"></textarea>\
            </div>');
    });
});