<template>
    <div class="likert_edit">

        <div class="formpart" tabindex="0" ref="autofocus">
            {{$gettext('Einleitungstext')}}
            <studip-wysiwyg v-model="val_clone.description"></studip-wysiwyg>
        </div>

        <span aria-live="assertive" class="sr-only">{{ assistiveLive }}</span>

        <table class="default nohover">
            <thead>
                <tr>
                    <th class="dragcolumn"></th>
                    <th>{{ $gettext('Aussagen') }}</th>
                    <th v-for="(option, index) in val_clone.options" :key="index">{{ option }}</th>
                    <th class="actions"></th>
                </tr>
            </thead>
            <draggable v-model="val_clone.statements" handle=".dragarea" tag="tbody" class="statements">
                <tr v-for="(statement, index) in val_clone.statements" :key="index">
                    <td class="dragcolumn">
                        <a class="dragarea"
                           tabindex="0"
                           :title="$gettextInterpolate('Sortierelement für Aussage %{statement}. Drücken Sie die Tasten Pfeil-nach-oben oder Pfeil-nach-unten, um dieses Element in der Liste zu verschieben.', {statement: statement})"
                           @keydown="keyHandler($event, index)"
                           :ref="'draghandle_' + index">
                            <span class="handle"></span>
                        </a>
                    </td>
                    <td>
                        <input type="text"
                               :ref="'statement_' + index"
                               :placeholder="$gettext('Aussage')"
                               @paste="(ev) => onPaste(ev, index)"
                               v-model="val_clone.statements[index]">
                    </td>
                    <td v-for="(option, index2) in val_clone.options" :key="index2">
                        <input type="radio" value="1" disabled :title="option">
                    </td>
                    <td class="actions">
                        <button class="as-link"
                           @click.prevent="askForDeletingStatement(index)"
                           :title="$gettext('Aussage löschen')">
                            <studip-icon shape="trash" role="clickable" size="20" alt=""></studip-icon>
                        </button>
                    </td>
                </tr>
            </draggable>
            <tfoot>
                <tr>
                    <td :colspan="typeof val_clone.options !== 'undefined' ? val_clone.options.length + 3 : 3">
                        <button @click.prevent="addStatement" class="as-link" :title="$gettext('Aussage hinzufügen')">
                            <studip-icon shape="add" role="clickable" size="20" alt=""></studip-icon>
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>

        <label>
            <input type="checkbox" v-model.number="val_clone.mandatory" true-value="1" false-value="0">
            {{ $gettext('Pflichtfrage') }}
        </label>
        <label>
            <input type="checkbox" v-model.number="val_clone.randomize" true-value="1" false-value="0">
            {{ $gettext('Antworten den Teilnehmenden zufällig präsentieren') }}
        </label>

        <div>
            <div>{{ $gettext('Antwortmöglichkeiten konfigurieren') }}</div>
            <input-array v-model="val_clone.options"></input-array>
        </div>

        <studip-dialog
            v-if="askForDeleting"
            :title="$gettext('Bitte bestätigen Sie die Aktion.')"
            :question="$gettext('Wirklich löschen?')"
            :confirmText="$gettext('Ja')"
            :closeText="$gettext('Nein')"
            closeClass="cancel"
            height="180"
            @confirm="deleteStatement"
            @close="askForDeleting = false"
        >
        </studip-dialog>
    </div>
</template>

<script>
import StudipIcon from "../StudipIcon.vue";
import StudipDialog from "../StudipDialog.vue";
import draggable from 'vuedraggable';
import StudipWysiwyg from "../StudipWysiwyg.vue";
import InputArray from "./InputArray.vue";
import { $gettext } from '../../../assets/javascripts/lib/gettext.js';
const default_value = {
    statements: ['', '', '', ''],
        options: [$gettext('trifft zu'), $gettext('trifft eher zu'), $gettext('teils-teils'), $gettext('trifft eher nicht zu'), $gettext('trifft nicht zu')]
};
export default {
    name: 'likert-edit',
    components: {
        StudipWysiwyg,
        StudipIcon,
        StudipDialog,
        draggable,
        InputArray
    },
    props: {
        value: {
            type: Object,
            required: false,
            default: function () {
                return default_value;
            }
        },
        question_id: {
            type: String,
            required: false
        }
    },
    data: function () {
        return {
            val_clone: {},
            askForDeleting: false,
            indexOfDeletingStatement: 0,
            assistiveLive: ''
        };
    },
    methods: {
        addStatement: function (val, position) {
            if (val.target) {
                val = '';
            }
            let data = this.value;
            if (typeof position === "undefined") {
                data.statements.push(val || '');
                position = this.value.length - 1
            } else {
                data.statements.splice(position, 0, val || '');
            }
            this.$emit('input', data);
            let v = this;
            this.$nextTick(function () {
                v.$refs['statement_' + (v.value.statements.length - 1)][0].focus();
            });
        },
        askForDeletingStatement: function (index) {
            this.indexOfDeletingStatement = index;
            if (this.value.statements[index]) {
                this.askForDeleting = true;
            } else {
                this.deleteStatement();
            }
        },
        deleteStatement: function () {
            this.$delete(this.value.statements, this.indexOfDeletingStatement);
            this.askForDeleting = false;
        },
        onPaste: function (ev, position) {
            let data = ev.clipboardData.getData("text").split("\n");
            for (let i = 0; i < data.length; i++) {
                if (data[i].trim()) {
                    this.addStatement(data[i], position + i);
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
                                , {pos: index, listLength: this.val_clone.statements.length}
                            );
                        });
                    }
                    break;
                case 40: // down
                    e.preventDefault();
                    if (index < this.val_clone.statements.length - 1) {
                        this.moveDown(index);
                        this.$nextTick(function () {
                            this.$refs['draghandle_' + (index + 1)][0].focus();
                            this.assistiveLive = this.$gettextInterpolate(
                                'Aktuelle Position in der Liste: %{pos} von %{listLength}.'
                                , {pos: index + 2, listLength: this.val_clone.statements.length}
                            );
                        });
                    }
                    break;
            }
        },
        moveDown: function (index) {
            let statement = this.val_clone.statements[index];
            this.val_clone.statements[index] = this.val_clone.statements[index + 1];
            this.val_clone.statements[index + 1] = statement;
            this.$forceUpdate();
        },
        moveUp: function (index) {
            let statement = this.val_clone.statements[index];
            this.val_clone.statements[index] = this.val_clone.statements[index - 1];
            this.val_clone.statements[index - 1] = statement;
            this.$forceUpdate();
        }
    },
    mounted: function () {
        this.val_clone = this.value;
        if (!this.value.statements) {
            this.$emit('input', default_value);
        }
        this.$refs.autofocus.focus();
    },
    watch: {
        value (new_val) {
            this.val_clone = new_val;
        }
    }
}
</script>
