<template>
    <div class="rangescale_edit">

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
                    <template v-if="typeof val_clone.maximum !== 'undefined' && typeof val_clone.minimum !== 'undefined'">
                    <th v-for="i in (val_clone.maximum - val_clone.minimum + 1)" :key="i" class="number">{{ (val_clone.minimum - 1 + i) }}</th>
                    </template>
                    <th v-if="typeof val_clone.alternative_answer !== 'undefined' && val_clone.alternative_answer.trim().length > 0">{{ val_clone.alternative_answer }}</th>
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
                            <studip-icon shape="hamburger" role="clickable"></studip-icon>
                        </a>
                    </td>
                    <td>
                        <input type="text"
                               :ref="'statement_' + index"
                               :placeholder="$gettext('Aussage')"
                               @paste="(ev) => onPaste(ev, index)"
                               v-model="val_clone.statements[index]">
                    </td>
                    <template v-if="typeof val_clone.maximum !== 'undefined' && typeof val_clone.minimum !== 'undefined'">
                    <td v-for="i in (val_clone.maximum - value.minimum + 1)" :key="i">
                        <input type="radio" value="1" disabled :title="i + val_clone.minimum - 1">
                    </td>
                    </template>
                    <td v-if="typeof val_clone.alternative_answer !== 'undefined' && val_clone.alternative_answer.trim().length > 0 > 0">
                        <input type="radio" value="1" disabled :title="val_clone.alternative_answer">
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
                    <td :colspan="val_clone.maximum - val_clone.minimum + 4 + (typeof val_clone.alternative_answer !== 'undefined' && val_clone.alternative_answer.trim().length > 0 ? 1 : 0)">
                        <button @click.prevent="addStatement" class="as-link" :title="$gettext('Aussage hinzufügen')">
                            <studip-icon shape="add" role="clickable" size="20" alt=""></studip-icon>
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>

        <label>
            <input type="checkbox" v-model="val_clone.mandatory">
            {{ $gettext('Pflichtfrage') }}
        </label>
        <label>
            <input type="checkbox" v-model="val_clone.randomize">
            {{ $gettext('Antworten den Teilnehmenden zufällig präsentieren') }}
        </label>

        <label>
            {{ $gettext('Maximum') }}
            <input type="number" v-model.number="val_clone.maximum">
        </label>

        <label>
            {{ $gettext('Minimum') }}
            <input type="number" v-model.number="val_clone.minimum">
        </label>

        <label>
            {{ $gettext('Ausweichantwort (leer lassen für keine)') }}
            <input type="text" v-model="val_clone.alternative_answer">
        </label>

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
const default_value = {
    statements: ['', '', '', ''],
    minimum: 1,
    maximum: 5,
    alternative_answer: ''
};
export default {
    name: 'likert-edit',
    components: {
        StudipIcon,
        StudipDialog,
        draggable,
        StudipWysiwyg
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
