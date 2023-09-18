<template>
    <div class="likert_edit">

        <div class="formpart" tabindex="0" ref="autofocus">
            {{ $gettext('Einleitungstext' )}}
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
                           :title="$gettextInterpolate($gettext('Sortierelement für Aussage %{statement}. Drücken Sie die Tasten Pfeil-nach-oben oder Pfeil-nach-unten, um dieses Element in der Liste zu verschieben.'), {statement: statement})"
                           @keydown="keyHandler($event, index)"
                           :ref="'draghandle_' + index">
                            <span class="drag-handle"></span>
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
                        <input type="radio" disabled :title="option">
                    </td>
                    <td class="actions">
                        <studip-icon name="delete"
                                     shape="trash"
                                     :size="20"
                                     @click.prevent="deleteStatement(index)"
                                     :title="$gettext('Aussage löschen')"
                        ></studip-icon>
                    </td>
                </tr>
            </draggable>
            <tfoot>
                <tr>
                    <td :colspan="val_clone.options.length + 3">
                        <studip-icon name="add"
                                     shape="add"
                                     :size="20"
                                     @click.prevent="addStatement()"
                                     :title="$gettext('Aussage hinzufügen')"
                        ></studip-icon>
                    </td>
                </tr>
            </tfoot>
        </table>

        <label>
            <input type="checkbox" v-model.number="val_clone.mandatory">
            {{ $gettext('Pflichtfrage') }}
        </label>
        <label>
            <input type="checkbox" v-model.number="val_clone.randomize">
            {{ $gettext('Antworten den Teilnehmenden zufällig präsentieren') }}
        </label>

        <div>
            <div>{{ $gettext('Antwortmöglichkeiten konfigurieren') }}</div>
            <input-array v-model="val_clone.options"></input-array>
        </div>
    </div>
</template>

<script>
import draggable from 'vuedraggable';
import InputArray from "./InputArray.vue";
import { $gettext } from '../../../assets/javascripts/lib/gettext';

const default_value = () => ({
    description: '',
    statements: ['', '', '', ''],
    mandatory: false,
    randomize: false,
    options: [
        $gettext('trifft zu'),
        $gettext('trifft eher zu'),
        $gettext('teils-teils'),
        $gettext('trifft eher nicht zu'),
        $gettext('trifft nicht zu'),
    ],
});
export default {
    name: 'likert-edit',
    components: {
        draggable,
        InputArray
    },
    props: {
        value: {
            type: Object,
            required: false,
            default() {
                return {...default_value()};
            }
        },
        question_id: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            val_clone: null,
            assistiveLive: ''
        };
    },
    methods: {
        addStatement(val = '', position = null) {
            if (position === null) {
                this.val_clone.statements.push(val || '');
            } else {
                this.val_clone.statements.splice(position, 0, val || '');
            }
            this.$nextTick(() => {
                this.$refs['statement_' + (this.val_clone.statements.length - 1)][0].focus();
            });
        },
        deleteStatement(index) {
            STUDIP.Dialog.confirm(this.$gettext('Wirklich löschen?')).done(() => {
                this.$delete(this.val_clone.statements, index);
            });
        },
        onPaste(ev, position) {
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
                        this.$nextTick(() => {
                            this.$refs['draghandle_' + (index - 1)][0].focus();
                            this.assistiveLive = this.$gettextInterpolate(
                                this.$gettext('Aktuelle Position in der Liste: %{pos} von %{listLength}.'),
                                {pos: index, listLength: this.val_clone.statements.length}
                            );
                        });
                    }
                    break;
                case 40: // down
                    e.preventDefault();
                    if (index < this.val_clone.statements.length - 1) {
                        this.moveDown(index);
                        this.$nextTick(() => {
                            this.$refs['draghandle_' + (index + 1)][0].focus();
                            this.assistiveLive = this.$gettextInterpolate(
                                this.$gettext('Aktuelle Position in der Liste: %{pos} von %{listLength}.'),
                                {pos: index + 2, listLength: this.val_clone.statements.length}
                            );
                        });
                    }
                    break;
            }
        },
        moveDown(index) {
            this.val_clone.statements.splice(
                index,
                2,
                this.val_clone.statements[index + 1],
                this.val_clone.statements[index]
            )
        },
        moveUp(index) {
            this.val_clone.statements.splice(
                index - 1,
                2,
                this.val_clone.statements[index],
                this.val_clone.statements[index - 1]
            )
        }
    },
    created() {
        this.val_clone = Object.assign({}, default_value(), this.value ?? {});
    },
    mounted() {
        this.$refs.autofocus.focus();
    },
    watch: {
        val_clone: {
            handler(current) {
                this.$emit('input', current);
            },
            deep: true
        }
    }
}
</script>
