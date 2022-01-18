<div class="cw-bookmarks">
    <? if(!empty($bookmarks)): ?>
    <ul class="cw-tiles">
        <? foreach($bookmarks as $bookmark) :?>
            <li class="tile <?= htmlReady($bookmark['element']['payload']['color'])?>">
                <a href="<?= htmlReady($bookmark['url'])?>">
                    <? if ($element->getImageUrl() === null) : ?>
                        <div class="preview-image default-image"></div>
                    <? else : ?>
                        <div class="preview-image" style="background-image: url(<?= htmlReady($element->getImageUrl()) ?>)" ></div>
                    <? endif; ?>

                    <div class="description">
                        <header><?= htmlReady($bookmark['element']['title']) ?></header>
                        <div class="description-text-wrapper">
                            <p><?= htmlReady($bookmark['element']['payload']['description']) ?></p>
                        </div>
                        <footer>
                        <? if($bookmark['course']): ?>
                            <?= Icon::create('seminar', Icon::ROLE_INFO_ALT)?> <?= htmlReady($bookmark['course']['name'])?>
                        <? endif; ?>
                        <? if($bookmark['user']): ?>
                            <?= Icon::create('headache', Icon::ROLE_INFO_ALT)?> <?= htmlReady($bookmark['user']->getFullName())?>
                        <? endif; ?>
                        </footer>
                    </div>
                </a>
            </li>
        <? endforeach; ?>
    </ul>
    <? else: ?>
        <?= MessageBox::info(_('Sie haben noch keine Lesezeichen angelegt.')); ?>
    <? endif; ?>
</div>
