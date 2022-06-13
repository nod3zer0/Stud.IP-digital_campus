<editable-list name="<?= htmlReady($this->name) ?>"
               quicksearch="<?= htmlReady((string) $searchtype) ?>"
               :items="<?= htmlReady(json_encode($items)) ?>"
               :selectable="<?= htmlReady(json_encode($selectable)) ?>"
               :category_order="<?= htmlReady(json_encode($category_order)) ?>"
               @input="output => <?= htmlReady($this->name) ?> = output">
</editable-list>
