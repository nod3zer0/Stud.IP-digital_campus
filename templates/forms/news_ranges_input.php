<editable-list name="<?= htmlReady($this->name) ?>"
               quicksearch="<?= htmlReady((string) $searchtype) ?>"
               :items="<?= htmlReady(json_encode($items)) ?>"
               :selectable="<?= htmlReady(json_encode($selectable)) ?>"
               :category_order="<?= htmlReady(json_encode($category_order)) ?>"
               :required="STUDIPFORM_REQUIRED.indexOf('<?= htmlReady($this->name) ?>') !== -1"
               label="<?= htmlReady($this->title) ?>"
               @input="output => <?= htmlReady($this->name) ?> = output">
</editable-list>
