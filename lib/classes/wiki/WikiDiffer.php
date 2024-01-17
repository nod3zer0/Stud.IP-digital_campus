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

class WikiDiffer
{
    var $arr1,$arr2,$m,$n,$pos,$key,$plus,$minus,$equal,$reverse,$result,$path;
    var $add_count;
    var $delete_count;

    static public function toDiffLineArray($lines, $who = null) {
        $dla = [];
        $lines = Studip\Markup::removeHtml($lines);
        $lines = explode("\n",preg_replace("/\r/",'', $lines));
        foreach ($lines as $l) {
            $dla[] = new WikiDiffLine($l, $who);
        }
        return $dla;
    }

    static public function doDiff($strlines1, $strlines2)
    {
        $minus = '<div class="wiki_added" title="'._('Dieser Text wurde hinzugefügt').'"></div>';
        $plus  = '<div class="wiki_erased" title="'._('Dieser Text wurde gelöscht bzw. ersetzt.').'"></div>';
        $equal = '<div class="wiki_equal"></div>';
        $obj = new WikiDiffer($plus, $minus, $equal);
        $str = $obj->str_compare($strlines1,$strlines2);
        return $str;
    }

    function __construct($plus='+',$minus='-',$equal='=')
    {
        $this->plus = $plus;
        $this->minus = $minus;
        $this->equal = $equal;
    }
    function arr_compare($key,$arr1,$arr2)
    {
        $this->key = $key;
        $this->arr1 = $arr1;
        $this->arr2 = $arr2;
        $this->compare();
        $arr = $this->toArray();
        return $arr;
    }
    function set_str($key,$str1,$str2)
    {
        $this->key = $key;
        $this->arr1 = [];
        $this->arr2 = [];
        $str1 = preg_replace("/\r/",'',$str1);
        $str2 = preg_replace("/\r/",'',$str2);
        foreach (explode("\n",$str1) as $line)
        {
            $this->arr1[] = new WikiDiffLine($line);
        }
        foreach (explode("\n",$str2) as $line)
        {
            $this->arr2[] = new WikiDiffLine($line);
        }
    }
    function str_compare($str1, $str2, $show_equal=FALSE)
    {
        $this->set_str('diff',$str1,$str2);
        $this->compare();

        $str = '';
        $lastdiff = "";
        $textaccu = "";
        $template = "<div class='wiki_diff'>%s<div>%s</div></div>";
        foreach ($this->toArray() as $obj)
        {
            if ($show_equal || $obj->get('diff') != $this->equal) {
                if ($lastdiff && ($obj->get("diff") != $lastdiff) && trim($textaccu)) {
                    $str .= sprintf($template, $lastdiff, wikiReady($textaccu));
                    $textaccu="";
                }
                $textaccu .= $obj->text();
                $lastdiff = $obj->get("diff");
            }
        }
        if (trim($textaccu)) {
            $str .= sprintf($template, $lastdiff, wikiReady($textaccu));
        }
        return $str;
    }
    function compare()
    {
        $this->m = count($this->arr1);
        $this->n = count($this->arr2);

        if ($this->m == 0 or $this->n == 0) // no need compare.
        {
            $this->result = [['x'=>0,'y'=>0]];
            return;
        }

        // sentinel
        array_unshift($this->arr1,new WikiDiffLine(''));
        $this->m++;
        array_unshift($this->arr2,new WikiDiffLine(''));
        $this->n++;

        $this->reverse = ($this->n < $this->m);
        if ($this->reverse) // swap
        {
            $tmp = $this->m; $this->m = $this->n; $this->n = $tmp;
            $tmp = $this->arr1; $this->arr1 = $this->arr2; $this->arr2 = $tmp;
            unset($tmp);
        }

        $delta = $this->n - $this->m; // must be >=0;

        $fp = [];
        $this->path = [];

        for ($p = -($this->m + 1); $p <= ($this->n + 1); $p++)
        {
            $fp[$p] = -1;
            $this->path[$p] = [];
        }

        for ($p = 0;; $p++)
        {
            for ($k = -$p; $k <= $delta - 1; $k++)
            {
                $fp[$k] = $this->snake($k, $fp[$k - 1], $fp[$k + 1]);
            }
            for ($k = $delta + $p; $k >= $delta + 1; $k--)
            {
                $fp[$k] = $this->snake($k, $fp[$k - 1], $fp[$k + 1]);
            }
            $fp[$delta] = $this->snake($delta, $fp[$delta - 1], $fp[$delta + 1]);
            if ($fp[$delta] >= $this->n)
            {
                $this->pos = $this->path[$delta]; //
                return;
            }
        }
    }
    function snake($k, $y1, $y2)
    {
        if ($y1 >= $y2)
        {
            $_k = $k - 1;
            $y = $y1 + 1;
        }
        else
        {
            $_k = $k + 1;
            $y = $y2;
        }
        $this->path[$k] = $this->path[$_k];//
        $x = $y - $k;
        while ((($x + 1) < $this->m) and (($y + 1) < $this->n)
            and $this->arr1[$x + 1]->compare($this->arr2[$y + 1]))
        {
            $x++; $y++;
            $this->path[$k][] = ['x'=>$x,'y'=>$y]; //
        }
        return $y;
    }
    function toArray()
    {
        $arr = [];
        if ($this->reverse) //
        {
            $_x = 'y'; $_y = 'x'; $_m = $this->n; $arr1 =& $this->arr2; $arr2 =& $this->arr1;
        }
        else
        {
            $_x = 'x'; $_y = 'y'; $_m = $this->m; $arr1 =& $this->arr1; $arr2 =& $this->arr2;
        }

        $x = $y = 1;
        $this->add_count = $this->delete_count = 0;
        $this->pos[] = ['x'=>$this->m,'y'=>$this->n]; // sentinel
        foreach ($this->pos as $pos)
        {
            $this->delete_count += ($pos[$_x] - $x);
            $this->add_count += ($pos[$_y] - $y);

            while ($pos[$_x] > $x)
            {
                $arr1[$x]->set($this->key, $this->minus);
                $arr[] = $arr1[$x++];
            }

            while ($pos[$_y] > $y)
            {
                $arr2[$y]->set($this->key, $this->plus);
                $arr[] =  $arr2[$y++];
            }

            if ($x < $_m)
            {
                $arr1[$x]->merge($arr2[$y]);
                $arr1[$x]->set($this->key,$this->equal);
                $arr[] = $arr1[$x];
            }
            $x++; $y++;
        }
        return $arr;
    }
}
