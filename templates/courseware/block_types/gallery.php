<? foreach ($files as $file): ?>
    <? if ($file === null) { continue; } ?>
    <p>
        <img src="<?= htmlReady($file->getDownloadURL()); ?>">
        <span style="font-style: italic; font-size: 0.75em"><?= htmlReady($file->name); ?></span>
    </p>
<? endforeach; ?>