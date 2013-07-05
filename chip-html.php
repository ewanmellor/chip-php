<?php

/*

CHIP: Code Highlighting in PHP.

Copyright (c) 2004-2005 Ewan Mellor <chip@ewanmellor.org.uk>.  All rights
reserved.

*/


require_once "chip-comment.php";
require_once "chip-javascript.php";


$htmlKeywords = array(
  "align",
  "alink",
  "alt",
  "bgcolor",
  "class",
  "clear",
  "content",
  "height",
  "href",
  "http-equiv",
  "id",
  "lang",
  "link",
  "name",
  "noshade",
  "onload",
  "rel",
  "rev",
  "src",
  "style",
  "target",
  "text",
  "title",
  "type",
  "vlink",
  "width",
  "xml:lang",
  "xmlns"
  );


$tagState =& newState();

$tagState['class'] = $variableClass;
$tagState['sQuotedStrings'] = TRUE;
$tagState['dQuotedStrings'] = TRUE;

$tagState['keywords'] =& $htmlKeywords;
$tagState['keywordEndLetters'] = " \t\r\n=>";
$tagState['keywordsCaseInsensitive'] = TRUE;

$javaScriptState['class'] = $embedded1Class;

$htmlState =& newState();

$htmlState['blocks'][]      = "<!--";
$htmlState['end']  ["<!--"] = "->";
$htmlState['state']["<!--"] =& $commentState;

$htmlState['blocks'][]         = "<script";
$htmlState['end']  ["<script"] = "</script>";
$htmlState['state']["<script"] =& $javaScriptState;

$htmlState['blocks'][]   = "<";
$htmlState['end']  ["<"] = ">";
$htmlState['state']["<"] =& $tagState;


$topState =& $htmlState;

?>
