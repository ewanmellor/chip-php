<?php

/*

CHIP: Code Highlighting in PHP.

Copyright (c) 2004-2005 Ewan Mellor <chip@ewanmellor.org.uk>.  All rights
reserved.

*/


require_once "chip-javadoc.php";


$javaScriptKeywords = array(
  "abstract",
  "boolean",
  "break",
  "byte",
  "case",
  "catch",
  "char",
  "class",
  "const",
  "continue",
  "debugger",
  "default",
  "delete",
  "do",
  "double",
  "else",
  "enum",
  "export",
  "extends",
  "false",
  "final",
  "finally",
  "float",
  "for",
  "function",
  "goto",
  "if",
  "implements",
  "import",
  "in",
  "instanceof",
  "int",
  "interface",
  "long",
  "native",
  "new",
  "null",
  "package",
  "private",
  "protected",
  "public",
  "return",
  "short",
  "static",
  "super",
  "switch",
  "synchronized",
  "this",
  "throw",
  "throws",
  "transient",
  "true",
  "try",
  "typeof",
  "var",
  "void",
  "volatile",
  "while",
  "with"
  );


$javaScriptState =& newState();

$javaScriptState['sQuotedStrings'] = TRUE;
$javaScriptState['dQuotedStrings'] = TRUE;

$javaScriptState['keywords'] =& $javaScriptKeywords;

$javaScriptState['blocks'][]    = "/*";
$javaScriptState['end']  ["/*"] = "*/";
$javaScriptState['state']["/*"] =& $javadocCommentState;

$javaScriptState['blocks'][]    = "//";
$javaScriptState['end']  ["//"] = "\n";
$javaScriptState['state']["//"] =& $javadocCommentState;

$topState =& $javaScriptState;

?>
