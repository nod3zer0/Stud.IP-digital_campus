<template>
    <div>
        <div class="formpart" tabindex="0" ref="autofocus">
            {{ $gettext('Frage') }}
            <studip-wysiwyg v-model="val_clone.description" :key="question_id"></studip-wysiwyg>
        </div>

        <label>
            <input type="checkbox" v-model="val_clone.mandatory">
            {{ $gettext('Pflichtfrage') }}
        </label>
    </div>
</template>

<script>
import StudipWysiwyg from "../StudipWysiwyg.vue";

export default {
    name: 'freetext-edit',
    components: {
        StudipWysiwyg
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
            val_clone: ''
        };
    },
    mounted: function () {
        this.val_clone = this.value;
        this.$refs.autofocus.focus();
    },
    watch: {
        value (new_val) {
            this.val_clone = new_val;
        }
    }

}
</script>
