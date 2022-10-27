<?php
/**
 * @var boolean $enabled
 * @var array $types
 * @var array $config
 * @var string $cache
 */
?>
<? if ($enabled) : ?>
    <div id="cache-admin-container">
        <cache-administration :cache-types='<?= htmlReady(json_encode($types)) ?>' current-cache="<?= htmlReady($cache) ?>"
                     :current-config='<?= htmlReady(json_encode($config)) ?>'></cache-administration>
    </div>
<? endif;
