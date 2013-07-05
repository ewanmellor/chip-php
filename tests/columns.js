/*

columns.js.  Version 1.4.

Copyright (c) 2003, 2004 Ewan Mellor.  All rights reserved.

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


Dynamic single or double column text flow.  This module flows text in either
one column or two, depending upon the horizontal space available in the
window.  It keeps the columns a comfortable width for reading, and places a
margin to the left hand side if there is room.  If there is room for only one
column, the text from the second column is placed immediately following the
first.  Sections of text may be marked as flowing across both columns, and
unmarked text will flow across the entire page regardless of the arrangement
of columns.


This file may be obtained from <http://www.ewanmellor.org.uk/javascript/>.

This file depends upon sniffer.js by Eric Krok, Andy King, and Michel
Plungjan.

To use this file, call columns() from your page's onload and onresize event
handlers.  Content should be placed within div elements with IDs
"bigcontentN", and thence inside divs with IDs "leftcontentN" and
"rightcontentN".  The N should be an integer, starting at 0 and incrementing
for each separate column block.  The leftcontentN and rightcontentN divs mark
the left and right-hand columns respectively.  Alternatively, text may be
inside an element with ID "widecontentN", which marks it as flowing across
both columns.

*/


function columns()
{
  var pixelsWidePerPointHigh = 0.58;

  var bodyMargin = 20;

  var desiredMargin = 5; // en (ish)
  var desiredCharsAcross = 72; // chars

  var points = baseTextPoints();

  var desiredContentMargin = Math.floor(
    desiredMargin * pixelsWidePerPointHigh * points); // px
  var desiredContentWidth = Math.floor(
    desiredCharsAcross * pixelsWidePerPointHigh * points); // px

  var dw =
    document.documentElement ? 
    (document.documentElement.clientWidth ? 
     document.documentElement.clientWidth :
     document.documentElement.offsetWidth - 20) :
    window.innerWidth ?
    window.innerWidth :
    document.width ?
    document.width :
    document.body ?
    document.body.clientWidth :
    desiredContentWidth;

  dw = dw - bodyMargin;

  var chosenBigContentWidth;
  var chosenContentWidth;
  var chosenContentMargin;
  var chosenCentreContentMargin;
  var chosenWideContentWidth;
  var chosenWideContentMargin;
  var twoColumns;

  var hackedMarginFactor = is_opera || is_moz ? 1 : 2;

  var hackedDesiredContentMargin =
    Math.floor(desiredContentMargin / hackedMarginFactor);

  if (dw < desiredContentWidth)
  {
    // Less than enough room for one column.  Thus, one narrow column.

    chosenBigContentWidth = "100%";
    chosenContentWidth = "100%";
    chosenContentMargin = "0px";
    chosenWideContentWidth = "100%";
    chosenWideContentMargin = "0px";
    twoColumns = false;
  }
  else if (dw < desiredContentWidth + desiredContentMargin)
  {
    // Room for one full column, but with a less-than-full left margin.

    chosenBigContentWidth = "100%";
    chosenContentWidth = desiredContentWidth + "px";
    chosenContentMargin =
      ((dw - desiredContentWidth) / hackedMarginFactor) + "px";
    chosenWideContentWidth = desiredContentWidth + "px";
    chosenWideContentMargin = chosenContentMargin;
    twoColumns = false;
  }
  else if (dw < desiredContentWidth * 2 + desiredContentMargin)
  {
    // Less than enough for two full content columns with a centre margin.
    // Thus, one full column and a full left margin.

    chosenBigContentWidth =
      desiredContentWidth + desiredContentMargin + "px";
    chosenContentWidth = desiredContentWidth + "px";
    chosenContentMargin = hackedDesiredContentMargin + "px";
    chosenWideContentWidth = desiredContentWidth + "px";
    chosenWideContentMargin = desiredContentMargin + "px";
    twoColumns = false;
  }
  else if (dw < (desiredContentWidth + desiredContentMargin) * 2)
  {
    // Less than enough for two columns with full left and centre margins,
    // thus two full content columns with a full centre but a less than full
    // left margin.

    chosenBigContentWidth = "100%";
    chosenContentWidth = desiredContentWidth + "px";
    chosenContentMargin =
      ((dw - desiredContentWidth * 2 -
        desiredContentMargin) / hackedMarginFactor) + "px";
    chosenCentreContentMargin = desiredContentMargin + "px";
    chosenWideContentWidth = (desiredContentWidth * 2) + "px";
    chosenWideContentMargin = 
      (dw - desiredContentWidth * 2 - desiredContentMargin) + "px";
    twoColumns = true;
  }
  else
  {
    // Room for two full columns and full left and centre margins.

    chosenBigContentWidth =
      (desiredContentWidth + desiredContentMargin) * 2 + "px";
    chosenContentWidth = desiredContentWidth + "px";
    chosenContentMargin = hackedDesiredContentMargin + "px";
    chosenCentreContentMargin = desiredContentMargin + "px";
    chosenWideContentWidth =
      (desiredContentWidth * 2 + desiredContentMargin) + "px";
    chosenWideContentMargin = desiredContentMargin + "px";
    twoColumns = true;
  }

  if (twoColumns)
  {
    apply("leftcontent",  chosenContentWidth,
          "0 0 0 " + chosenContentMargin, "left");
    apply("rightcontent", chosenContentWidth,
          "0 0 0 " + chosenCentreContentMargin, "right");
  }
  else
  {
    apply("leftcontent", chosenContentWidth,
          "0 0 0 " + chosenContentMargin, "right");

    apply("rightcontent", chosenContentWidth,
          "1em 0 0 " + chosenContentMargin, "right");
  }

  apply("bigcontent", chosenBigContentWidth, "0 0 0 0", "none");

  apply("widecontent", chosenWideContentWidth,
        "0 0 0 " + chosenWideContentMargin, "none");
}


function apply(id, width, leftMargin, floats)
{
  var i = 0;
  do
  {
    var element = document.getElementById(id + i);

    if (element)
    {
      element.style.width = width;
      element.style.margin = leftMargin;
      element.style.padding = "0 0 0 0";
      element.style.visibility = "visible";
      element.style["float"] = floats;
    }
    else
    {
      break;
    }
    i++;
  } while (true);
}


/**
 * @return The size, in points, of the base typeface, or a guess at that
 * value.
 */
function baseTextPoints()
{
  var els = document.getElementsByTagName("p");

  if (els)
  {
    var topP = els[0];

    return parseInt(
      topP.currentStyle ?
      topP.currentStyle.fontSize :
      document.defaultView && document.defaultView.getComputedStyle ?
      document.defaultView.getComputedStyle(topP, null)
      .getPropertyValue("font-size") :
      12);
  }
  else
  {
    return 12;
  }
}
