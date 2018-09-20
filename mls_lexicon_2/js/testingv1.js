var headAttributes;
var original_content;
var nonEditId;
var prevIdPrefix = 'row-';
var prevId;
var isFirst = true;
function editWordRow(ids) {
    //alert("Hello " + ids + "!");
    nonEditId = "row-" + ids;
    /*var editId = "row-" + ids + "-edit";
     //alert(nonEditId + "--" + editId);
     document.getElementById(editId).style.display = "block";
     document.getElementById(nonEditId).style.display = "none";
     */
    if (ids != prevId) {
        if (isFirst) {
            isFirst = false;
            original_content = document.getElementById(nonEditId).innerHTML;
            var word_code = document.getElementById("row-" + ids + "-contains-code").innerHTML;
            prevId = ids;
            document.getElementById(nonEditId).innerHTML =
                    '<td colspan="3">'
                    + '<b>Word Code: ' + word_code + '</b><br><br>'
                    + 'Text: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + 'Phrase: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + 'Level: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + '<input class="button-secondary" type="button" id="' + ids + '" value="Cancel" onclick="cancelMods(this.id)" />'
                    + '   <input class="button-primary" type="button" id="' + ids + '" value="Update" onclick="applyMods(this.id)" />'
                    + '</td>'
                    + '<td colspan="2">'
                    + 'Word Code: ' + word_code + '<br><br>'
                    + 'Text: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + '</td>'
                    + '<td colspan="2">'
                    + 'Word Code: ' + word_code + '<br><br>'
                    + 'Text: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + '</td>'
                    + '</tr>';
        } else if (isFirst == false) {
            var prevIdFull = prevIdPrefix + prevId;
            document.getElementById(prevIdFull).innerHTML = original_content;
            original_content = document.getElementById(nonEditId).innerHTML;

            var word_code = document.getElementById("row-" + ids + "-contains-code").innerHTML;
            prevId = ids;
            document.getElementById(nonEditId).innerHTML =
                    '<td colspan="3">'
                    + '<b>Word Code: ' + word_code + '</b><br><br>'
                    + 'Text: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + 'Phrase: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + 'Level: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + '<input class="button-secondary" type="button" id="' + ids + '" value="Cancel" onclick="cancelMods(this.id)" />'
                    + '   <input class="button-primary" type="button" id="' + ids + '" value="Update" onclick="applyMods(this.id)" />'
                    + '</td>'
                    + '<td colspan="2">'
                    + 'Word Code: ' + word_code + '<br><br>'
                    + 'Text: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + '</td>'
                    + '<td colspan="2">'
                    + 'Word Code: ' + word_code + '<br><br>'
                    + 'Text: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                    + '</td>'
                    + '</tr>';
        }
    } else if (ids == prevId) {
        original_content = document.getElementById(nonEditId).innerHTML;
        var word_code = document.getElementById("row-" + ids + "-contains-code").innerHTML;
        prevId = ids;
        document.getElementById(nonEditId).innerHTML =
                '<td colspan="3">'
                + '<b>Word Code: ' + word_code + '</b><br><br>'
                + 'Text: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                + 'Phrase: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                + 'Level: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                + '<input class="button-secondary" type="button" id="' + ids + '" value="Cancel" onclick="cancelMods(this.id)" />'
                + '   <input class="button-primary" type="button" id="' + ids + '" value="Update" onclick="applyMods(this.id)" />'
                + '</td>'
                + '<td colspan="2">'
                + 'Word Code: ' + word_code + '<br><br>'
                + 'Text: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                + '</td>'
                + '<td colspan="2">'
                + 'Word Code: ' + word_code + '<br><br>'
                + 'Text: ' + '<input type="text" value="' + word_code + '"/> <br><br>'
                + '</td>'
                + '</tr>';
    }
}

function applyMods(theId) {
    //alert("Applying..." + theId);
}

function cancelMods(theId) {
    /*//alert("Canceling..." + theId);
     var nonEditId = "row-" + theId;
     var editId = "row-" + theId + "-edit";
     //alert(nonEditId + "--" + editId);
     document.getElementById(nonEditId).style.display = "";
     document.getElementById(editId).style.display = "none";
     */

    document.getElementById(nonEditId).innerHTML = original_content;
}

window.onload = function () {
    //var classToAppendButtons = document.getElementById("custom_alignleft_bulkactions_lexicon").innerHTML;

    //classToAppendButtons += '<input type="button" id="addWordId" onclick="addWord()" class="button-primary" value="Add Word">'
    //    + '    <input type="button" id="loadCsvId" onclick="loadCsv()" class="button-primary" value="Import Language File">'
    //   + '    <input type="button" id="exportCsvId" onclick="exportCsv()" class="button-primary" value="Export CSV">';


    //document.getElementById("custom_alignleft_bulkactions_lexicon").innerHTML = classToAppendButtons;

    document.getElementById("lexicon-add-word").style.display = "none";
    document.getElementById("lexicon-import-file").style.display = "none";
}

function addWord() {
    document.getElementById("lexicon-table-content").style.display = "none";
    document.getElementById("lexicon-add-word").style.display = "block";
    document.getElementById("lexicon-import-file").style.display = "none";
}

function backToLexicon() {
    document.getElementById("lexicon-table-content").style.display = "block";
    document.getElementById("lexicon-add-word").style.display = "none";
    document.getElementById("lexicon-import-file").style.display = "none";
}

function import_lexicon_lang_CSV() {
    document.getElementById("lexicon-table-content").style.display = "none";
    document.getElementById("lexicon-import-file").style.display = "block";
    document.getElementById("lexicon-add-word").style.display = "none";
}

/* jQuery(document).ready(function () {
    jQuery('#lexicon-form-full').submit(function (e) {
        e.preventDefault();
        // Variables
        var files = jQuery("#lexicon_file_to_upload")[0].files;
        var nonce = jQuery("#admin_data_import").val();
        var sendingPage = jQuery("#sendingPage").val();
        var ajaxurl = jQuery("#varAjUrl").val();
        var files_data = new FormData();
        files_data.append('action', 'importLangCSV');
        files_data.append('act', 'sp_import_lexicon_lang_CSV');
        files_data.append('nonce', nonce);
        alert("Read everything");
        jQuery.each(files, function (key, value)
        {
            files_data.append(key, value);
        });

        jQuery.ajax({
            type: 'POST',
            dataType: "json",
            url: ajaxurl,
            data: files_data,
            cache: false,
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function (response) {
                if (response.type === 'success') {
                    window.location = '?page=' + sendingPage + '&action=importLangCSV&_wpnonce=' + nonce;
                } else {
                    var err_msg = response.msg;
                    alert(err_msg);

                }
            }
        });

    });

    function progress(e) {
        alert("Hey");
    }
}); */
