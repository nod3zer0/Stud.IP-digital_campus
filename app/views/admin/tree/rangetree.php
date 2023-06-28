<div data-studip-tree>
    <studip-tree start-id="<?= htmlReady($startId) ?>" view-type="table" breadcrumb-icon="institute"
                 :with-search="false" :visible-children-only="false"
                 :editable="true" edit-url="<?= $controller->url_for('admin/tree/edit') ?>"
                 create-url="<?= $controller->url_for('admin/tree/create') ?>"
                 delete-url="<?= $controller->url_for('admin/tree/delete') ?>"
                 :with-courses="true" semester="<?= htmlReady($semester) ?>" :show-structure-as-navigation="true"
                 title="<?= _('Einrichtungshierarchie bearbeiten') ?>"></studip-tree>
</div>
