<?php

/*

CHIP: Code Highlighting in PHP.

Copyright (c) 2004-2005 Ewan Mellor <chip@ewanmellor.org.uk>.  All rights
reserved.

*/


$keywords = array(
  "and",
  "assert",
  "break",
  "class",
  "continue",
  "def",
  "del",
  "elif",
  "else",
  "except",
  "exec",
  "finally",
  "for",
  "from",
  "global",
  "if",
  "import",
  "in",
  "is",
  "lambda",
  "not",
  "or",
  "pass",
  "print",
  "raise",
  "return",
  "try",
  "while"
  );


$blockCommentStart = FALSE;
$blockCommentEnd   = FALSE;
$lineCommentStart  = "#";
$blockStringStart = '"""';
$blockStringEnd   = '"""';

$sQuotedStrings = TRUE;
$dQuotedStrings = TRUE;

?>
