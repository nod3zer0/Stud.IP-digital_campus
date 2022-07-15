<dl>
    <dt><?= _('Name') ?></dt>
    <dl><?= htmlReady($application['name']) ?></dl>

    <dt><?= _('Beschreibung') ?></dt>
    <dl><?= htmlReady($application['description']) ?></dl>

    <dt><?= _('Von wem wird der OAuth2-Client entwickelt?') ?></dt>
    <dl>
        <a rel="noreferrer noopener" target="_blank"
            href="<?= htmlReady($application['homepage']) ?>">
            <?= htmlReady($application['owner']) ?>
        </a>
    </dl>

    <dt><?= _('Berechtigungen') ?></dt>
    <dd>
        <ul>
            <? foreach ($application['scopes'] as $scope) { ?>
                <li><?= htmlReady($scope->description) ?></li>
            <? } ?>
        </ul>
    </dd>
</dl>
