<?php


include_once("../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../..");

$tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
        /*
        $query = "select comment_author_email, comment_posts_id from ".$tablePrefix
        ."blogcomments where comment_notification_hash = '".$_GET['id']."'";
        print "<pre/>";
        $res = $GLOBALS['POOL']->db->query($query);
        $re = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        $queryupdate = "update ".$tablePrefix."blogcomments set comment_notification = '0' "
        ."where comment_author_email = '".$re['comment_author_email']."' and comment_posts_id = '".$re['comment_posts_id']."'";
        $var=$GLOBALS['POOL']->db->query($queryupdate);
        print "<p align='center'><b>Notice</b></p>";
        print "<p align='center'>You will not longer receive mails for this post.</p>";
        */

define('PREFIX',$tablePrefix);


define('TOP',745);
define('LEFT',58);
define('RIGHT',552);

$searchpath = "pdffiles";


$text = utf8_decode("pwoeu ropwequoihroijgoefio hgow iüpqwu oij wreoi uwo" .
		"irujfrwoi gfoiru u9q7 r lllll ll ll l lll l ll l l l ll ll l ll ll ll ll ll 5983" .
		"   47q9u aoshdk fdahsdkjhf iuwqz8z rwsdghfui zas89 fhcads kj" .
		"fhasdjk sbdjkf hioas" .
		"d fsüpa dipfasopfjopa sdoif sadop fsaopf sdopf jpoweai" .
		" rf903 qiur osfo sapojfodsj fjsaopjf" .
		"sad8z idahf hgifdh gj" .
		"fhasdjk sbdjkf hioas" .
		"d fsüp a dipfaso pfjopa sdoif sadop fsaopf sdopf" .
		" jpowe ai rf903q iur osfo sapojfodsj fjsaopjf" .
		"sad8z idahf hg ifdh gj" .
		"fhasdjk sbdjkf hioas" .
		"d fsü pa dipfas opfjopa sdoif sadop fsaopf sdopf " .
		"jpowea i rf903qiur osfo sapojfodsj fjsaopjf" .
		"sad8zidahf hgifdh gj" .	
		"fhasdjk sbdjkf hioas" .
		"d fsüpadipf  asgdfsg dsfo pfjopa sdoif sadosd fg sdfp fsaopf sdopf jpoweai" .
		" rf903qiur osfgds o sapojfodsj fjsaopjf" .
		"sad8zi dahf hgifdh gj" .
		"fhasdjk sbdjkf hioas" .
		"d fsüpad ipf asopfj opa sdoifg dgsdfgsdg sadop fsaopf sdopf" .
		" jpoweai rf903qiur osfod sgsd f sapojfodsj fjsaopjf" .
		"sad8zidahf hgifdh gj" .
		"fhasdjk sbdjkf hioas" .
		"d fsüpad ip fasopfj opa sdoif sadop fs aopf sdopf " .
		"jpoweai rf903qiur osfo sapojfodsj fjsaopjf" .
		"sad8zidahf hgifdh gj" .
		"dsfkh gjkdsfhkjg hsjfhgh dsjhgsfh gsjd");

try {
    $p = new PDFlib();

    /* Suchpfad */
    $p->set_parameter("SearchPath", $searchpath);    
    /* Input pdfs sind 1.6 */
    $p->set_parameter("compatibility", "1.6");
    /* lizenz */
    $p->set_parameter("license", "X600605-009100-4FB312-055097");
    /* This line is required to avoid problems on Japanese systems */
    $p->set_parameter("hypertextencoding", "winansi");

    $p->set_info("Creator", "m20areal.pdf");
    $p->set_info("Author", "Bitflux GmbH");
    $p->set_info("Title", "M20 Areal");
    
    
    /* neues file */
    if ($p->begin_document("", "") == 0) {
		die("Error: " . $p->get_errmsg());
    }



	/**
	 * seite 1
	 */
	m20_new_page_from_tpl($p,"m20_seiten_01-02.pdf");
	$p->end_page_ext("");
	
	/**
	 * seite 2
	 */
	m20_new_page_from_tpl($p,"m20_seiten_01-02.pdf",2);
	$p->end_page_ext("");

	/**
	 * seite 3
	 */
	m20_new_page_from_tpl($p,"m20_seite_leer.pdf");	
	$y = m20_title($p,'Areal und Umgebung',TOP);
	$p->end_page_ext("");
		
	/**
	 * seite 4
	 */
	m20_new_page_from_tpl($p,"m20_seite_leer.pdf");	
	$y = m20_title($p,'Verkehrslage',TOP);
	$p->end_page_ext("");
		
	/**
	 * seite 5
	 */
	m20_new_page_from_tpl($p,"m20_seite_leer.pdf");	
	$y = m20_title($p,utf8_decode('Gewerbeflächen'),TOP);
	//tabelle
	m20_objekte_typ($p, 21);
	$p->end_page_ext("");
		
	/**
	 * seite 6
	 */
	m20_new_page_from_tpl($p,"m20_seite_leer.pdf");	
	$y = m20_title($p,utf8_decode('Büro - und Atelierflächen'),TOP);	
	//tabelle
	m20_objekte_typ($p, 20);
	$p->end_page_ext("");
		
	/**
	 * seite 7
	 */
	m20_new_page_from_tpl($p,"m20_seite_leer.pdf");	
	$y = m20_title($p,utf8_decode('Lagerflächen'),TOP);			
	//tabelle
	m20_objekte_typ($p, 22);
	
	$textflow =	$p->create_textflow($text,"encoding=winansi fontname=l047013t fontsize=9");
			
	//$result = $p->fit_textflow($textflow, 55, 30, 500, 800, "");
	//print_r($result);
	
	
	$p->delete_textflow($textflow);	
	$p->end_page_ext("");


	/**
	 * dokument abschliessen
	 */
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

	/**
	 * und raus damit ;)
	 */
    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=m20-areal.pdf");
    print $buf;

}
catch (PDFlibException $e) {
    die("PDFlib exception occurred in businesscard sample:\n" .
	"[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
	$e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}


function m20_line($p, $y, $x1 ,$x2){
    /* linie */
    $p->save();
    $p->setlinewidth(0.75);
    /* abrunden */
    $p->setlinecap(1);
    $p->setdash(0,2);
    $p->moveto($x1,$y);
    $p->lineto($x2,$y);
    $p->stroke();
    $p->restore();	
}

function m20_title($p, $text, $y){
	$font = $p->load_font("l047013t","winansi","embedding");
	$p->setfont($font, 20);
	$p->fit_textline($text,LEFT,$y,'');
	//linie
	$y = $y -12;
	m20_line($p,$y,LEFT,RIGHT);	
	return $y -6;
}


function m20_new_page_from_tpl($p, $file, $page = 1){	
    $tpl = $p->open_pdi($file, "", 0);    
    if ($tpl == 0){
		die ("Error: " . $p->get_errmsg());
    }
    $page = $p->open_pdi_page($tpl, $page, "");
    if ($page == 0){
		die ("Error: " . $p->get_errmsg());
    }
    $p->begin_page_ext(20, 20, "");		/* neue seite */
    $p->fit_pdi_page($page, 0, 0, "adjustpage");	   
    $p->close_pdi_page($page);
    $p->close_pdi($tpl);	
}

function m20_objekte_typ($p, $typ){





	
	$query = "SELECT 
	fluxcms_objects_displayname.name AS stock,
	fluxcms_object_nutzungstyp.name AS nutzung,
	SUM( fluxcms_parcels.area ) AS qm, 
	fluxcms_objects.object_pricem AS preis, 
	fluxcms_objects.object_extracharges AS nk,
	fluxcms_renters.name AS mieter
	FROM fluxcms_objects, fluxcms_renters, fluxcms_object_nutzungstyp,
	fluxcms_objects_displayname, fluxcms_objects2parcels, fluxcms_parcels
	WHERE fluxcms_renters.id = fluxcms_objects.renters_id
	AND fluxcms_object_nutzungstyp.id = fluxcms_objects.designation_id
	AND fluxcms_objects_displayname.id = fluxcms_objects.displayname_id
	AND fluxcms_objects2parcels.objects_id = fluxcms_objects.id
	AND fluxcms_objects2parcels.parcels_id = fluxcms_parcels.id
	AND fluxcms_object_nutzungstyp.id = $typ
	AND fluxcms_objects.renters_id = 14
	GROUP BY fluxcms_objects2parcels.objects_id
	ORDER BY fluxcms_objects_displayname.rang
	";

	$y = 262;
	$leading = 13;


	//optionen						//444!!!!!
	$optlist = 	"ruler {100 204 322 424} " .
				"tabalignment { left left left left } " .
				"hortabmethod=ruler encoding=winansi fontname=l047013t fontsize=9";

	//linie oben
	m20_line($p,$y,LEFT,RIGHT);
	
	//titel
	$text = utf8_decode("Geschoss\tFläche\tMietpreis\tNebenkosten\tMieter");
	$textflow = $p->create_textflow($text, $optlist);
 	$p->fit_textflow($textflow, LEFT, $y-$leading, RIGHT, $y, "");
	$p->delete_textflow($textflow); 
		
    $y = $y - 3 * $leading;	
    m20_line($p,$y,LEFT,RIGHT);
    	
    $res = $GLOBALS['POOL']->db->query($query);
    while( $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC) ){
  
  		$nk 	= ($row['nk'] == 0)?'Auf Anfrage':sprintf("%d.-/m2 p.a",$row['nk']);
		$preis 	= ($row['preis'] == 0)?'Auf Anfrage':sprintf("%d.-/m2 p.a",$row['preis']);
		
  		$text = sprintf("%s\t%d m2\t%s\t%s\t%s",
		$row['stock'],
		$row['qm'],
		$preis,
		$nk,
		$row['mieter']);
		

	 	$textflow = $p->create_textflow($text, $optlist);
	 	$p->fit_textflow($textflow, LEFT, $y-$leading, RIGHT, $y, "");
		$p->delete_textflow($textflow); 
	   	
    	$y = $y - $leading;	
    	m20_line($p,$y,LEFT,RIGHT);
    }
}


$p = 0;
?>
