/*
options array expected in the following format:
options[indexes][content] = string
options[indexes][value] = string
options[indexes][selected] = boolean
*/
function filterChild(childid,selectedindex,optionarray) {
    select = document.getElementById(childid);
    //truncate any visible options
    select.options.length = 0;
    //selected index exists
    if (typeof optionarray['o' + selectedindex] != 'undefined' ) {
        var optionvals = optionarray['o' + selectedindex];
        //add all options with properties as given by options JSON
        for (var i in optionvals) {
            select.options.add(new Option(optionvals[i].text,optionvals[i].value,optionvals[i].selected));
        }
    }
}