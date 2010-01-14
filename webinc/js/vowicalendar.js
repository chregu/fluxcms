var currentTime = new Date();
var month = currentTime.getMonth() + 1;
var year = currentTime.getFullYear();
var oldMonth;
var oldYear;

var oldDate;
var currentDate;

if( (month - 6) < 1)
{
    difference = month - 7;
    oldYear = year - 1;
    oldMonth = 12 + difference;
}
else
{
    oldMonth = month - 6;
    oldYear = year - 1;
}

if(month < 10)
    month = "0"+month;

if(oldMonth < 10)
    oldMonth = "0"+oldMonth;

oldDate = oldYear+"-"+oldMonth+"-01";
currentDate = year+"-"+month+"-01"

if(document.getElementById('lowerRange'))
    document.getElementById('lowerRange').value = oldDate;
if(document.getElementById('upperRange'))
    document.getElementById('upperRange').value = currentDate;