var headAttributes;
var original_content;
var nonEditId;
var prevIdPrefix = 'row-';
var prevId;
var isFirst = true;
var parseFullString = [];
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
    
}

function addWord() {
    document.getElementById("lexicon-table-content").style.display = "none";
    document.getElementById("lexicon-add-word").style.display = "block";
    
}

function backToLexicon() {
    document.getElementById("lexicon-table-content").style.display = "block";
    document.getElementById("lexicon-add-word").style.display = "none";
    
}



function export_data_to_CSV(iterNum) {

    var fullString = document.getElementById("data_to_export").innerHTML;

    for (var i = 0; i <= iterNum - 1; i++) {
        var partString = fullString.split("<br/>", 1).toString();
        //alert(partString);
        var partStringLength = partString.length;
        var fullStringTemp = fullString.slice(partStringLength);
        fullString = fullStringTemp;
        parseFullString[i] = partString;
    }
    var finalText = parseFullString.toString();

    var searchNum = finalText.search("<br>");

    while (searchNum != -1) {
        var final = finalText.replace("<br>", "\n");
        finalText = final;
        searchNum = finalText.search("<br>");
        /*if (searchNum == -1) {
            var searchNum2 = finalText.search(",,");
            while (searchNum2 != -1) {
                final = finalText.replace(",,", "");
                finalText = final;
                searchNum2 = finalText.search(",,");
            }
        }*/
    }

    alert(parseFullString.length);
    //alert(theContent);
    var filename = "mydata.csv";
    var csvFile = finalText.slice(0, -iterNum+1);
    var blob = new Blob([csvFile], {type: 'text/csv;charset=utf-8;'});
    if (navigator.msSaveBlob) { // IE 10+
        navigator.msSaveBlob(blob, filename);
    } else {
        var link = document.createElement("a");
        if (link.download !== undefined) { // feature detection
            // Browsers that support HTML5 download attribute
            var url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
}

function showMoreLanguages(response) {
    var theLessLangData = document.getElementById("lessOpt").value;
    var theMoreLangData = document.getElementById("moreOpt").value;
    
    if(response === true){
        document.getElementById("theLanguagesOptions").innerHTML = theMoreLangData;
        document.getElementById("showLanguagesLink").innerHTML = '<a href="#" onclick="showMoreLanguages(false)">Need less languages?</a>';
    }else if(response === false){
        document.getElementById("theLanguagesOptions").innerHTML = theLessLangData;
        document.getElementById("showLanguagesLink").innerHTML = '<a href="#" onclick="showMoreLanguages(true)">Need more languages?</a>';
    }
    
    //document.getElementById("sectionImportLang").innerHTML;
}
