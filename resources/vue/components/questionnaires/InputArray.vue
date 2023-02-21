<template>
    <div class="input-array">
        <span aria-live="assertive" class="sr-only">{{ assistiveLive }}</span>
        <draggable v-model="options" handle=".dragarea" tag="ol" class="clean options">
            <li v-for="(option, index) in options" :key="index">
                <a class="dragarea"
                   v-if="options.length > 1"
                   tabindex="0"
                   :ref="'draghandle_' + index"
                   :title="$gettextInterpolate('Sortierelement für Option %{option}. Drücken Sie die Tasten Pfeil-nach-oben oder Pfeil-nach-unten, um dieses Element in der Liste zu verschieben.', {option: option})"
                   @keydown="keyHandler($event, index)">
                    <span class="handle"></span>
                </a>
                <input type="text"
                       :placeholder="$gettext('Option')"
                       :ref="'option_' + index"
                       @paste="(ev) => onPaste(ev, index)"
                       v-model="options[index]">
                <button class="as-link"
                   :title="$gettext('Option löschen')"
                   @click.prevent="askForDeletingOption(index)">
                    <studip-icon shape="trash" role="clickable" size="20" alt=""></studip-icon>
                </button>
                <button v-if="index == options.length - 1"
                   class="as-link"
                   :title="$gettext('Option hinzufügen')"
                   @click.prevent="addOption">
                    <studip-icon shape="add" role="clickable" size="20" alt=""></studip-icon>
                </button>
            </li>
        </draggable>

        <studip-dialog
            v-if="askForDeleting"
            :title="$gettext('Bitte bestätigen Sie die Aktion.')"
            :question="$gettext('Wirklich löschen?')"
            :confirmText="$gettext('Ja')"
            :closeText="$gettext('Nein')"
            closeClass="cancel"
            height="180"
            @confirm="deleteOption"
            @close="askForDeleting = false"
        >
        </studip-dialog>
    </div>
</template>

<script>
import StudipIcon from "../StudipIcon.vue";
import StudipDialog from "../StudipDialog.vue";
import draggable from 'vuedraggable';
export default {
    name: 'input-array',
    components: {
        StudipIcon,
        StudipDialog,
        draggable
    },
    props: {
        value: {
            type: Array,
            required: false
        }
    },
    data: function () {
        return {
            options: [],
            askForDeleting: false,
            indexOfDeletingOption: 0,
            unique_id: null,
            assistiveLive: ''
        };
    },
    methods: {
        addOption: function (val, position) {
            let data = this.value;
            if (val.target) {
                val = '';
            }
            if (typeof position === "undefined") {
                data.push(val || '');
                position = this.value.length - 1
            } else {
                data.splice(position, 0, val || '');
            }
            this.$emit('input', data);
            let v = this;
            this.$nextTick(function () {
                v.$refs['option_' + position][0].focus();
            });
        },
        askForDeletingOption: function (index) {
            this.indexOfDeletingOption = index;
            if (this.value[index]) {
                this.askForDeleting = true;
            } else {
                this.deleteOption();
            }
        },
        deleteOption: function () {
            this.$delete(this.value, this.indexOfDeletingOption);
            this.askForDeleting = false;
        },
        onPaste: function (ev, position) {
            let data = ev.clipboardData.getData("text").split("\n");
            for (let i = 0; i < data.length; i++) {
                if (data[i].trim()) {
                    this.addOption(data[i], position + i);
                }
            }
        },
        keyHandler(e, index) {
            switch (e.keyCode) {
                case 38: // up
                    e.preventDefault();
                    if (index > 0) {
                        this.moveUp(index);
                        this.$nextTick(function () {
                            this.$refs['draghandle_' + (index - 1)][0].focus();
                            this.assistiveLive = this.$gettextInterpolate(
                                'Aktuelle Position in der Liste: %{pos} von %{listLength}.'
                                , {pos: index, listLength: this.options.length}
                            );
                        });
                    }
                    break;
                case 40: // down
                    e.preventDefault();
                    if (index < this.options.length - 1) {
                        this.moveDown(index);
                        this.$nextTick(function () {
                            this.$refs['draghandle_' + (index + 1)][0].focus();
                            this.assistiveLive = this.$gettextInterpolate(
                                'Aktuelle Position in der Liste: %{pos} von %{listLength}.'
                                , {pos: index + 2, listLength: this.options.length}
                            );
                        });
                    }
                    break;
            }
        },
        moveDown: function (index) {
            if (index == this.options.length - 1) {
                return;
            }
            let option = this.options[index];
            this.options[index] = this.options[index + 1];
            this.options[index + 1] = option;
            this.$forceUpdate();
        },
        moveUp: function (index) {
            if (index === 0) {
                return;
            }
            let option = this.options[index];
            this.options[index] = this.options[index - 1];
            this.options[index - 1] = option;
            this.$forceUpdate();
        }
    },
    mounted: function () {
        this.options = this.value;
        this.unique_id = 'array_input_' + Math.floor(Math.random() * 100000000);
    },
    watch: {
        options (new_data, old_data) {
            if (typeof old_data === 'undefined' || typeof new_data === 'undefined') {
                return;
            }
            this.$emit('input', new_data);
        },
        value (new_val) {
            this.options = new_val;
        }
    }
}
</script>
