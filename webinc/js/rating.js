var sent = "";

function loadStars() {
    sent = false;
    if(document.getElementById('star5')) {
        s5 = document.getElementById('star5').src;
        s4 = document.getElementById('star4').src;
        s3 = document.getElementById('star3').src;
        s2 = document.getElementById('star2').src;
        s1 = document.getElementById('star1').src;
    }
}

function ratingClick(webroot, starNr, path) {
    
    sent = "true";
    
    var neues_bild = new Image();
    if(document.star5.src == webroot + "files/images/star/st_pe_off_r_lg.gif" && starNr == 5) {
        neues_bild.src = webroot + "files/images/star/st_pe_on_r_lg.gif";
        document.star5.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
        document.star4.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
        document.star3.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
        document.star2.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
        document.star1.src = neues_bild.src;
    }
    if(document.star4.src == webroot + "files/images/star/st_pe_off_m_lg.gif" && starNr == 4) {
        neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
        document.star4.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
        document.star3.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
        document.star2.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
        document.star1.src = neues_bild.src;
    }
    
    if(document.star3.src == webroot + "files/images/star/st_pe_off_m_lg.gif" && starNr == 3) {
        neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
        document.star3.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
        document.star2.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
        document.star1.src = neues_bild.src;
    }
    
    if(document.star2.src == webroot + "files/images/star/st_pe_off_m_lg.gif" && starNr == 2) {
        neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
        document.star2.src = neues_bild.src;
        neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
        document.star1.src = neues_bild.src;
    }
    
    if(document.star1.src == webroot + "files/images/star/st_pe_off_l_lg.gif" && starNr == 1) {
        neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
        document.star1.src = neues_bild.src;
    }
    
    
    
    new ajax (webroot + 'inc/bx/php/rating.php', {
    postBody: starNr+"-"+path,
    method: 'post',
    onComplete: ratingDone
    });
}

function ratingHover(webroot, starNr) {
    
    if(sent == 'true') {
        
    } else {
        var neues_bild = new Image();
        
        if(starNr == 5) {
            neues_bild.src = webroot + "files/images/star/st_pe_on_r_lg.gif";
            document.star5.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
            document.star4.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
            document.star3.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
            document.star2.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
        if(starNr == 4) {
            neues_bild.src = webroot + "files/images/star/st_pe_off_r_lg.gif";
            document.star5.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
            document.star4.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
            document.star3.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
            document.star2.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
        
        if(starNr == 3) {
            neues_bild.src = webroot + "files/images/star/st_pe_off_r_lg.gif";
            document.star5.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star4.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
            document.star3.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
            document.star2.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
        
        if(starNr == 2) {
            neues_bild.src = webroot + "files/images/star/st_pe_off_r_lg.gif";
            document.star5.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star4.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star3.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_m_lg.gif";
            document.star2.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
        
        if(starNr == 1) {
            neues_bild.src = webroot + "files/images/star/st_pe_off_r_lg.gif";
            document.star5.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star4.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star3.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star2.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_on_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
    }
}

function ratingOut(webroot, starNr) {
    
    
    if(sent == 'true') {
        
    } else {
        var neues_bild = new Image();
        
        if(document.star5.src == webroot + "files/images/star/st_pe_on_r_lg.gif" && starNr == 5) {
            neues_bild.src = webroot + "files/images/star/st_pe_off_r_lg.gif";
            document.star5.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star4.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star3.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star2.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
        
        if(document.star4.src == webroot + "files/images/star/st_pe_on_m_lg.gif" && starNr == 4) {
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star4.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star3.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star2.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
        
        if(document.star3.src == webroot + "files/images/star/st_pe_on_m_lg.gif" && starNr == 3) {
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star3.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star2.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
        
        if(document.star2.src == webroot + "files/images/star/st_pe_on_m_lg.gif" && starNr == 2) {
            neues_bild.src = webroot + "files/images/star/st_pe_off_m_lg.gif";
            document.star2.src = neues_bild.src;
            neues_bild.src = webroot + "files/images/star/st_pe_off_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
        
        if(document.star1.src == webroot + "files/images/star/st_pe_on_l_lg.gif" && starNr == 1) {
            neues_bild.src = webroot + "files/images/star/st_pe_off_l_lg.gif";
            document.star1.src = neues_bild.src;
        }
        
        if(s5 || s4 || s3 || s2 || s1) {
            document.star5.src = s5;
            document.star4.src = s4;
            document.star3.src = s3;
            document.star2.src = s2;
            document.star1.src = s1;
        }
    }
}


function ratingDone(request) {

}