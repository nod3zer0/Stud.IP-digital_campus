<?= $this->render_partial('search/breadcrumb') ?>
<h2><?= htmlReady($studiengang->getDisplayName()) ?></h2>
<h3><?= _('Ausprägungen') ?></h3>
<ul class="mvv-result-list">
<? foreach ($data as $fach_id => $fach) : ?>
    <li>
        <a href="<?= $controller->link_for($verlauf_url, $fach_id) ?>"><?= htmlReady($fach) ?></a>
    </li>
<? endforeach; ?>
</ul>
