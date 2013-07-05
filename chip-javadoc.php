<?php

/*

CHIP: Code Highlighting in PHP.

Copyright (c) 2004-2005 Ewan Mellor <chip@ewanmellor.org.uk>.  All rights
reserved.

*/


require_once "chip-comment.php";


$javadocKeywords = array(
  "@link",
  "@param",
  "@return",
  "@see"
  );

$inCommentKeywords =& $javadocKeywords;

$javadocCommentState = $commentState;

$javadocCommentState['keywordClass']  = $inCommentKeywordClass;
$javadocCommentState['keywords'] =& $javadocKeywords;
$javadocCommentState['keywordsCaseInsensitive'] = FALSE;


?>
