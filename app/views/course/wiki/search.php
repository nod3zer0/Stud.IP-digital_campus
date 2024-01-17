<?php
/**
 * @var WikiPage[] $pages
 * @var string $edit_perms
 * @var Course_WikiController $controller
 */
?>

<? if (count($pages)) : ?>

    <table class="default">
        <caption>
            <?= sprintf(_('Treffer für Suche nach <em>%s</em> in allen Versionen'), htmlReady(Request::get('search'))) ?>
        </caption>
        <thead>
        <tr>
            <th><?= _('Seite') ?></th>
            <th><?= _('Treffer') ?></th>
            <th><?= _('Datum') ?></th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($pages as $page_id => $pagedata) : ?>
            <tr>
                <td>
                    <?
                    sort($pagedata['versions'], SORT_NUMERIC);
                    $pagedata['versions'] = array_reverse($pagedata['versions']);
                    if ($pagedata['is_in_content']) {
                        $content = $pagedata['page']->content;
                    } else if ($pagedata['is_in_history']) {
                        $version = WikiVersion::find($pagedata['versions'][0]);
                        $content = $version->content;
                    }
                    ?>
                    <a href="<?= $pagedata['is_in_content'] || $pagedata['is_in_name']
                        ? $controller->page($page_id)
                        : $controller->version($version) ?>">
                        <?= htmlReady($pagedata['page']->name) ?>
                        <? if (!$pagedata['is_in_content'] && !$pagedata['is_in_name']) : ?>
                            <span><?= _('Nur in alter Version der Seite enthalten.') ?></span>
                        <? endif ?>
                    </a>
                </td>
                <td>
                    <?
                    $content = Studip\Markup::removeHtml($content);
                    $offset  = 0;
                    $output  = [];

                    // find all occurences
                    while ($offset < mb_strlen($content)) {
                        $pos = mb_stripos($content, Request::get('search'), $offset);
                        if ($pos === false) {
                            break;
                        }
                        $offset = $pos + 1;
                        if (($ignore_next_hits--) > 0) {
                            // if more than one occurence is found
                            // in a fragment to be displayed,
                            // the fragment is only shown once
                            continue;
                        }
                        // show max 80 chars
                        $fragment       = '';
                        $split_fragment = preg_split('/(' . preg_quote(Request::get('search'), '/') . ')/i', mb_substr($content, max(0, $pos - 40), 80), -1, PREG_SPLIT_DELIM_CAPTURE);
                        for ($i = 0; $i < count($split_fragment); ++$i) {
                            if ($i % 2) {
                                $fragment .= '<span class="wiki_highlight">';
                                $fragment .= htmlready($split_fragment[$i], false);
                                $fragment .= '</span>';
                            } else {
                                $fragment .= htmlready($split_fragment[$i], false);
                            }
                        }
                        $found_in_fragment = (count($split_fragment) - 1) / 2; // number of hits in fragment
                        $ignore_next_hits  = ($found_in_fragment > 1) ? $found_in_fragment - 1 : 0;
                        $output[]          = "..." . $fragment . "...";
                    }
                    if ($pagedata['is_in_name']) {
                        $name = str_ireplace(Request::get('search'), '<span class="wiki_highlight">' . htmlReady(Request::get('search')) . '</span>', htmlReady($pagedata['page']->name));
                        array_unshift($output, sprintf(_('Treffer im Namen: %s'), $name));
                    } else if ($pagedata['is_in_old_name']) {
                        $name = str_ireplace(Request::get('search'), '<span class="wiki_highlight">' . htmlReady(Request::get('search')) . '</span>', htmlReady($version->name));
                        array_unshift($output, sprintf(_('Treffer in alten Namen: %s'), $name));
                    }
                    echo implode('<br>', $output);
                    ?>
                </td>
                <td>
                    <? if ($pagedata['is_in_content'] || $pagedata['is_in_name']) : ?>
                        <?= _('Aktuelle Version') . ': ' . ($pagedata['page']->chdate ? date('d.m.Y H:i:s', $pagedata['page']->chdate) : _('unbekannt')) ?>
                    <? else : ?>
                        <?= $version->chdate > 0 ? date('d.m.Y H:i:s', $version->chdate) : _('unbekannt') ?>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? else : ?>
    <?= MessageBox::info(sprintf(_('Ihre Suche nach <em>%s</em> ergab keine Treffer.'), htmlReady(Request::get('search')))) ?>
<? endif ?>
