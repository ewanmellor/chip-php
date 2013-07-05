<?php

/*

CHIP: Code Highlighting in PHP.

Copyright (c) 2004-2005 Ewan Mellor <chip@ewanmellor.org.uk>.  All rights
reserved.

*/


$keywords = array(
    "cond",
    "defconst",
    "defcustom",
    "defgroup",
    "define",
    "define-structure",
    "defvar",
    "export",
    "if",
    "lambda",
    "let",
    "let\*",
    "open",
    "setq",
    "unless",
    "when"
    );


$blockCommentStart   = "#|";
$blockCommentEnd     = "|#";
$keywordStartLetters = "~("; // Due to what I assume is a PHP/PCRE bug, this
                             // cannot be a single character, so the ~ is here
                             // as a workaround.
$lineCommentStart    = ";";
$variableStart       = " :";
$blockStringStart    = FALSE;
$blockStringEnd      = FALSE;

$sQuotedStrings = FALSE;
$dQuotedStrings = TRUE;

?>
