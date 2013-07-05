<?php

/*

CHIP: Code Highlighting in PHP.

Copyright (c) 2004-2005 Ewan Mellor <chip@ewanmellor.org.uk>.  All rights
reserved.

*/


require_once "chip-javadoc.php";
require_once "chip-comment.php";
require_once "chip-javascript.php";
require_once "chip-html.php";


$phpKeywords = array(
  "and",
  "array",
  "as",
  "break",
  "case",
  "cfunction",
  "class",
  "const",
  "continue",
  "declare",
  "default",
  "die",
  "do",
  "echo",
  "else",
  "elseif",
  "empty",
  "enddeclare",
  "endfor",
  "endforeach",
  "endif",
  "endswitch",
  "endwhile",
  "eval",
  "exception",
  "exit",
  "extends",
  "for",
  "foreach",
  "function",
  "global",
  "if",
  "include",
  "include_once",
  "isset",
  "list",
  "new",
  "old_function",
  "or",
  "php_user_filter",
  "print",
  "require",
  "require_once",
  "return",
  "static",
  "switch",
  "unset",
  "use",
  "xor",

  "__FILE__",
  "__LINE__",
  "__FUNCTION__",
  "__CLASS__",
  "__METHOD__",
  "__file__",
  "__line__",
  "__function__",
  "__class__",
  "__method__",
  "__File__",
  "__Line__",
  "__Function__",
  "__Class__",
  "__Method__",

  "FALSE",
  "TRUE",
  "false",
  "true",
  "False",
  "True",

  "NULL",
  "null",
  "Null",

  "define"
  );


$phpState =& newState();

$phpState['class'] = $normalClass;

$phpState['variableStart']  = "$";
$phpState['sQuotedStrings'] = TRUE;
$phpState['dQuotedStrings'] = TRUE;

$phpState['keywords'] =& $phpKeywords;

$phpState['blocks'][]    = "/*";
$phpState['end']  ["/*"] = "*/";
$phpState['state']["/*"] =& $commentState;

$phpState['blocks'][]    = "//";
$phpState['end']  ["//"] = "\n";
$phpState['state']["//"] =& $commentState;


$javaScriptState['class'] = $embedded2Class;

$javaScriptState['blocks'][]       = "<?php";
$javaScriptState['end']  ["<?php"] = "?>";
$javaScriptState['state']["<?php"] =& $phpState;


$htmlState['class']  = $embedded1Class;

$htmlState['blocks'][]       = "<?php";
$htmlState['end']  ["<?php"] = "?>";
$htmlState['state']["<?php"] =& $phpState;

// Bit of a hack: delete the entry in blocks for < and then re-add it so that
// it comes after the entry for <?php.  This is required to ensure that the
// former is recognised in preference.
foreach ($htmlState['blocks'] as $key => $block)
{
  if ($block == "<")
  {
    unset($htmlState['blocks'][$key]);
    break;
  }
}
$htmlState['blocks'][] = "<";


$topState =& $htmlState;

?>
