<template>
    <div class="mpscontainer studip-msp-vue">
        <form method="post" class="default" @submit.prevent="search">
            <label class="with-action">
                <input type="text" ref="searchInputField" v-model="searchTerm" :placeholder="$gettext('Suchen')" style="width: 260px;">
                <a href="#" class="msp-btn" @click.prevent="search" :title="$gettext('Suche starten')">
                    <studip-icon shape="search" role="clickable" size="16"></studip-icon>
                </a>
                <a href="#" class="msp-btn" @click.prevent="resetSearch" :title="$gettext('Suche zurücksetzen')">
                    <studip-icon shape="decline" role="clickable" size="16"></studip-icon>
                </a>
            </label>
            <select multiple="multiple" :id="select_box_id" name="selectbox[]"></select>
        </form>
    </div>
</template>

<script>
export default {
    name: 'studip-multi-person-search',
    props: {
        name: String,
        withDetail: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            searchTerm: '',
            count: 0,
            users: []
        }
    },
    mounted () {
        this.$nextTick(() => {
            this.init();
            setTimeout(() => {
                this.$refs.searchInputField.focus();
            }, 100);
        });
    },
    computed: {
        id() {
            return this._uid;
        },
        count_text_id() {
            return this.id + '_count';
        },
        select_box_id() {
            return this.id + '_selectbox';
        },
    },
    methods: {
        init() {
            let select_all_btn = document.createElement('a');
            select_all_btn.setAttribute('id', `${this.id}-select-all`);
            select_all_btn.setAttribute('href', '#');
            select_all_btn.innerText = this.$gettext('Alle hinzufügen');
            select_all_btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectAll();
            });
            let unselect_all_btn = document.createElement('a');
            unselect_all_btn.setAttribute('id', `${this.id}-unselect-all`);
            unselect_all_btn.setAttribute('href', '#');
            unselect_all_btn.innerText = this.$gettext('Alle entfernen');
            unselect_all_btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.unselectAll();
            });
            let selection_header = document.createElement('div');
            selection_header.setAttribute('id', this.count_text_id);
            selection_header.innerText = this.$gettextInterpolate('Sie haben %{ count } Personen ausgewählt', {count: this.count});

            $('#' + this.select_box_id).multiSelect({
                selectableHeader: '<div>' + this.$gettext('Suchergebnisse') + '</div>',
                selectionHeader: selection_header,
                selectableFooter: select_all_btn,
                selectionFooter: unselect_all_btn,
                afterSelect: () => this.updateSelection(),
                afterDeselect: () => this.updateSelection()
            });
        },

        search() {
            this.users = [];
            let view = this;
            $.getJSON(
                STUDIP.URLHelper.getURL('dispatch.php/multipersonsearch/ajax_search_vue/' + this.name, { s: this.searchTerm }),
                function(data) {
                    view.removeAllNotSelected();
                    var searchcount = 0;
                    $.each(data, function(i, item) {
                        searchcount += view.append(
                            item.id,
                            item.avatar + ' -- ' + item.text,
                            item.selected
                        );
                        delete item.selected;
                        view.users.push(item);
                    });
                    view.refresh();

                    if (searchcount === 0) {
                        view.append(
                            '--',
                            view.$gettextInterpolate('Es wurden keine neuen Ergebnisse für "%{ needle }" gefunden.', {needle: view.searchTerm}),
                            true
                        );
                        view.refresh();
                    }
                }
            );
        },

        selectAll: function() {
            $('#' + this.select_box_id).multiSelect('select_all');
            this.updateSelection();
        },

        unselectAll: function() {
            $('#' + this.select_box_id).multiSelect('deselect_all');
            this.updateSelection();
        },

        removeAll: function() {
            $('#' + this.select_box_id + ' option').remove();
            this.refresh();
        },

        removeAllNotSelected() {
            $('#' + this.select_box_id + ' option:not(:selected)').remove();
            this.refresh();
        },

        resetSearch() {
            this.searchTerm = '';
            this.removeAllNotSelected();
        },

        append(id, text, selected = false) {
            if ($('#' + this.select_box_id + ' option[value=' + id + ']').length === 0) {
                $('#' + this.select_box_id).multiSelect('addOption', {
                    value: id,
                    text: text,
                    disabled: selected
                });
                return 1;
            }
            return 0;
        },

        refresh() {
            $('#' + this.select_box_id).multiSelect('refresh');
            this.updateSelection();
        },

        updateCount(){
            this.count = $('#' + this.select_box_id + ' option:enabled:selected').length;
            $('#' + this.count_text_id).text(this.$gettextInterpolate('Sie haben %{ count } Personen ausgewählt', {count: this.count}));
        },

        async updateSelection() {
            this.updateCount();
            let selected_options = $('#' + this.select_box_id + ' option:enabled:selected');
            let user_ids = [];
            if (selected_options.length) {
                for (const option of selected_options) {
                    user_ids.push(option.value);
                }
            }
            let return_value = [];
            if (this.withDetail && this.users.length) {
                for (const user_id of user_ids) {
                    let existing_index = this.users.findIndex(user => {
                        return user.id === user_id;
                    });
                    if (existing_index !== -1) {
                        return_value.push(this.users[existing_index]);
                    }
                }
            } else {
                return_value = user_ids;
            }
            this.$emit('input', return_value);
        }
    },
}
</script>
