<?php

/////////////////////////////////////////////////
// DIFF functions adapted from:
// PukiWiki - Yet another WikiWikiWeb clone.
// http://www.pukiwiki.org (GPL'd)
/*
WikiDiffer

S. Wu, <a href="http://www.cs.arizona.edu/people/gene/vita.html">
E. Myers,</a> U. Manber, and W. Miller,
<a href="http://www.cs.arizona.edu/people/gene/PAPERS/np_diff.ps">
"An O(NP) Sequence Comparison Algorithm,"</a>
Information Processing Letters 35, 6 (1990), 317-323.
*/
class WikiDiffLine
{
    var $text;
    var $status;
    var $who; // who originally wrote this line?

    function __construct($text, $who = null)
    {
        $this->text = "$text\n";
        $this->who = $who;
        $this->status = [];
    }
    function compare($obj)
    {
        return $this->text == $obj->text;
    }
    function set($key,$status)
    {
        $this->status[$key] = $status;
    }
    function get($key)
    {
        return array_key_exists($key,$this->status) ? $this->status[$key] : '';
    }
    function merge($obj)
    {
        $this->status += $obj->status;
    }
    function text()
    {
        return $this->text;
    }
}
