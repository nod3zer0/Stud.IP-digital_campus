<tr>
    <td data-sort-value="<?= htmlReady(is_a($version, WikiPage::class) ? $version->name : $version->page->name) ?>">
        <a href="<?= is_a($version, WikiPage::class) ? $controller->page($version) : $controller->version($version) ?>">
            <?= htmlReady(is_a($version, WikiPage::class) ? $version->name : $version->page->name) ?>
        </a>
    </td>
    <td>
        <?
        $oldversion = $version->predecessor ? $version->predecessor->content : '';
        $oldcontent = strip_tags(wikiReady($oldversion));
        $content = strip_tags(wikiReady($version->content));
        while ($content && $oldcontent && $content[0] == $oldcontent[0]) {
            $content = substr($content, 1);
            $oldcontent = substr($oldcontent, 1);
        }
        while ($content && $oldcontent && $content[strlen($content) - 1] == $oldcontent[strlen($oldcontent) - 1]) {
            $content = substr($content, 0, -1);
            $oldcontent = substr($oldcontent, 0, -1);
        }
        if ($content) {
            echo nl2br(htmlReady($content));
        } elseif ($oldcontent) {
            echo _('Gelöscht') . ': ' . nl2br(htmlReady($oldcontent));
        } else {
            echo nl2br(strip_tags(wikiReady($version->content)));
        }

        ?></td>
    <? $user = User::find($version->user_id) ?>
    <td data-sort-value="<?= htmlReady($user ? $user->getFullName() : _('unbekannt')) ?>">
        <?
        if ($user) {
            echo Avatar::getAvatar($user->id)->getImageTag(Avatar::SMALL);
            echo ' ';
            echo htmlReady($user->getFullName());
        } else {
            echo _('unbekannt');
        }
        ?></td>
    <td data-sort-value="<?= htmlReady(is_a($version, WikiPage::class) ? $version->chdate : $version->mkdate) ?>">
        <? $chdate = is_a($version, WikiPage::class) ? $version->chdate : $version->mkdate ?>
        <?= $chdate > 0 ? date('d.m.Y H:i:s', $chdate) : _('unbekannt') ?>
    </td>
</tr>
