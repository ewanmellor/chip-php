/*

CHIP: Code Highlighting in PHP.

Copyright (c) 2004-2005 Ewan Mellor <chip@ewanmellor.org.uk>.  All rights
reserved.

*/


function chipInnerText(el)
{
  return el.innerText ? el.innerText : el.childNodes[0].nodeValue;
}


function chipFixRotEmails()
{
  if (document.getElementById)
  {
    var i = 0;
    while (true)
    {
      var el = document.getElementById(chipEmailLabelPrefix + i);

      if (!el)
      {
        break;
      }

      var inner = chipInnerText(el);

      var re = /^(.*) at (.*)$/;
      var matches = inner.match(re);

      var address = rot13(matches[1]) + '@' + rot13(matches[2]);

      var new_a_el = document.createElement("a");
      new_a_el.href = "mailto:" + address;

      new_a_el.appendChild(document.createTextNode(address));

      el.replaceChild(new_a_el, el.childNodes[0]);

      i++;
    }
  }
}


function chipFileAnchor()
{
  var new_a_el  = document.createElement("a");
  new_a_el.href = chipFilePath;
  new_a_el.appendChild(document.createTextNode(chipPlainPageAnchorText));

  return new_a_el;
}


function chipFixLink(id)
{
  var el = document.getElementById(id);

  if (el)
  {
    el.replaceChild(chipFileAnchor(), el.childNodes[0]);
  }
}


function chipFixLinks()
{
  if (document.getElementById)
  {
    chipFixLink(chipTopLinkLabel);
    chipFixLink(chipBottomLinkLabel);
  }
}
