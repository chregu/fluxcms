<?php

/**
This is filter to mail form data, including uploaded attachments.

Parameters:
emailTo - address to send
emailFromField - field to set value of header From
subjectTemplateKey - i18n catalogue key to get subject template
bodyTemplateKey - i18n catalogue key to get subject template
charset - charset to use when sending message (both header and body).
uploadDir - directory to get uploaded files
uploadFormFields - space separated fields containing uploaded filenames

This filter uses PEAR::Mail and PEAR::Mail_mime.
All attachments encoded as application/octet-stream, encoded in base64.

*/
class bx_filters_patforms_pearmail extends bx_filters_patforms_formhandler {

    public function submitFields($params, $fields) {
				$charset = !empty($params['charset']) ? $params['charset'] : "ISO-8859-1";

				$mailTo = $params['emailTo'];
				if(empty($mailTo)) $mailTo="root";
				
        $mailFrom = 'No address specified <webmaster@localhost>';
        if(!empty($params['emailFromField']) && !empty($fields[$params['emailFromField']])) {
            $mailFrom = $fields[$params['emailFromField']];
        } else if(!empty($params['emailFrom'])) {
            $mailFrom = $params['emailFrom'];
        }
				
				// get subj and body from i18n catalogue 
        $mailSubj = !empty($params['subjectTemplateKey']) ? $this->getText($params['subjectTemplateKey']) : '';
        $mailBody = !empty($params['bodyTemplateKey']) ? $this->getText($params['bodyTemplateKey']) : '';
        // fields contain themselves as formatted string
        $fields['__allfields__'] = bx_helpers_string::formatTextFields($fields);
        // replace textfields in subject and body
        $mailSubj = bx_helpers_string::replaceTextFields($mailSubj, $fields);
        $mailBody = bx_helpers_string::replaceTextFields($mailBody, $fields);
        // recode utf8 strings
				$mailSubj=iconv("utf8",$charset,$mailSubj);
				$mailBody=iconv("utf8",$charset,$mailBody);
				
				
				$mailHeaders = array(
						'User-Agent' => "Flux CMS " . BXCMS_VERSION.'/'.BXCMS_REVISION,
						'From' => $mailFrom,
            'Subject' => $mailSubj,
						);
							
				$message = new Mail_mime();
				$message->setTXTBody($mailBody);
				
				$uploads =   $params['uploadFormFields'];
				$uploaddir = $params['uploaddir'];
				if( !empty($uploads) ) {
					foreach(explode(' ',$uploads) as $upload) {
						$filename = $fields[$upload];
						$encfilename = "=?utf-8?Q?".
                           preg_replace( '/[^\x21-\x3C\x3E-\x7E\x09\x20]/e', 
                                        'sprintf( "=%02x", ord ( "$0" ) ) ;',  $filename ).
                           "?=";
						if( TRUE != $message->addAttachment($uploaddir."/".$filename,
																					'application/octet-stream',
                                          $encfilename,
																					TRUE, 
																					'base64') ) {
							error_log("Failed to attach $uploaddir/$upload");
							}
						}
					}

				$build_params = array(
						'text_encoding'=>'8bit',
						'head_charset'=>$charset,
						'text_charset'=>$charset,
					  );
				
				$msg_body = $message->get($build_params);
				$msg_hdrs = $message->headers($mailHeaders);

				$mail = &Mail::factory('sendmail');
				$mail->send($mailTo, $msg_hdrs, $msg_body);

        return TRUE;
    }
    
}

?>
