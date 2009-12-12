if(document.addEventListener)
{
    document.addEventListener("mousemove",mtrack,true);
}
else
{
    if (document.layers) document.captureEvents(Event.MOUSEMOVE);
    document.onmousemove=mtrack;

}
var mouseX =0;
var mouseY = 0;
var RecordID = 0;
var ParentID = 0;
var TableName = 0;
var ViewPath = 0;
var FieldTitle = "";
var BX_loaded = new Array();

/* check if we have mozilla here... */
var isMoz	 =  (navigator.userAgent.indexOf("Gecko") > 0) ? 1 : 0;

if (isMoz)
{
   /*document.writeln('<script language="JavaScript" src="/admin/power/js/RangePatch.js"/>');*/
}

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
    if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
            document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
    else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);


function MM_findObj(n, d) { //v4.0
    var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
        d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
    if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
    for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
    if(!x && document.getElementById) x=document.getElementById(n); return x;
}
function showMoveLayers() { //v3.0
    var i,p,v,obj,args=showMoveLayers.arguments;

    for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) {
            v=args[i+2];
            DivWidth = (obj.offsetWidth) ? obj.offsetWidth : obj.clip.width;
            FrameWidth = (window.innerWidth) ? window.innerWidth : document.body.clientWidth ;

            if (window.pageXOffset >= 0)
            {
                LeftScroll 	=  window.pageXOffset;
            }
            else
            {
                LeftScroll = document.body.scrollLeft;
                mouseX += document.body.scrollLeft;
                mouseY += document.body.scrollTop;
            }
            if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v='hide')?'hidden':v; }
            if (mouseX + DivWidth > FrameWidth +  LeftScroll)
            {
                obj.left= FrameWidth - DivWidth +  LeftScroll+"px";
            }
            else
            {
                obj.left = mouseX+"px" ;
            }

            obj.top = mouseY+"px";

            obj.visibility=v;

        }


}


function mtrack(e) {

    if (e)
    {
        mouseX = e.pageX;
        mouseY = e.pageY;
    }
    else
    {
        mouseX = window.event.x;
        mouseY = window.event.y;
    }
    //window.status = mouseX;
}

function MM_showHideLayers() { //v3.0
    var i,p,v,obj,args=MM_showHideLayers.arguments;
    for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
            if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v='hide')?'hidden':v; }
            obj.visibility=v; }
}
function showPopUp (name,id,parentid,view,title) {
    //MM_findObj("Delete").href = "blabla";
    //	window.status = "bl" + MM_findObj("Layer1");

    RecordID = id;
    ParentID = parseInt(parentid);
    ViewPath = view;
    MM_showHideLayers('PopUp'+TableName,'','hide');
    showMoveLayers('PopUp'+name,'','show');

    TableName = name;
    FieldTitle = title;
}

function addBookmark () {
    with (parent.header.document.forms['bookmarks'])
    {
        re = /%20/gi;
        NewOption = new Option(TableName+": "+FieldTitle.replace(re," "), ViewPath+"|"+TableName+"|"+RecordID);


        selectBookmarks.options[selectBookmarks.options.length]=NewOption;

        selectBookmarks.selectedIndex = selectBookmarks.options.length-1;

    }
    showMoveLayers('PopUp'+TableName,'','hide');
}

function edit()
{

    parent.edit.location.href="../../form/"+TableName+"/?ID="+RecordID;
    showMoveLayers('PopUp'+TableName,'','hide');

}
function editwysiwyg(inSameWindow)
{

/* uncomment this until we have a better solution...
    if (inSameWindow == true)
    {

        window.top.location.href="/admin/wysiwyg/?"+ViewPath;
    }
    else {
        BX_window_viewer = window.open("/admin/wysiwyg/?"+ViewPath,"viewer");
        try{BX_window_viewer.focus();} catch(e) {};
    }
    MM_showHideLayers('PopUp'+TableName,'','hide');    MM_showHideLayers('PopUp'+TableName,'','hide');
*/
alert('The Wysiwyg Editor is not integrated in the admin interface right now. \nGo back to the website and you should see "Edit this article"');

}

function del()
{
    parent.edit.location.href="../../form/"+TableName+"/?delete=1&update=1&ID="+RecordID;
    MM_showHideLayers('PopUp'+TableName,'','hide');

}

function view()
{

    BX_window_viewer = window.open("/?"+ViewPath,"viewer");
    try{BX_window_viewer.focus();} catch(e) {};
    MM_showHideLayers('PopUp'+TableName,'','hide');
}


function add(table,id)
{
    if (id == 'this')
    {
        id = RecordID;
    }

    parent.edit.location.href="../../form/"+table+"/?default[parentid]="+id+"&new=1";
    MM_showHideLayers('PopUp'+TableName,'','hide');

}

function OnLoad()
{
    if (isMoz)
    {
        var reloader = document.getElementById('reload');

        reloader.style.left = window.innerWidth - reloader.offsetWidth-20+ "px";
        reloader.style.top = "0px";
        reloader.style.visibility = "visible";
    }

}

function openCloseDiv( div, open)
{
    if (!isMoz)
    {
        return true;
    }
    la = document.getElementById("Div_S"+div);
    //	la.style.display="none";
    pfeil = document.getElementById("Pfeil_S"+div);
    if (BX_loaded[div] != true && div.indexOf("_D") == -1 && pfeil.src.indexOf("closed.gif") > 0)
    {
        xml = document.implementation.createDocument("http://www.w3.org/TR/REC-html40","",null);
        xml.onload = divloaded;
        BX_loaded[div] = true;
        /* no good this way, either make configurable or do it better :) */
        xml.load("./index.php/section.xml?Section="+div);

    }

    if (pfeil.src.indexOf("closed.gif") > 0)
    {
        pfeil.src = "../../img/open.gif";
        la.style.display="block";
    }
    else
    {
        pfeil.src = "../../img/closed.gif";
        la.style.display="none";
    }
    return false;
}


function divloaded(bla)
{

    var xml = bla.currentTarget;


    var repl = document.getElementById(xml.childNodes[2].getAttribute("id"));

    var r = repl.ownerDocument.createRange();
    r.setStartBefore(repl);

    var df = r.createContextualFragment(xml.childNodes[2].xml);
    repl = repl.parentNode.replaceChild(df, repl);

    repl.style.display="block";

    //	alert(bla.currentTarget.childNodes[1].getAttribute("id"));

}

function BX_debug(object)
{
    var win = window.open("","debug");
    for (b in object)
    {

        bla += b;
        try {

            bla +=  ": "+object.eval(b) ;
        }
        catch(e)
        {
            bla += ": NOT EVALED";
        };
        bla += "\n";
    }
    win.document.innerHTML = "";

    win.document.writeln("<pre>");
    win.document.writeln(bla);
    win.document.writeln("<hr>");
}

