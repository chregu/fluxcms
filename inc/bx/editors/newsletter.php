<?php

/**
* Admin view of the newsletter collection
*/
class bx_editors_newsletter extends bx_editor implements bxIeditor {    
    
    protected $callbackDate = "00000000000000";
    
    public function getDisplayName() {
        return "Newsletter";
    }
    
    public function getPipelineParametersById($path, $id) {
        return array('pipelineName'=>'newsletter');
    }
    
    /**
    * Gathers a list of subscriptors for the newsletter the user wishes to send. 
    */
    public function handlePOST($path, $id, $data) {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        
        $perm = bx_permm::getInstance();
        $parts = bx_collections::getCollectionUriAndFileParts($id);
        $colluri = $parts['colluri'];
        // Send event
        if($parts['name'] == "send/.")
        {
            if (!$perm->isAllowed($colluri,array('newsletter-back-send'))) {
                throw new BxPageNotAllowedException();
            }    
            
            $i18n = $GLOBALS['POOL']->i18nadmin;
            if($data["_all"] == $i18n->getText("Preview")) {
                if($data['groups'] === null) {
                    $_POST["nogroups"] = true;
                }
                
                $_POST["preview"] = true;
                return;
            }
            else {
                $_POST["sent"] = true;
            }
            
            // check if this is a testmail
            
            $groupIds = implode(",", $data["groups"]);
            $query = "select COUNT(*) from ".$prefix."newsletter_groups where id in (".$groupIds.") and test=1";
            $testMode = $GLOBALS['POOL']->db->queryOne($query); 
            
            list($htmlname, $htmllanguage, $htmltype) = explode(".", $data["htmlfile"]);
            list($textname, $textlanguage, $texttype) = explode(".", $data["textfile"]);
            
            // archive the file
            $newHtmlFile = "";
            $newTextFile = "";
            $year = date("Y") . "/";
            
            // replace whitespaces to get a clean url
            $clearSubject = str_replace(" ", "-", $data['subject']);
            if(!empty($data["htmlfile"])) {
                if($testMode == 0) {
                    $newHtmlFile = $year.date("Ymd-").$clearSubject.".".$htmllanguage.".xhtml";
                    rename("data".$colluri."drafts/".$data["htmlfile"], "data".$colluri."archive/".$newHtmlFile);
                    $this->removeNewsletterProperties($colluri."drafts/".$data["htmlfile"]);
                    $this->addNewsletterProperties($colluri."archive/".$newHtmlFile, $data["subject"],$colluri);
                    
                    if(isset($data['publish'])) {
                        // make the newsletter visible to the users
                        bx_resourcemanager::setProperty($colluri."archive/".$newHtmlFile, "display-order", "99");
                    }
                } else {
                    $newHtmlFile = "drafts/".$data["htmlfile"];
                }
            }
            if(!empty($data["textfile"]) and $data["htmlfile"] != $data["textfile"]) {
                if($testMode == 0) {    
                    $newTextFile = $year.date("Ymd-").$clearSubject."-txt.".$textlanguage.".xhtml";
                    rename("data".$colluri."drafts/".$data["textfile"], "data".$colluri."archive/".$newTextFile);
                    $this->removeNewsletterProperties($colluri."drafts/".$data["textfile"]);
                    $this->addNewsletterProperties($colluri."archive/".$newTextFile, $data["subject"],$colluri);
                } else {
                    $newTextFile = "drafts/".$data["textfile"];
                }
            }        
            
            $classname = $this->getConfigParameter($id, "sendclass");    
            
            // Save all the information we received about the newsletter in the database for archiving purposes
            // FIXME... add also colluri 
            $query =     "INSERT INTO ".$prefix."newsletter_drafts (`from`,`subject`,`htmlfile`, `textfile`, `colluri`,`attachment`, `class`, `mailserver`, `embed`, `baseurl`)
            VALUES (
            '".$data['from']."', '".$data['subject']."', '".$newHtmlFile."', '".$newTextFile."','".$colluri."', '".$data['attachment']."', '".$classname."', '".$data['mailserver']."', '".(isset($data["embed"])?1:0)."', '".BX_WEBROOT."');";
            $GLOBALS['POOL']->dbwrite->exec($query);
            
            $draftId = $GLOBALS['POOL']->dbwrite->lastInsertID($prefix."newsletter_drafts", "id");
            
            $draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);    
            
            foreach($data['groups'] as $grp)
            {
                $query =     "INSERT INTO ".$prefix."newsletter_drafts2groups (`fk_draft`,`fk_group`)
                VALUES (
                '".$draftId."', '".$grp."');";
                $GLOBALS['POOL']->dbwrite->exec($query);
            }
            
            // Get a unique list of subscriptors
            $query = "SELECT DISTINCT u.* FROM ".$prefix."newsletter_users2groups u2g, ".$prefix."newsletter_drafts2groups d2g, ".$prefix."newsletter_users u WHERE d2g.fk_draft = ".$draftId." AND u.id=u2g.fk_user AND u2g.fk_group = d2g.fk_group AND u.status=1";
            $users = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
            
            foreach($users as $recv) {
                
                $query =     
                "INSERT INTO ".$prefix."newsletter_cache ( `fk_user` , `fk_draft` , `status` )
                VALUES ('".$recv['id']."', '".$draftId."', '1')";
                
                $GLOBALS['POOL']->dbwrite->exec($query);                
            }
            
            /*
            // get news mailer instance            
            $newsmailer = bx_editors_newsmailer_newsmailer::newsMailerFactory($classname);
            
            // Send it
            $mailoptions = bx_editors_newsmailer_newsmailer::getMailserverOptions($data['mailserver']);
            $newsmailer->sendNewsletter($draft, $users, $mailoptions, isset($data["embed"]));   
            */
        }
        else if($parts['name'] == "users/.")
        {
            if (!$perm->isAllowed($colluri,array('newsletter-back-manage'))) {
                throw new BxPageNotAllowedException();
            }    
            if(!empty($_FILES))
            {
                // get the content of the uploaded file
                $file = utf8_encode(file_get_contents($_FILES["userfile"]["tmp_name"]));
                $this->importUsers($file, $_POST["importgroup"]);
                
            }
        }
        else if($parts['name'] == "feed/.")
        {
            if (!$perm->isAllowed($colluri,array('newsletter-back-feed'))) {
                throw new BxPageNotAllowedException();
            }    
            
            // Generate a newsletter from a RSS feed
            $this->createFromFeed($data,$colluri);    
        }
    }
    
    /**
    * Removes all properties of the given resource
    */
    protected function addNewsletterProperties($path, $display,$colluri) 
    {
        bx_resourcemanager::setProperty($path, "parent-uri", $colluri."archive/".date("Y")."/");
        bx_resourcemanager::setProperty($path, "display-name", $display);
        bx_resourcemanager::setProperty($path, "display-order", "0");
        bx_resourcemanager::setProperty($path, "mimetype", "text/html");
        bx_resourcemanager::setProperty($path, "output-mimetype", "text/html");
    }
    
    /**
    * Removes all properties of the given resource
    */
    protected function removeNewsletterProperties($path) 
    {
        bx_resourcemanager::removeProperty($path, "parent-uri");
        bx_resourcemanager::removeProperty($path, "display-name");
        bx_resourcemanager::removeProperty($path, "display-order");
        bx_resourcemanager::removeProperty($path, "mimetype");
        bx_resourcemanager::removeProperty($path, "output-mimetype");    
    }
    
    /**
    * Collection view handler
    * 
    * @return XML response to be sent back to the user
    */
    public function getEditContentById($id) {
        
        $parts = bx_collections::getCollectionUriAndFileParts($id);
        $colluri = $parts['colluri'];
        $perm = bx_permm::getInstance();    
        
        // Manage view requested
        if($parts['name'] == "manage/")
        {
            if (!$perm->isAllowed($colluri,array('newsletter-back-manage'))) {
                throw new BxPageNotAllowedException();
            }    
            
            return $this->generateManageView($colluri);
        }
        // Send view requested
        else if($parts['name'] == "send/")
        {
            if (!$perm->isAllowed($colluri,array('newsletter-back-send'))) {
                throw new BxPageNotAllowedException();
            }    
            
            if(isset($_POST["preview"]) and !isset($_POST["nogroups"])) {
                return $this->generatePreviewView($colluri);
            }
            return $this->generateSendView($colluri);
        }
        
        // Send view requested
        else if($parts['name'] == "users/")
        {
            if (!$perm->isAllowed($colluri,array('newsletter-back-archive'))) {
                throw new BxPageNotAllowedException();
            }    
            
            return $this->generateUsersView($colluri);
        }
        
        // Send view requested
        else if($parts['name'] == "feed/")
        {
            if (!$perm->isAllowed($colluri,array('newsletter-back-feed'))) {
                throw new BxPageNotAllowedException();
            }    
            
            return $this->generateFeedView($colluri);
        }
    }
    
    /**
    * The send view lets the user enter information about the newsletter to be sent
    */
    protected function generatePreviewView($colluri)
    {
        
        
        $i18n = $GLOBALS['POOL']->i18nadmin;
        $txtFrom = $i18n->getText("From");
        $txtTo = $i18n->getText("To");
        $txtSub = $i18n->getText("Subject");
        $txtAttachment = $i18n->getText("Add Attachment");
        $txtEmbed = $i18n->getText("Embed Images");
        $txtPublish = $i18n->getText("Publish Online");
        $txtSend = $i18n->getText("Send");        
        $txtNewsPrev = $i18n->getText("Newsletter Preview");        
        $txtTemps = $i18n->getText("WARNING: The following templates do not match a database field, you should correct them before sending the newsletter:");
        
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_mailservers where id=".$_POST["mailserver"];
        $mailserver = $GLOBALS['POOL']->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC); 
        
        if(!isset($_POST["embed"])) {
            $_POST["embed"] = "off";
        }
        if(!isset($_POST["publish"])) {
            $_POST["publish"] = "off";
        }
        
        $groupIds = implode(",", $_POST["groups"]);
        $query = "select name from ".$prefix."newsletter_groups where id in (".$groupIds.")";
        $groups = implode(",", $GLOBALS['POOL']->db->queryCol($query)); 
        
        $query = "select COUNT(DISTINCT fk_user) from ".$prefix."newsletter_users2groups u2g, ".$prefix."newsletter_users u where fk_group in (".$groupIds.") AND fk_user=u.id AND u.status='1' ORDER BY fk_user";
        $usercount = $GLOBALS['POOL']->db->queryOne($query); 
        
        $htmlsrc = (!empty($_POST["htmlfile"]) ? BX_WEBROOT.'admin/edit'.$colluri.'drafts/'.$_POST["htmlfile"].'?editor=kupu' : "");
        $textsrc = (!empty($_POST["textfile"]) ? BX_WEBROOT.'admin/edit'.$colluri.'drafts/'.$_POST["textfile"].'?editor=oneform' : "");
        
        $htmlContent = @file_get_contents('data'.$colluri.'drafts/'.$_POST["htmlfile"]);
        $textContent = @file_get_contents('data'.$colluri.'drafts/'.$_POST["textfile"]);
        $htmlMissing = $this->checkTemplates($htmlContent);
        $textMissing = $this->checkTemplates($textContent);
        
        $xml = '<newsletter>
        <form name="bx_news_send" action="./" method="post">
        <h3>'.$txtNewsPrev.'</h3>
        <table border="0" id="send">
        <tr><td>'.$txtFrom.':</td><td>'.$_POST["from"].'</td></tr>
        <tr><td style="vertical-align:top">'.$txtTo.':</td><td>'.$groups.' ('.$usercount.' subscribers)'.'</td></tr>
        <tr><td>'.$txtSub.':</td><td>'.$_POST["subject"].'</td></tr>';
        if(!empty($_POST["attachment"])) {
            $xml .= '<tr><td>'.$txtAttachment.':</td><td>'.$_POST["attachment"].'</td></tr>';
        }
        $xml .= '<tr><td>Mail Server:</td><td>'.$mailserver["descr"].' ('.$mailserver["host"].')'.'</td></tr>
        <tr><td>'.$txtEmbed.':</td><td>'.$_POST["embed"].'</td></tr>
        <tr><td>'.$txtPublish.':</td><td>'.$_POST["publish"].'</td></tr>
        <tr>
        <td></td>
        <td><input type="submit" name="bx[plugins][admin_edit][_all]" value="'.$txtSend.'" class="formbutton"/></td>
        </tr>
        </table><br/>';
        
        if(count($htmlMissing) > 0 or count($textMissing) > 0) {
            $xml .= "<p><b>{$txtTemps} ".implode(',',array_merge($textMissing,$htmlMissing))."</b></p>";
        }
        
        $xml .=    '<table>
        <cols>
        <col width="700"/>
        <col width="500"/>
        </cols>
        <tr>';
        if(!empty($_POST["htmlfile"])) {
            $xml .= '   <td><b>'.$_POST["htmlfile"].'</b></td>';
        }
        if(!empty($_POST["textfile"])) {
            $xml .= '    <td><b>'.$_POST["textfile"].'</b></td>';
        }
        $xml .=    '</tr>
        <tr>';
        if(!empty($_POST["htmlfile"])) {
            $xml .= '   <td>
            <iframe src="'.$htmlsrc.'" width="100%" height="500" name="htmlfile"/>
            </td>';
        }
        if(!empty($_POST["textfile"])) {
            $xml .= '    <td>
            <iframe src="'.$textsrc.'" width="100%" height="500" name="textfile"/>
            </td>';
        }
        $xml .=    '</tr>
        </table>
        
        <input type="hidden" name="from" value="'.$_POST["from"].'"/>
        <input type="hidden" name="subject" value="'.$_POST["subject"].'"/>
        <input type="hidden" name="attachment" value="'.$_POST["attachment"].'"/>
        <input type="hidden" name="htmlfile" value="'.$_POST["htmlfile"].'"/>
        <input type="hidden" name="textfile" value="'.$_POST["textfile"].'"/>
        <input type="hidden" name="mailserver" value="'.$_POST["mailserver"].'"/>';
        if($_POST["embed"] == "on") {
            $xml .= '<input type="hidden" name="embed" value="'.$_POST["embed"].'"/>';
        }
        if($_POST["publish"] == "on") {
            $xml .= '<input type="hidden" name="publish" value="'.$_POST["publish"].'"/>';
        }
        foreach($_POST["groups"] as $group) {
            $xml .=    '<input type="hidden" name="groups[]" value="'.$group.'"/>';
        }
        $xml .= '</form>
        </newsletter>';
        
        return domdocument::loadXML($xml);
    }
    
    /**
    * The send view lets the user enter information about the newsletter to be sent
    */
    protected function generateSendView($colluri)
    {
        $i18n = $GLOBALS['POOL']->i18nadmin;
        $txtNews = $i18n->getText("Send Newsletter");
        $txtFrom = $i18n->getText("From");
        $txtTo = $i18n->getText("To");
        $txtSub = $i18n->getText("Subject");
        $txtAttachment = $i18n->getText("Add Attachment");
        $txtEmbed = $i18n->getText("Embed Images");
        $txtPublish = $i18n->getText("Publish Online");
        $txtSending = $i18n->getText("Your newsletter is being sent");
        $txtNoGrp = $i18n->getText("ERROR: Select at least one group");
        $txtPrev = $i18n->getText("Preview");
        
        // show a list of available groups
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_groups";
        $groups = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);    
        $query = "select * from ".$prefix."newsletter_from";
        $senders = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);    
        
        $groupsHTML = '<select name="groups[]" size="'.count($groups).'" multiple="multiple">';
        foreach($groups as $row)
        {
            $groupsHTML .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';    
        }
        $groupsHTML .= '</select>';
        
        $sendersHTML = '<select name="from" size="1">';
        foreach($senders as $row)
        {
            $sendersHTML .= '<option value="'.$row['sender'].'">'.$row['sender'].'</option>';    
        }
        $sendersHTML .= '</select>';
        
        // show a list of newsletter templates created
        $files = $this->getNewsletterFilenames($colluri);
        foreach($files as $file)
        {
            if(strstr($file, "-txt.") !== FALSE) {
                $txtEnum[] = $file;
            }else {
                $htmlEnum[] = $file;
            }
        }
        
        $newsHTML = '<select name="htmlfile" size="1"><option/>';
        foreach($htmlEnum as $entry)
        {
            $newsHTML .= '<option value="'.$entry.'">'.$entry.'</option>';    
        }
        $newsHTML .= '</select>';    
        
        // same but for the text version 
        $newsText = '<select name="textfile" size="1"><option/>';
        foreach($txtEnum as $entry)
        {
            $newsText .= '<option value="'.$entry.'">'.$entry.'</option>';    
        }
        $newsText .= '</select>';    
        
        // get the list of mail servers
        $servers = $this->getMailServers();
        
        $serversHtml = '<select name="mailserver" size="1">';
        foreach($servers as $server)
        {
            $serversHtml .= '<option value="'.$server['id'].'">'.$server['descr'].'</option>';    
        }
        $serversHtml .= '</select>';                
        
        if(isset($_POST["nogroups"])) {
            $msg = "<b>".$txtNoGrp."</b>";    
        }
        else if(isset($_POST["sent"])) {
            $msg = "<b>".$txtSending."</b>";    
        }
        
        $xml = '<newsletter>
        <script type="text/javascript">
        bx_webroot = "'.BX_WEBROOT.'";
        </script>
        <form id="bx_news_send" name="bx_news_send" action="./" method="post">
        <h3>'.$txtNews.'</h3>';
        $xml .= $msg;
        $xml .= '<table border="0" id="send">
        <tr><td>'.$txtFrom.':</td><td>'.$sendersHTML.'</td></tr>
        <tr><td style="vertical-align:top">'.$txtTo.':</td><td>'.$groupsHTML.'</td></tr>
        <tr><td>'.$txtSub.':</td><td><input type="text" name="subject"/></td></tr>
        <tr><td>HTML Body:</td><td>'.$newsHTML.'</td></tr>
        <tr><td>Text Body:</td><td>'.$newsText.'</td></tr>
        <tr><td>'.$txtAttachment.':</td><td><input type="text" id="attachment" name="attachment"/><input type="button" onclick="openFileBrowser(\'attachment\')" value="..."/></td></tr>
        <tr><td>Mail Server:</td><td>'.$serversHtml.'</td></tr>
        <tr><td>'.$txtEmbed.':</td><td><input type="checkbox" name="embed" checked="checked"/></td></tr>
        <tr><td>'.$txtPublish.':</td><td><input type="checkbox" name="publish"/></td></tr>
        <tr>
        <td></td>
        <td><input type="submit" name="bx[plugins][admin_edit][_all]" value="'.$txtPrev.'" class="formbutton"/></td>
        </tr>
        </table>
        </form>
        </newsletter>';
        
        return domdocument::loadXML($xml);        
    }
    
    /**
    * The manage view shows information about the newsletters created and a more detailed view in case they have already been sent
    */
    protected function generateManageView($colluri)
    {       
        $i18n = $GLOBALS['POOL']->i18nadmin;
        $txtArchive = $i18n->getText("Newsletter Archive");
        
        // get information about the newsletters sent
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_drafts";
        $drafts = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);    
        
        // get a list of all current newsletter templates
        $newsletters = $this->getNewsletterFilenames($colluri);
        
        $xml = '<newsletter>
        <h3>'.$txtArchive.'</h3>
        <form name="bx_news_manage" action="./" method="post">
        <table>
        <cols>
        <col width="200"/>
        <col width="200"/>
        <col width="200"/>
        <col width="200"/>
        <col width="200"/>
        <col width="200"/>
        </cols>
        <tr>
        <th class="stdBorder">From</th>
        <th class="stdBorder">To</th>
        <th class="stdBorder">Subject</th>
        <th class="stdBorder">HTML Body</th>
        <th class="stdBorder">Text Body</th>
        <th class="stdBorder">Sent</th>
        </tr>';
        
        // the newsletters sent
        foreach($drafts as $row)
        {
            $query = "select name from ".$prefix."newsletter_drafts2groups, ".$prefix."newsletter_groups WHERE fk_draft='".$row['id']."' AND fk_group=".$prefix."newsletter_groups.id";
            $groups = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);            
            
            $groupstring = "";        
            for($i=0; $i<count($groups); $i++)
            {
                if($i != 0)
                $groupstring .= ", ";
                $groupstring .= $groups[$i]["name"];    
            }
            
            $xml .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td><a href="'.BX_WEBROOT.'admin/edit'.$colluri.'archive/'.$row['htmlfile'].'?editor=kupu">%s</a></td><td><a href="'.BX_WEBROOT.'admin/edit'.$colluri.'archive/'.$row['textfile'].'?editor=oneform">%s</a></td><td>%s</td></tr>', 
            $row['from'], $groupstring, $row['subject'], $row['htmlfile'], $row['textfile'], $row['sent']);
            
            // remove the element if it was already sent from the extra list
            $key = array_search($row['htmlfile'], $newsletters);
            if($key !== null) {
                
                unset($newsletters[$key]);
            }
        } 
        
        $xml .= '</table>
        </form>
        </newsletter>';
        
        return domdocument::loadXML($xml);        
    }
    
    /**
    * The user view shows information about the existing users and lets the admin import a list of new users
    */
    protected function generateUsersView($colluri)
    {
        $i18n = $GLOBALS['POOL']->i18nadmin;
        $txtUserMan = $i18n->getText("User Management");
        $txtImportUsers = $i18n->getText("Import Users");
        $txtCSV = $i18n->getText("CSV-File");
        $txtGroup = $i18n->getText("Group");
        $txtImport = $i18n->getText("Import");
        
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_groups";
        $groups = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);            
        
        // filter for user entered attributes
        $query = "select * from ".$prefix."newsletter_users LIMIT 1";
        $columns = $GLOBALS['POOL']->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);            
        unset($columns["id"]);
        unset($columns["activation"]);
        unset($columns["created"]);
        unset($columns["status"]);
        unset($columns["bounced"]);
        unset($columns["lastevent"]);
        
        // cache table headers
        $cols = '<cols>';
        for($i=0; $i<count($columns); $i++) {
            $cols .= '<col width="200"/>';
        }
        $cols .= '</cols><tr>';
        foreach($columns as $col=>$val) {
            $cols .= '<th class="stdBorder">'.$col.'</th>';
        }
        $cols .= '</tr>';
        
        $xml = '<newsletter>
        <h2>'.$txtUserMan.'</h2>
        <form enctype="multipart/form-data" name="bx_news_users" action="./" method="post">';
        
        foreach($groups as $group)
        {
            $xml .= '<h3>'.$group["name"].'</h3>
            <table>'.$cols;            
            
            // get this group's users
            $query = "select * from ".$prefix."newsletter_users2groups,".$prefix."newsletter_users where fk_group=".$group["id"]." and ".$prefix."newsletter_users.id=fk_user";
            $users = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
            
            foreach($users as $user) {
                $user['gender'] = $user['gender'] == 0 ? "male" : "female";
                $user['email'] = '<a href="'.BX_WEBROOT.'admin/dbforms2/newsletter_users/?id='.$user['id'].'">'.$user['email'].'</a>';
                
                $xml .= '<tr>';
                foreach($columns as $col=>$val) {
                    $xml .= '<td>'.$user[$col].'</td>';
                }
                $xml .= '</tr>';
            }
            
            $xml .= '</table><br/>';
        }
        
        $groupsHTML = '<select name="importgroup" size="1">';
        foreach($groups as $row)
        {
            $groupsHTML .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';    
        }
        $groupsHTML .= '</select>';
        
        // fiel upload to import a user list
        $xml .= '<br/><h2>'.$txtImportUsers.'</h2>
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000"/>
        <table>
        <tr><td>'.$txtCSV.':</td><td><input name="userfile" type="file"/></td></tr>
        <tr><td>'.$txtGroup.':</td><td>'.$groupsHTML.'</td></tr>
        <tr><td></td><td><input type="submit" name="bx[plugins][admin_edit][_all]" value="'.$txtImport.'" class="formbutton"/></td></tr>
        </table>
        </form>
        </newsletter>';
        
        return domdocument::loadXML($xml);        
    }
    
    /**
    * The feed view lets the user generate a html newsletter from a RSS feed
    */
    protected function generateFeedView($colluri)
    {
        $i18n = $GLOBALS['POOL']->i18nadmin;
        $txtFromFeed = $i18n->getText("Generate from Feed");
        $txtGen = $i18n->getText("Generate");
        
        // show a list of available feeds
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_feeds";
        $feeds = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);    
        
        $feedsHTML = '<select name="feed" size="1">';
        foreach($feeds as $feed)
        {
            $feedsHTML .= '<option value="'.$feed["id"].'">'.$feed["name"].'</option>';    
        }
        $feedsHTML .= '</select>';    
        
        
        $xml = '<newsletter>
        <form name="bx_news_send" action="./" method="post">
        <table border="0" id="send">
        <tr><td colspan="2"><h3>'.$txtFromFeed.'</h3></td></tr>
        <tr><td>Feed:</td><td>'.$feedsHTML.'</td></tr>
        <tr><td>HTML Format:</td><td><input type="checkbox" name="html"/></td></tr>
        <tr>
        <td></td>                    
        <td><input type="submit" name="bx[plugins][admin_edit][_all]" value="'.$txtGen.'" class="formbutton"/></td>
        </tr>
        </table>
        </form>
        </newsletter>';
        
        return domdocument::loadXML($xml);            
    }
    
    /**
    * Gets all newsletters saved in the collection
    */
    protected function getNewsletterFilenames($colluri)
    {
        
        
        $newsletters = array();
        
        $counter = 0;
        $files = scandir("data".$colluri."drafts/");
        
        foreach($files as $file)
        {
            // skip directories and config files
            if(strncmp($file,".",1)    != 0)
            if(strncmp($file,"index",5)    != 0)
            {
                $newsletters[$counter++] = $file;
            }
        }
        
        return $newsletters;
    }
    
    /**
    * Imports a list of new users as comma separated values (CSV)
    */
    protected function importUsers($file, $group)
    {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        
        // replace different line delimiters
        $file = str_replace(array("\r\n", "\r"), "\n", $file);
        
        $firstline = true;
        $queryFields = "";
        
        $lines = explode("\n", $file);
        foreach($lines as $line)
        {
            if($line == "") {
                continue;
            }
            
            $line = str_replace(";",",",$line);
            // first line defines which db fields are being filled in
            if($firstline === true) {
                $queryFields = $line;
                $firstline = false;
                continue;
            }
            
            // quote values
            $tokens = explode(",", $line);
            for($i=0; $i<count($tokens); $i++) {
                $tokens[$i] = "'" . $tokens[$i] . "'";    
            }
            $line = implode(",", $tokens);
            
            $query = "insert into ".$prefix."newsletter_users (".$queryFields.",created) value(".$line.",NOW())";
            if($GLOBALS['POOL']->dbwrite->exec($query) == 1) {
                $query = "insert into ".$prefix."newsletter_users2groups (fk_user,fk_group) value('".$GLOBALS['POOL']->dbwrite->lastInsertID($prefix."newsletter_users", "id")."','".$group."')";
                $GLOBALS['POOL']->dbwrite->exec($query);
            }
        }
    }
    
    /**
    * Generates a HTML newsletter file from the given RSS feed. Only new entries since the feed was read last
    * time are included in the message.
    */
    protected function createFromFeed($data,$colluri)
    {
        
        
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "SELECT * FROM ".$prefix."newsletter_feeds WHERE id=".$data["feed"];
        $feed = $GLOBALS['POOL']->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);            
        
        // use html special characters so the code can be displayed later
        $feedContent = file_get_contents($feed["url"]);
        $feedContent = str_replace(array('&lt;', '&gt;'), array('<', '>'), $feedContent);
        
        // simplify date format (YYYYMMDDhhmmss) so we can compare it later within Xslt
        $this->callbackDate = $feed["lastdate"];
        $feedContent = preg_replace("/<dc:date>([^<]*)<\/dc:date>/e", 
        "'<dc:date>'.\$this->callbackFeedDate(date('YmdHis', strtotime('$1'))).'</dc:date>'", 
        $feedContent);
        
        $filenameSimple = $feed["name"].'_'.date("Ymd-His");
        
        if($data["html"] == "on") {
            // HTML format
            $filename = $filenameSimple.'.de.xhtml';
            $xsl = new DomDocument();
            $xsl->load('themes/'.bx_helpers_config::getTheme().'/newsfeeds.xsl');
            $inputdom = new DomDocument();
            $inputdom->loadXML($feedContent);
            $proc = new XsltProcessor();
            $xsl = $proc->importStylesheet($xsl);
            $proc->setParameter('', 'lastdate', $feed["lastdate"]);
            $newdom = $proc->transformToDoc($inputdom);
            
            $newdom->save('data'.$colluri.'drafts/'.$filename);
        }
        else
        {
            // Plain Text format
            $filename = $filenameSimple.'-txt.de.xhtml';
            $xsl = new DomDocument();
            $xsl->load('themes/'.bx_helpers_config::getTheme().'/textfeeds.xsl');
            $inputdom = new DomDocument();
            $inputdom->loadXML($feedContent);
            $proc = new XsltProcessor();
            $xsl = $proc->importStylesheet($xsl);
            $proc->setParameter('', 'lastdate', $feed["lastdate"]);
            $newdom = $proc->transformToDoc($inputdom);     
            
            // replace breaks with newline ASCII character
            $nodeValue = $newdom->getElementsByTagName('div')->item(0)->nodeValue;
            $nodeValue = str_replace("<br/>", "\n", $nodeValue);        
            
            file_put_contents('data'.$colluri.'drafts/'.$filename, $nodeValue);
        }
        
        // add as resource so it's visible inside the collection
        bx_resourcemanager::setProperty($colluri."drafts/".$filename, "parent-uri", $colluri."drafts/");
        bx_resourcemanager::setProperty($colluri."drafts/".$filename, "display-name", $filenameSimple);
        bx_resourcemanager::setProperty($colluri."drafts/".$filename, "display-order", "0");
        bx_resourcemanager::setProperty($colluri."drafts/".$filename, "mimetype", "text/html");
        bx_resourcemanager::setProperty($colluri."drafts/".$filename, "output-mimetype", "text/html");
        
        // update table with the date of the most recent feed entry
        $query = "UPDATE ".$prefix."newsletter_feeds SET lastdate='".$this->callbackDate."' WHERE id=".$data["feed"];
        $GLOBALS['POOL']->dbwrite->exec($query);             
    }
    
    /**
    * This callback function is invoked by createFromFeed in order to extract the date of the most recent feed entry
    */
    protected function callbackFeedDate($date)
    {
        // if the new date is greater save it
        if($this->callbackDate < $date) {
            $this->callbackDate = $date;
        }
        
        return $date;
    }
    
    /**
    * Returns an array of newsletter mail servers
    * 
    * @return associated array
    */
    protected function getMailServers()
    {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_mailservers";
        $res = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);    
        return $res;        
    }
    
    /**
    * Gets a value from the .configxml
    */
    protected function getConfigParameter($id, $name)
    {
        $collection = bx_collections::getCollection($id);
        $plugins = $collection->getPluginMapByRequest($id);
        foreach($plugins as $p ) {
            if ($p['plugin']->name == 'newsletter') {
                $plugin = $p['plugin'];
                break;
            }
        }
        return $plugin->getParameter($collection->uri,$name);    
    }
    
    /**
    * Find all templates that don't match a dbfield from the newsletter_users table
    */
    protected function checkTemplates($content)
    {
        // templates in the form of {text}
        preg_match_all("/{([^\}:]*)}/", $content, $matches);
        
        // get column names
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "SHOW COLUMNS FROM ".$prefix."newsletter_users";
        $cols = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);     
        
        // find the matching fields
        $found = array();
        foreach($matches[1] as $match) {
            foreach($cols as $col) {
                if($col['field'] == $match) {
                    $found[] = $match;
                }
            }
        }
        
        // default templates
        array_push($found, 'weblink', 'activate', 'unsubscribe', 'publication', 'date');
        
        // compute difference
        $missing = array_diff($matches[1], $found);
        return $missing;
    }
}

?>
