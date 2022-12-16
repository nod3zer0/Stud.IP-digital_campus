<template>
    <div class="vote_edit">
        <label>
            {{ $gettext('Link eines Videos oder einer anderen Informationsseite (optional)') }}
            <input type="text" v-model="val_clone.url" ref="autofocus">
        </label>

        <div class="formpart">
            {{ $gettext('Hinweistext (optional)') }}
            <studip-wysiwyg v-model="val_clone.description" :key="question_id"></studip-wysiwyg>
        </div>
    </div>
</template>

<script>
import StudipWysiwyg from "../StudipWysiwyg.vue";

export default {
    name: 'questionnaire-info-edit',
    components: {
        StudipWysiwyg
    },
    props: {
        value: {
            type: Object,
            required: false,
            default: function () {
                return {
                    url: '',
                    description: ''
                };
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
