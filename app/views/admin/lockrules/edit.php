<?php
# Lifter010: TODO

/**
 * @var Admin_LockrulesController $controller
 * @var LockRule $lock_rule
 */
?>
<?= $this->render_partial('admin/lockrules/_form.php', ['action' => $controller->url_for('admin/lockrules/edit/' . $lock_rule->getId())]);
