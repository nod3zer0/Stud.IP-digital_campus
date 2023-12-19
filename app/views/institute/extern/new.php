<?php
/**
 * @var ExternController $controller
 * @var array $config_types
 */
?>

<ul class="content-items" style="padding-top: 10px;">
    <? foreach ($config_types as $type_id => $config_type) : ?>
        <li class="content-item">
            <a class="content-item-link" href="<?= $controller->edit($type_id) ?>">
                <div class="content-item-img-wrapper">
                    <?= Icon::create($config_type['icon'])->asImg(64) ?>
                </div>
                <div class="content-item-text">
                    <p class="content-item-title">
                        <?= htmlReady($config_type['name']) ?>
                    </p>
                    <p class="content-item-description">
                        <?= htmlReady($config_type['description']) ?>
                    </p>
                </div>
            </a>
        </li>
    <? endforeach ?>
</ul>
