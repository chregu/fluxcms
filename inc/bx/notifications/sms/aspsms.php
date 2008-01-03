<?php

/*
 * Copyright (C) 2002-2007 Oliver Hitz <oliver@net-track.ch>
 *
 * $Id: SMS.inc,v 1.5 2007-09-18 14:23:13 oli Exp $
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * adapted for flux-cms by gassi
 * changed classname and constructor
 */

class bx_notifications_sms_aspsms {
  var $userkey;
  var $password;
  var $originator;
  var $recipients;
  var $content;
  var $blinking;
  var $flashing;
  var $debug;
  var $mcc;
  var $mnc;
  var $logo;
  var $timeout;
  var $servers;
  var $deferred;
  var $timezone;
  var $notification;

  function __construct($u, $p)
  {
    $this->userkey = $u;
    $this->password = $p;
    $this->recipients = array();
    $this->blinking = false;
    $this->flashing = false;
    $this->originator = "";
    $this->debug = 0;
    $this->timeout = 2;
    $this->notification = array();

    $this->servers = array( "xml1.aspsms.com:5061",
                "xml1.aspsms.com:5098",
                "xml2.aspsms.com:5061",
                "xml2.aspsms.com:5098" );
  }

  function setTimeout($t)
  {
    $this->timeout = $t;
  }

  function setOriginator($o)
  {
    $this->originator = $o;
  }

  function setDeferred($d)
  {
    $this->deferred = $d;
  }

  function setTimezone($t)
  {
    $this->timezone = $t;
  }

  function addRecipient($r, $id = null)
  {
    $this->recipients[] = array( "number" => $r, "transaction" => $id );
  }

  function setMCC($mcc)
  {
    $this->mcc = $mcc;
  }

  function setMNC($mnc)
  {
    $this->mnc = $mnc;
  }

  function setLogo($logo)
  {
    $this->logo = $logo;
  }

  function setContent($content, $parse = false)
  {
    if ($parse) {
      $this->content = "";
      $in = false;
      for ($i = 0; $i < strlen($content); $i++) {
        $c = $content[$i];
        if ($c == "[" && !$in) {
          $this->content .= "<blink>";
        $in = true;
      $this->blinking = true;
        } else if ($c == "]" && $in) {
          $this->content .= "</blink>";
      $in = false;
        } else {
          $this->content .= $c;
        }
      }
    } else {
      $this->content = $content;
    }
  }

  function setFlashing()
  {
    $this->flashing = true;
  }

  function setBufferedNotificationURL($url)
  {
    $this->notification["buffered"] = $url;
  }

  function setDeliveryNotificationURL($url)
  {
    $this->notification["delivery"] = $url;
  }

  function setNonDeliveryNotificationURL($url)
  {
    $this->notification["nondelivery"] = $url;
  }

  function getXML($content, $action)
  {
    $originator = "";
    if ($this->originator != "") {
      $originator = sprintf("  <Originator>%s</Originator>\r\n", $this->originator);
    }

    $recipients = "";
    if (count($this->recipients) > 0) {
      foreach ($this->recipients as $re) {
    if ($re["transaction"] != null) {
      $recipients .= sprintf("  <Recipient>\r\n".
                 "    <PhoneNumber>%s</PhoneNumber>\r\n".
                 "    <TransRefNumber>%s</TransRefNumber>\r\n".
                 "  </Recipient>\r\n",
                 htmlspecialchars($re["number"]),
                 htmlspecialchars($re["transaction"]));
    } else {
      $recipients .= sprintf("  <Recipient>\r\n".
                 "    <PhoneNumber>%s</PhoneNumber>\r\n".
                 "  </Recipient>\r\n",
                 htmlspecialchars($re["number"]));
    }
      }
    }

    $notify = "";
    if (isset($this->notification["buffered"])) {
      $notify .= sprintf("  <URLBufferedMessageNotification>%s</URLBufferedMessageNotification>\r\n",
             htmlspecialchars($this->notification["buffered"]));
    }
    if (isset($this->notification["delivery"])) {
      $notify .= sprintf("  <URLDeliveryNotification>%s</URLDeliveryNotification>\r\n",
             htmlspecialchars($this->notification["delivery"]));
    }
    if (isset($this->notification["nondelivery"])) {
      $notify .= sprintf("  <URLNonDeliveryNotification>%s</URLNonDeliveryNotification>\r\n",
             htmlspecialchars($this->notification["nondelivery"]));
    }

    if (isset($this->deferred)) {
      $deferred = sprintf("  <DeferredDeliveryTime>%s</DeferredDeliveryTime>\r\n", $this->deferred);
    } else {
      $deferred = "";
    }
    if (isset($this->timezone)) {
      $timezone = sprintf("  <TimeZone>%s</TimeZone>\r\n", $this->timezone);
    } else {
      $timezone = "";
    }

    return sprintf("<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n".
           "<aspsms>\r\n".
           "  <Userkey>%s</Userkey>\r\n".
           "  <Password>%s</Password>\r\n".
           "%s%s%s%s%s%s%s".
                   "  %s\r\n".
           "  <Action>%s</Action>\r\n".
           "</aspsms>\n",
           $this->userkey,
           $this->password,
           $originator,
           $this->flashing ? "  <FlashingSMS>1</FlashingSMS>\r\n" : "",
           $this->blinking ? "  <BlinkingSMS>1</BlinkingSMS>\r\n" : "",
           $recipients,
           $deferred,
           $timezone,
           $notify,
           $content,
           $action);
  }

  function setDebug()
  {
    $this->debug = 1;
  }

  function sendVCard($name, $phone)
  {
    $content = sprintf("<VCard><VName>%s</VName><VPhoneNumber>%s</VPhoneNumber></VCard>", htmlspecialchars($name), htmlspecialchars($phone));
    return $this->send($this->getXML($content, "SendVCard"));
  }

  function sendSMS()
  {
    return $this->send($this->getXML("<MessageData>".htmlspecialchars($this->content)."</MessageData>", "SendTextSMS"));
  }

  function sendLogo()
  {
    $c = sprintf("<MCC>%s</MCC><MNC>%s</MNC><URLBinaryFile>%s</URLBinaryFile>",
                 $this->mcc, $this->mnc, $this->logo);
    return $this->send($this->getXML($c, "SendLogo"));
  }

  function showCredits()
  {
    return $this->send($this->getXML("", "ShowCredits"));
  }

  function send($msg)
  {
    foreach ($this->servers as $server) {
      list($host, $port) = explode(":", $server);
      $result = $this->sendToServer($msg, $host, $port);
      if ($result == 1) {
    return $result;
      }
    }
    return 0;
  }

  function sendToServer($msg, $host, $port)
  {
    if ($this->debug) {
      print "<pre>";
      print nl2br(htmlentities($msg));
      print "</pre>";
      return 1;
    } else {
      $errno = 0;
      $errdesc = 0;
      $fp = fsockopen($host, $port, $errno, $errdesc, $this->timeout);
      if ($fp) {
        fputs($fp, "POST /xmlsvr.asp HTTP/1.0\r\n");
        fputs($fp, "Content-Type: text/xml\r\n");
        fputs($fp, "Content-Length: ".strlen($msg)."\r\n");
        fputs($fp, "\r\n");
        fputs($fp, $msg);

        $content = 0;
        $reply = array();
        while (!feof($fp)) {
          $r = fgets($fp, 1024);
            if ($content) {
          $reply[] = $r;
      } else {
        if (trim($r) == "") {
          $content = 1;
        }
      }
        }

        fclose($fp);
        $this->parseResult(join("", $reply));
        return $this->result["ErrorCode"];
      } else {
    $this->result["ErrorCode"] = 0;
        $this->result["ErrorDescription"] = "Unable to connect to ".$host.":".$port;
        return 0;
      }
    }
  }

  function getErrorCode()
  {
    return $this->result["ErrorCode"];
  }

  function getErrorDescription()
  {
    return $this->result["ErrorDescription"];
  }

  function getCredits()
  {
    return $this->result["Credits"];
  }

  var $result = array();
  var $nextResult = "";

  function startElement($parser, $name, $attrs)
  {
    if ($name == "ErrorCode" || $name == "ErrorDescription" || $name == "Credits") {
      $this->nextResult = $name;
    }
  }

  function endElement($parser, $name)
  {
    $this->nextResult = "";
  }

  function characterData($parser, $data)
  {
    if ($this->nextResult != "") {
      $this->result[$this->nextResult] .= $data;
    }
  }

  function parseResult($result)
  {
    // Clear the result
    $this->result = array("ErrorCode" => 0,
              "ErrorDescription" => "",
              "Credits" => "");

    $p = xml_parser_create();
    xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, false);
    xml_set_element_handler($p, array(&$this, "startElement"), array(&$this, "endElement"));
    xml_set_character_data_handler($p, array(&$this, "characterData"));
    if (!xml_parse($p, $result, true)) {
      $this->result["ErrorCode"] = 0;
      $this->result["ErrorDescription"] = "Unable to parse result.";
    }
    xml_parser_free($p);
  }
}

?>
