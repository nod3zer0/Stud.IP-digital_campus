<template>
    <div class="vote_edit">
        <div class="formpart" tabindex="0" ref="autofocus">
            {{ $gettext('Frage') }}
            <studip-wysiwyg v-model="val_clone.description" :key="question_id"></studip-wysiwyg>
        </div>

        <input-array v-model="val_clone.options"></input-array>

        <label>
            <input type="checkbox" v-model.number="val_clone.multiplechoice" true-value="1" false-value="0">
            {{ $gettext('Mehrere Antworten sind erlaubt') }}
        </label>
        <label>
            <input type="checkbox" v-model.number="val_clone.mandatory" true-value="1" false-value="0">
            {{ $gettext('Pflichtfrage') }}
        </label>
        <label>
            <input type="checkbox" v-model.number="val_clone.randomize" true-value="1" false-value="0">
            {{ $gettext('Antworten den Teilnehmenden zufällig präsentieren') }}
        </label>

    </div>
</template>

<script>
import StudipWysiwyg from "../StudipWysiwyg.vue";
import InputArray from "./InputArray.vue";

export default {
    name: 'vote-edit',
    components: {
        StudipWysiwyg,
        InputArray
    },
    props: {
        value: {
            type: Object,
            required: false,
            default: function () {
                return {};
            }
        },
        question_id: {
            type: String,
            required: false
        }
    },
    data: function () {
        return {
            val_clone: {}
        };
    },
    mounted: function () {
        this.val_clone = this.value;
        if (!this.value.description) {
            this.$emit('input', {
                multiplechoice: 1,
                options: ['', '', '', '']
            });
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
