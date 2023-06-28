<div data-studip-tree>
    <studip-tree start-id="<?= htmlReady($startId) ?>" view-type="table" breadcrumb-icon="literature"
                 :with-search="false" :visible-children-only="false"
                 :editable="true" edit-url="<?= $controller->url_for('admin/tree/edit') ?>"
                 create-url="<?= $controller->url_for('admin/tree/create') ?>"
                 delete-url="<?= $controller->url_for('admin/tree/delete') ?>"
                 :show-structure-as-navigation="true" :with-course-assign="true"
                 :with-courses="true" semester="<?= htmlReady($semester) ?>"
                 title="<?= _('Veranstaltungshierarchie bearbeiten') ?>"></studip-tree>
</div>
