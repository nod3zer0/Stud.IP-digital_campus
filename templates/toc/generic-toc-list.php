<input type="checkbox" id="cb-toc-close"/>
<article class="toc_overview toc_transform" id="toc">
    <header id="toc_header">
        <h1 id="toc_h1"><?= sprintf(_('Inhalt (%u Elemente)'), htmlReady($root->countAllChildren())) ?></h1>
        <label for="cb-toc" class="check-box" title="<?= _('Schließen')?>">
            <?= Icon::create('decline')->asImg(24) ?>
        </label>
    </header>
    <section>
        <ul class="toc">
            <?= $this->render_partial('toc/_toc-item-list', ['item' => $root]) ?>
        </ul>
    </section>
</article>
