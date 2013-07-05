<?php

define("VERSION", "2.7.0");

/*

CHIP: Code Highlighting in PHP.  Version 2.7.0.

Copyright (c) 2004-2005 Ewan Mellor <chip@ewanmellor.org.uk>.  All rights
reserved.

This software is covered by the MIT Licence
<http://www.opensource.org/licenses/mit-license.html>:

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to
  deal in the Software without restriction, including without limitation the
  rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
  sell copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
    DEALINGS IN THE SOFTWARE.


CHIP is a simple source code highlighter designed to be used on webservers.
It takes C, C++, Java, Lisp, Python, PHP, HTML, or JavaScript code, and
outputs an XHTML version of that code, with appropriate highlighting.

CHIP contains measures to protect against spambots: any email addresses in the
input file are obfuscated in the output, and then this obfuscation is
corrected by a JavaScript function.  This should stymie any searches for email
addresses as it is too expensive for spiders to execute JavaScript in their
search for addresses.  In order that the addresses are not found in the plain
version instead, the links to those files are obfuscated too.  This means that
spiders, both for respectable search engines and disreputable spammers, will
not be able to follow those links.


See the README file for installation and configuration details.


Developers' Info
----------------

CHIP requires PHP >= 4.1.0.  This is because it uses $_GET and $_SERVER
instead of $HTTP_GET_VARS and $HTTP_SERVER_VARS, as the latter two are
deprecated and will be disablable from PHP 5.0.

*/


$behaviourMap = array(
  ".C"       => "c++",
  ".c"       => "c++",
  ".cc"      => "c++",
  ".cpp"     => "c++",
  ".c++"     => "c++",
  ".H"       => "c++",
  ".h"       => "c++",
  ".hh"      => "c++",
  ".htm"     => "html",
  ".html"    => "html",
  ".java"    => "java",
  ".jl"      => "lisp",
  ".js"      => "javascript",
  ".lisp"    => "lisp",
  ".lsp"     => "lisp",
  ".php"     => "php",
  "-php.txt" => "php",
  ".php4"    => "php",
  ".py"      => "python"
  );


/**
 * Whether email addresses are obfuscated in comments in the output.  This is
 * a spam-protection measure; disable this at your peril!
 */
define("OBFUSCATE_EMAIL_ADDRESSES", TRUE);

/**
 * Whether links to the plain output are obfuscated in the output.  This is a
 * spam-protection measure; disable this at your peril!
 */
define("OBFUSCATE_PLAIN_PAGE_LINKS", TRUE);


/**
 * Whether to apply the heuristics when processing URLs.  These heuristics are
 * designed to separate URLs from the surrounding punctuation.
 */
define("APPLY_URL_HEURISTICS", TRUE);


define("BREADCRUMB_SEPARATOR",
  "<span class='chip-breadcrumbArrow'>&nbsp;&nbsp;&gt;&nbsp;&nbsp;</span>");


/**
 * Whether to show line numbers by default.  Overridden by the linenumbers
 * parameter.
 */
define("DEFAULT_SHOW_LINE_NUMBERS", TRUE);


/**
 * How regularly line numbers should be inserted.
 */
define("LINE_NUMBER_MODULUS", 5);


/**
 * The column to which each line is padded.  Padding improves the appearance
 * of blocks of mixed-language code.
 */
define("PAD_COLUMN", 80);


/**
 * The transition to use when revealing the page.  Using a transition hides
 * the changes to the page caused when the JavaScript fixes any embedded email
 * addresses and URLs and the links to the plain pages.
 *
 * Transitions are specific to Internet Explorer.  The HTTP header Page-Enter:
 * revealTrans(duration='1', transition='n') is used, when n is the number
 * given below.
 *
 * This value may be -1, in which case no transition is used.
 *
 * This value may be overridden by the "transition" script parameter.
 */
define("DEFAULT_TRANSITION", 20);


/**
 * @return The header applied to our output.  Pure XHTML.
 */
function pageHeader()
{
  global $filename;

  $obfuscatedLink = plainPageObfuscatedLink(FALSE);
  $breadcrumb = breadcrumb();

  if ($breadcrumb == "")
  {
    return <<<EOS
<p style="text-align: right">$obfuscatedLink</p>
<p style="text-align: center">&middot; $filename &middot;</p>

EOS;
  }
  else
  {
    return <<<EOS
<p class="chip-breadcrumbs">$breadcrumb</p>
<p class="chip-toplink">$obfuscatedLink</p>
<p class="chip-breadcrumbSkipper">&nbsp;</p>
<p style="text-align: center">&middot; $filename &middot;</p>

EOS;
  }

}


/**
 * @return The footer applied to our output.  Pure XHTML.
 */
function pageFooter()
{
  global $filename;

  $obfuscatedLink = plainPageObfuscatedLink(TRUE);

  $chipLink = chipAttribution();

  return <<<EOS
<p style="text-align: center">&middot; $filename ends &middot;</p>
<p class="chip-bottomlink">$obfuscatedLink</p>
<p class="chip-attribution">$chipLink</p>

EOS;
}


/**
 * @param bottom Whether the bottom link is requested or the top.
 * @return The obfuscated link to the plain page (or the non-obfuscated
 * version, if OBFUSCATE_PLAIN_PAGE_LINKS is FALSE).
 */
function plainPageObfuscatedLink($bottom)
{
  global $topLinkLabel;
  global $bottomLinkLabel;
  global $path;
  global $url;

  if (OBFUSCATE_PLAIN_PAGE_LINKS)
  {
    $label = $bottom ? $bottomLinkLabel : $topLinkLabel;

    return <<<EOS
<span id='$label'>
To load this file without formatting, visit $url.  This is a spam-protection
measure; sorry for the inconvenience.
</span>

EOS;
  }
  else
  {
    return anchor($path, plainPageAnchorText());
  }
}


/**
 * @return The text to label the anchor that refers to plain text versions of
 * our output.
 */
function plainPageAnchorText()
{
  global $filename;

  return "(Load $filename without formatting)";
}


/**
 * @return A link to the CHIP home page to use as attribution.
 */
function chipAttribution()
{
  return
    anchor("http://www.ewanmellor.org.uk/chip.html",
           "Generated by CHIP: Code Highlighting in PHP, version " . VERSION .
           ".");
}


function breadcrumb()
{
  global $breadcrumbURLs;
  global $breadcrumbTitles;
  global $filename;

  if (!isSet($breadcrumbURLs[0]))
  {
    return "";
  }


  $result = "";
  $i = 0;
  while (isSet($breadcrumbURLs[$i]))
  {
    $result .= "<a href='$breadcrumbURLs[$i]'>$breadcrumbTitles[$i]</a>";
    $result .= BREADCRUMB_SEPARATOR;
    $i++;
  }

  $result .= $filename;

  return $result;
}


/* ** CSS Interface Variables ** */

$commentClass          = "chip-comment";
$embedded1Class        = "chip-embedded-1";
$embedded2Class        = "chip-embedded-2";
$inCommentKeywordClass = "chip-inCommentKeyword";
$keywordClass          = "chip-keyword";
$normalClass           = "chip-normal";
$stringClass           = "chip-string";
$urlClass              = "chip-url";
$variableClass         = "chip-variable";


/* ** JavaScript Interface Variables ** */

$emailLabelPrefix = "chip-rotemailaddress";
$topLinkLabel     = "chip-toplink";
$bottomLinkLabel  = "chip-bottomlink";


/* ** Debugging Options ** */


define("DISABLE_JAVASCRIPT", FALSE);
define("DEBUG", FALSE);


/* ** Code Body ** */


define("STRING_LOOKAHEAD", 100);


/**
 * The main processing function.
 */
function process()
{
  global $topState;
  global $fd;

  if (!isSet($topState))
  {
    $topState =& makeTopState();
  }

  prepareState(&$topState);

  if ($topState['class'])
  {
    newClass($topState['class']);
  }

  processState("", $fd, FALSE, &$topState);

  if ($topState['class'])
  {
    endClass();
  }
}


function processState($text, $fd, $eot, &$state)
{
  if (DEBUG)
  {
    echo escape("\nIn state {$state['name']}\n");
  }

  $buffer = "";

  while (TRUE)
  {
    if ($fd !== FALSE && strlen($text) < STRING_LOOKAHEAD)
    {
      if (!$eot && strlen($buffer) < STRING_LOOKAHEAD)
      {
        if (feof($fd))
        {
          $eot = TRUE;
        }
        else
        {
          $buffer .= fread($fd, 4096);
        }
      }

      $n = min(STRING_LOOKAHEAD, strlen($buffer));

      $text .= substr($buffer, 0, $n);
      $buffer = substr($buffer, $n, strlen($buffer));
    }

    if (!$eot && strlen($text) < STRING_LOOKAHEAD)
    {
      if (DEBUG)
      {
        echo "Returning for more.\n";
      }

      return $text;
    }

    if (strlen($text) == 0)
    {
      if (DEBUG)
      {
        echo "Returning; done.\n";
      }

      return $text;
    }

    if ($state['inBlock'])
    {
      $result = processInBlock($text, $eot, &$state, $state['inBlock']);
      $state['inBlock'] = $result[0];
      $text = $result[1];
    }
    elseif ($state['inSString'])
    {
      $result = processInQuotes($text, FALSE);
      $state['inSString'] = $result[0];
      $text = $result[1];
    }
    elseif ($state['inDString'])
    {
      $result = processInQuotes($text, TRUE);
      $state['inDString'] = $result[0];
      $text = $result[1];
    }
    elseif ($state['inBlockString'])
    {
      $endblock =
        $state['blockStringEnd'] ?
          strstr($text, $state['blockStringEnd']) :
          FALSE;

      if ($endblock === FALSE)
      {
        output($text);
        $text = "";
      }
      else
      {
        $before = substr($text, 0, strlen($text) - strlen($endblock));

        output($before);
        endClass();
        output($state['blockStringEnd']);

        $state['inBlockString'] = FALSE;

        $text = substr($endblock, strlen($state['blockStringEnd']));
      }
    }
    elseif ($state['inVariable'])
    {
      $bits = preg_split("/{$state['variableEnd']}/", $text, 2,
                         PREG_SPLIT_DELIM_CAPTURE);

      if (preg_match("/^{$state['variableEnd']}/", $bits[0]) != 0)
      {
        endClass();
        output($bits[0]);

        $text = substr($text, strlen($bits[0]));
      }
      else
      {
        output($bits[0]);

        if (count($bits) > 1)
        {
          endClass();

          $state['inVariable'] = FALSE;

          $text = substr($text, strlen($bits[0]));
        }
        else
        {
          $text = substr($text, strlen($bits[0]));
        }
      }
    }
    else
    {
      if (DEBUG)
      {
        echo "Doing composite\n";
      }

      $regex = $state['compositeRegex'];

      $matches = array();
      if ($regex === FALSE || preg_match($regex, $text, $matches) == 0)
      {
        if (strlen($text) > STRING_LOOKAHEAD)
        {
          $n = strlen($text) - STRING_LOOKAHEAD + 1;

          output(substr($text, 0, $n));
          $text = substr($text, $n);
        }
        else
        {
          output($text);
          $text = "";
        }
      }
      else
      {
        if (DEBUG)
        {
          echo "\n000{$matches[0]}000\n";

          if ($matches[0] == "")
          {
            output($regex);
          }
        }

        $match = strstr($text, $matches[0]);

        $before = substr($text, 0, strlen($text) - strlen($match));

        output($before);

        if (strlen($match) < STRING_LOOKAHEAD &&
            strlen($text) > STRING_LOOKAHEAD)
        {
          $text = $match;
          continue;
        }

        $text = substr($match, strlen($matches[0]));

        if ($matches[0] == $state['blockStringStart'])
        {
          output($state['blockStringStart']);
          newClass($state['stringClass']);
          $state['inBlockString'] = TRUE;
        }
        elseif ($matches[0] == "'")
        {
          output("'");
          newClass($state['stringClass']);
          $state['inSString'] = TRUE;
        }
        elseif ($matches[0] == '"')
        {
          output("\"");
          newClass($state['stringClass']);
          $state['inDString'] = TRUE;
        }
        elseif ($matches[0] == $state['variableStart'])
        {
          output($state['variableStart']);
          newClass($state['variableClass']);
          $state['inVariable'] = TRUE;
        }
        else
        {
          $found = FALSE;

          foreach ($state['blocks'] as $block)
          {
            if ($matches[0] == $block)
            {
              // We have found the start of a new block.  Enter it.

              $new_state =& $state['state'][$block];

              if ($new_state['class'])
              {
                newClass($new_state['class']);
              }

              output($block);
              $state['inBlock'] = $block;
              $found = TRUE;
              break;
            }
          }

          if (!$found && isSet($matches[1]))
          {
            if (DEBUG)
            {
              echo "\n111{$matches[1]}111\n";
            }

            foreach ($state['keywords'] as $keyword)
            {
              if (($state['keywordsCaseInsensitive'] &&
                   strcasecmp($matches[2], $keyword) == 0) ||
                  (!$state['keywordsCaseInsensitive'] &&
                   $matches[2] == $keyword))
              {
                newClass($state['keywordClass']);
                output($matches[2]);
                endClass();

                if (isSet($matches[3]))
                {
                  $text = $matches[3] . $text;
                }

                $found = TRUE;
                break;
              }
            }
          }

          if (!$found && startsWith($matches[0], "http://"))
          {
            // A URL.
            processURL($matches[0], &$state);

            $found = TRUE;
          }

          if (!$found && strchr($matches[0], "@"))
          {
            // An email address.
            processEmailAddress($matches[0]);

            $found = TRUE;
          }

          if (!$found)
          {
            // Can't happen.  Just protecting against accidental infinite
            // loops.
            echo "Can't Happen!";

            return "";
          }
        }
      }
    }
  }
}


function processInBlock($text, $eot, &$state, $nestedStateMarker)
{
  if (DEBUG)
  {
    output("InBlock $nestedStateMarker.\n");
  }


  $nested_state =& $state['state'][$nestedStateMarker];
  $end_marker = $state['end'][$nestedStateMarker];

  $end = strstr($text, $end_marker);

  if ($end === FALSE)
  {
    // We have not reached the end of this block.  Process the remaining
    // text in the nested state.

    $text = processState($text, FALSE, $eot, &$nested_state);

    return array($nestedStateMarker, $text);
  }
  else
  {
    // We have reached the end of this block.  Finish off, and leave this
    // state.

    $before = substr($text, 0, strlen($text) - strlen($end));

    processState($before, FALSE, TRUE, &$nested_state);

    if (inSomething(&$nested_state))
    {
      // Nested state isn't finished, so that means that we've not really
      // reached the end of the block.

      output($end_marker);

      if (DEBUG)
      {
        echo escape("Not really leaving State " .
                    $state['state'][$nestedStateMarker]['name'] . "\n");
      }

      return array($nestedStateMarker, substr($end, strlen($end_marker)));
    }
    else
    {
      // Really finished.

      output($end_marker);
      if ($nested_state['class'])
      {
        endClass();
      }

      if (DEBUG)
      {
        echo escape("Leaving State " .
                    $state['state'][$nestedStateMarker]['name'] .
                    " for " . $state['name'] . "\n");
      }

      return array(FALSE, substr($end, strlen($end_marker)));
    }
  }
}


function inSomething(&$state)
{
  return
    $state['inSString']     ||
    $state['inDString']     ||
    $state['inBlockString'] ||
    $state['inVariable']    ||
    $state['inBlock'];
}


/**
 * @return An array with the first element TRUE if still inside the quotes,
 * false otherwise, and the second element the text left to process.
 */
function processInQuotes($text, $doubleQuotes)
{
  global $stringsOneLine;

  $quote_char = $doubleQuotes ? '"' : "'";

  if ($text{0} == $quote_char)
  {
    endClass();
    output($quote_char);
    return array(FALSE, substr($text, 1));
  }

  $quote_pattern = "(?:\\\\\\\\)*$quote_char";

  if ($stringsOneLine)
  {
    $quote_pattern = "($quote_pattern|\n|\r)";
  }
  else
  {
    $quote_pattern = "($quote_pattern)";
  }

  $prequote_pattern = '(^|(?:^.*?[^\\\\]))';

  if (preg_match("/$prequote_pattern$quote_pattern/s", $text,
                 $quotesplit) == 0)
  {
    output($text);

    return array(TRUE, "");
  }
  else
  {
    output($quotesplit[1]);
    endClass();
    output($quotesplit[2]);

    return array(FALSE,
                 substr($text,
                        strlen($quotesplit[1]) + strlen($quotesplit[2])));
  }
}


$rotted = -1;
/**
 * Process the given email address, obfuscating it so that the JavaScript can
 * find it later.
 */
function processEmailAddress($addr)
{
  global $rotted;
  global $emailLabelPrefix;

  $rotted++;

  $after = substr(strstr($addr, "@"), 1);
  $before = substr($addr, 0, strlen($addr) - strlen($after) - 1);

  echo "<span id='$emailLabelPrefix$rotted'>";
  output(str_rot13($before));
  output(' at ');
  output(str_rot13($after));
  echo '</span>';
}


/**
 * Process the given URL, by wrapping it in a span element giving the
 * appropriate class, and making it into a link.
 *
 * @see #APPLY_URL_HEURISTICS
 */
function processURL($url, &$state)
{
  $suffix = "";

  if (APPLY_URL_HEURISTICS)
  {
    $n = strlen($url);
    $c = $url[$n - 1];

    if ($c == '.' || $c == ")" || $c == "," || $c == ";" || $c == ":" ||
        $c == '?' || $c == "!")
    {
      $url = substr($url, 0, $n - 1);
      $suffix = $c;
    }
  }

  newClass($state['urlClass']);
  outputAnchor($url);
  endClass();
  output($suffix);
}


/* ** Handling of the Simple and Advanced Interfaces ** */

/**
 * @return A state block derived from the settings given via the simple
 * interface.
 */
function makeTopState()
{
  global $commentClass;

  global $sQuotedStrings;
  global $dQuotedStrings;

  global $keywords;
  global $keywordsCaseInsensitive;
  global $inCommentKeywords;

  global $blockCommentStart;
  global $blockCommentEnd;
  global $blockStringStart;
  global $blockStringEnd;
  global $keywordStartLetters;
  global $keywordEndLetters;
  global $lineCommentStart;
  global $variableStart;
  global $variableEnd;


  $top_state =& newState();

  setInState($top_state, 'sQuotedStrings');
  setInState($top_state, 'dQuotedStrings');

  setInState($top_state, 'keywords');
  setInState($top_state, 'keywordsCaseInsensitive');

  setInState($top_state, 'blockStringStart');
  setInState($top_state, 'blockStringEnd');
  setInState($top_state, 'keywordStartLetters');
  setInState($top_state, 'keywordEndLetters');
  setInState($top_state, 'variableStart');
  setInState($top_state, 'variableEnd');


  $comment_state =& newState();

  $comment_state['class'] = $commentClass;
  $comment_state['processURLs'] = TRUE;

  if ($inCommentKeywords)
  {
    $comment_state['keywords'] = $inCommentKeywords;
  }
  $comment_state['keywordsCaseInsensitive'] = FALSE;


  if ($blockCommentStart)
  {
    $top_state['blocks'][] = $blockCommentStart;
    $top_state['end'][$blockCommentStart] = $blockCommentEnd;
    $top_state['state'][$blockCommentStart] =& $comment_state;
  }

  if ($lineCommentStart)
  {
    $top_state['blocks'][] = $lineCommentStart;
    $top_state['end'][$lineCommentStart] = "\n";
    $top_state['state'][$lineCommentStart] =& $comment_state;
  }

  return $top_state;
}


function setInState(&$state, $index, $var = FALSE)
{
  if ($var === FALSE)
  {
    $var = $index;
  }

  global $$var;

  if (isSet($$var))
  {
    $state[$index] = $$var;
  }
}


/**
 * Prepare a state block for processing.
 */
function prepareState(&$state)
{
  if (!isSet($state['name']))
  {
    $state['name'] = "topState";
  }

  makeCompositeRegex(&$state);

  foreach ($state['blocks'] as $block)
  {
    $substate =& $state['state'][$block];

    if (!isSet($substate['name']))
    {
      $substate['name'] = $block;
    }

    prepareState(&$substate);
  }
}


/**
 * Make the regex needed to spot each of the features of the language.
 */
function makeCompositeRegex(&$state)
{
  $result = "/(";
  $first = TRUE;

  if ($state['blocks'] && count($state['blocks']) > 0)
  {
    $result .= preg_alternates($state['blocks']);
    $first = FALSE;
  }

  if (count($state['keywords']) > 0)
  {
    if ($first)
    {
      $first = FALSE;
    }
    else
    {
      $result .= "|";
    }

    $result .= "(";

    if ($state['keywordStartLetters'])
    {
      $result .= "(?<![^{$state['keywordStartLetters']}])";
    }

    if ($state['keywordsCaseInsensitive'])
    {
      $result .= "(?i)";
    }

    $result .= "(?:";

    $result .= preg_alternates($state['keywords']);

    $result .= "))";

    if ($state['keywordEndLetters'])
    {
      $result .= "([{$state['keywordEndLetters']}])";
    }
  }


  if ($state['blockStringStart'])
  {
    if ($first)
    {
      $first = FALSE;
    }
    else
    {
      $result .= "|";
    }

    $result .= preg_addslashes($state['blockStringStart']);
  }

  if ($state['variableStart'])
  {
    if ($first)
    {
      $first = FALSE;
    }
    else
    {
      $result .= "|";
    }

    $result .= preg_addslashes($state['variableStart']);
  }

  if ($state['sQuotedStrings'])
  {
    if ($first)
    {
      $first = FALSE;
    }
    else
    {
      $result .= "|";
    }

    $result .= "'";
  }

  if ($state['dQuotedStrings'])
  {
    if ($first)
    {
      $first = FALSE;
    }
    else
    {
      $result .= "|";
    }

    $result .= '"';
  }

  if ($state['obfuscateEmailAddresses'])
  {
    $emailChar = "[-A-Za-z0-9!#$%&'*+\/=?^_`{|}~]";

    if ($first)
    {
      $first = FALSE;
    }
    else
    {
      $result .= "|";
    }

    $result .= "((?:$emailChar|\\.)+)@($emailChar+\\.(?:$emailChar|\\.)+)";
  }

  if ($state['processURLs'])
  {
    $urlChar = "[-A-Za-z0-9_.!~*'()%;\/?:@&=+$,]";

    if ($first)
    {
      $first = FALSE;
    }
    else
    {
      $result .= "|";
    }

    $result .= "http:\/\/($urlChar+)";
  }


  $result .= ")/";

  if (!$first)
  {
    $state['compositeRegex'] = $result;
  }


  if (DEBUG)
  {
    echo "REGEX: " . escape($result) . "\n";
  }
}


/**
 * Create a new, initialised state block.
 */
function newState()
{
  global $keywordClass;
  global $stringClass;
  global $urlClass;
  global $variableClass;

  $result = array();

  $result['obfuscateEmailAddresses'] = OBFUSCATE_EMAIL_ADDRESSES;

  $result['processURLs'] = FALSE;

  $result['keywordClass']  = $keywordClass;
  $result['stringClass']   = $stringClass;
  $result['urlClass']      = $urlClass;
  $result['variableClass'] = $variableClass;

  $result['sQuotedStrings'] = FALSE;
  $result['dQuotedStrings'] = FALSE;

  $result['keywords'] = array();
  $result['keywordsCaseInsensitive'] = FALSE;

  $result['class'] = FALSE;

  $result['blockStringStart']    = FALSE;
  $result['blockStringEnd']      = FALSE;
  $result['keywordStartLetters'] = " \t\n\r,.;:()";
  $result['keywordEndLetters']   = " \t\n\r,.;:()";
  $result['variableStart']       = FALSE;
  $result['variableEnd']         = "[ \t\n\r,;()<>]";

  $result['compositeRegex'] = FALSE;

  $result['inSString']      = FALSE;
  $result['inDString']      = FALSE;
  $result['inBlockString']  = FALSE;
  $result['inVariable']     = FALSE;
  $result['inBlock']        = FALSE;

  $result['blocks'] = array();
  $result['end'] = array();
  $result['state'] = array();

  return $result;
}


/* ** Output ** */

$lineNumber = 1;
$columnNumber = 0;
$numberNeeded = true;

/**
 * Output the given text.  Handles line padding and line numbering.  Only
 * pass this function body text in need of escaping -- in particular, do
 * not pass text containing tags.
 */
function output($text)
{
  global $columnNumber;
  global $lineNumber;
  global $numberNeeded;
  global $showLineNumbers;

  // Shortcut for speed.
  if (PAD_COLUMN < 1 && !$showLineNumbers)
  {
    echo escape($test);
    return;
  }


  $bits = explode("\n", $text);

  $i = 0;
  $n = count($bits) - 1;
  foreach ($bits as $bit)
  {
    if ($showLineNumbers && $numberNeeded)
    {
      /* The class for line numbers is called 'l'.  It saves 10% of the file
         size in some cases to use a short name over a more descriptive one.
      */
      echo '<span class="l">';

      if ($lineNumber == 1 || $lineNumber % LINE_NUMBER_MODULUS == 0)
      {
        if ($lineNumber < 10)
        {
          echo '&nbsp;&nbsp;&nbsp;';
        }
        elseif ($lineNumber < 100)
        {
          echo '&nbsp;&nbsp;';
        }
        elseif ($lineNumber < 1000)
        {
          echo '&nbsp;';
        }

        echo $lineNumber;
      }
      else
      {
        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
      }

      $numberNeeded = false;

      echo '</span>';
    }

    echo escape($bit);

    $columnNumber += strlen($bit);

    if ($i != $n)
    {
      while ($columnNumber < PAD_COLUMN)
      {
        echo ' ';
        $columnNumber++;
      }

      echo "\n";
      $lineNumber++;
      $columnNumber = 0;
      $numberNeeded = true;
    }

    $i++;
  }
}


function anchor($url, $text)
{
  return "<a href='$url'>" . escape($text) . "</a>";
}


function outputAnchor($url)
{
  echo "<a href='$url'>";
  output($url);
  echo "</a>";
}


function newClass($class)
{
  echo "<span class='$class'>";
}


function endClass()
{
  echo '</span>';
}


function transitionMeta()
{
  global $transition;

  return
    $transition == -1 ?
    "" :
    "<meta http-equiv='Page-Enter'
           content='revealTrans(duration=\"1\",transition=\"$transition\")' />
";
}


/* ** Helpers ** */


function escape($text)
{
  return htmlentities($text, ENT_NOQUOTES);
}


/**
 * Implementation of str_rot13 for PHP versions <4.2.0.
 */
if (!function_exists('str_rot13'))
{
  function str_rot13($str)
  {
    $from = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $to   = 'nopqrstuvwxyzabcdefghijklmNOPQRSTUVWXYZABCDEFGHIJKLM';
    
    return strtr($str, $from, $to);
  }
}


function preg_alternates($alts)
{
  $result = "";
  $first = TRUE;

  foreach ($alts as $alt)
  {
    if ($first)
    {
      $first = FALSE;
    }
    else
    {
      $result .= "|";
    }

    $result .= preg_addslashes($alt);
  }

  return $result;
}


/**
 * By kevin.bro@hostedstuff.com on
 * http://uk.php.net/manual/en/function.preg-match.php.
 */
function preg_addslashes($foo)
{
   return preg_replace("/([^A-z0-9_-]|[\\\[\]])/", "\\\\\\1", $foo);
}


function startsWith($haystack, $needle)
{
  return 0 == strncmp($haystack, $needle, strlen($needle));
}


function endsWith($haystack, $needle)
{
  return substr($haystack, strlen($haystack) - strlen($needle)) === $needle;
}


function usage()
{
  echo "
{$_SERVER['argv'][0]} <filename> [<options>]

where
    <filename> is the name of the script to process; and
    <options>  can be any of

       file=<filename> where <filename> overrides any filename given earlier
                             on the command line;
       dir=<directory> where <directory> specifies the directory where the 
                             CHIP CSS and JavaScript files are held (defaults
                             to the empty string);
       title=<title>   where <title> is the title given to the output page
                             (defaults to <filename>);
       transition=<n>  where <n> is a code for the page-entry transition,
                             or -1 to disable transitions (defaults to " .
                             DEFAULT_TRANSITION . ");
       linenumbers=<n> where <n> is 0 to disable line numbers, or 1 to enable
                             them (defaults to " .
                             (DEFAULT_SHOW_LINE_NUMBERS ?
                                "enabled" :
                                "disabled") . ");
       bcURL0=<url> and bcTitle0=<title> giving the URL and title of the first
                                         breadcrumb, with bcURL1 and bcTitle1
                                         giving the URL and title of the
                                         second breadcrumb, etc.
";
}


/* ** Parameter Processing ** */

if (isSet($_SERVER['argc']))
{
  $args = array();

  $n = 1;
  while (isSet($_SERVER['argv'][$n]))
  {
    $nameval = $_SERVER['argv'][$n];

    if ($nameval == "--")
    {
      continue;
    }

    if (startsWith($nameval, "-h") ||
        startsWith($nameval, "--h"))
    {
      usage();
      exit;
    }

    $parts = explode('=', $nameval);

    if (isSet($parts[1]))
    {
      $args[$parts[0]] = $parts[1];
    }
    else
    {
      $args['file'] = $parts[0];
    }

    $n++;
  }

  if (isSet($args['file']))
  {
    $path = $args['file'];
  }
  else
  {
    usage();
    exit;
  }

  $dir =
    isSet($args['dir']) ?
      $args['dir'] :
      "";

  $urlprefix = "";
}
else
{
  $args = $_GET;

  if (isSet($args['file']))
  {
    $path = $args['file'];

    if ($path[0] != "/")
    {
      $path = "/$path";
    }
  }
  else
  {
    header("HTTP/1.0 404 Not Found");
    exit;
  }

  $dir = dirname($_SERVER['SCRIPT_NAME']);
  if ($dir != "/")
  {
    $dir .= "/";
  }

  $urlprefix = "http://{$_SERVER['SERVER_NAME']}";
}


$filename = basename($path);


$title =
  isSet($args['title']) ? 
    $args['title'] :
    $filename;


$transition =
  isSet($args['transition']) ?
    $args['transition'] :
    DEFAULT_TRANSITION;


if (isSet($args['linenumbers']))
{
  $l = $args['linenumbers'];
  $showLineNumbers = ($l == "1");
}
else
{
  $showLineNumbers = DEFAULT_SHOW_LINE_NUMBERS;
}


$breadcrumbURLs   = array();
$breadcrumbTitles = array();
$i = 0;
while (isSet($args["bcURL$i"]))
{
  $u = $args["bcURL$i"];
  $t = isSet($args["bcTitle$i"]) ? $args["bcTitle$i"] : $u;

  if ($u[0] != "/")
  {
    $u = "/$u";
  }

  $breadcrumbURLs[$i]   = $u;
  $breadcrumbTitles[$i] = $t;

  $i++;
}


$done = false;
foreach ($behaviourMap as $ext => $lang)
{
  if (endsWith($filename, $ext))
  {
    require "chip-$lang.php";
    $done = true;
    break;
  }
}

if (!$done)
{
  header("HTTP/1.0 404 Not Found");
  echo "No formatting module for '$path' found.\n";
  exit;
}

$url = "$urlprefix$path";

set_magic_quotes_runtime(false);
@$fd = fopen("$url", "rb");

if ($fd === FALSE)
{
  header("HTTP/1.0 404 Not Found");
  echo "$url not found\n";
  exit;
}


/* ** The Output ** */

header("Content-type: text/html");

?>
<!DOCTYPE html PUBLIC
               "-//W3C//DTD XHTML 1.0 Strict//EN"
               "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php echo $title; ?></title>
<?php echo transitionMeta(); ?>
<link rel="stylesheet" href="<?php echo $dir; ?>chip.css" title="CHIP"
      type="text/css" />
<!--[if IE]>
<link href="<?php echo $dir; ?>chip-ie.css" rel="stylesheet" type="text/css"
      media="screen">
<![endif]-->
<script type="text/javascript" src="<?php echo $dir; ?>rot13.js"></script>
<script type="text/javascript" src="<?php echo $dir; ?>chip.js"></script>
<script type="text/javascript">
// <!--

<?php

$ppat = plainPageAnchorText();

echo <<<EOS
chipFilePath = '$path';
chipPlainPageAnchorText = '$ppat';
chipEmailLabelPrefix = '$emailLabelPrefix';
chipTopLinkLabel = '$topLinkLabel';
chipBottomLinkLabel = '$bottomLinkLabel';

EOS;

?>

  function chipOnload()
  {
<?php
    if (DISABLE_JAVASCRIPT)
    {
      echo "return;\n";
    }
?>

    chipFixRotEmails();
    chipFixLinks();
  }

// -->
</script>
</head>
<body onload="javascript:chipOnload()">
<?php echo pageHeader(); ?>
<pre>
<?php process(); ?>
</pre>
<?php echo pageFooter(); ?>
</body>
</html>
