<template>
    <div class="vote_edit">
        <label>
            {{ $gettext('Link eines Videos oder einer anderen Informationsseite (optional)') }}
            <input type="url" v-model="val_clone.url" ref="infoUrl"
                   @input="checkValidity()">
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
            default() {
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
    data () {
        return {
            val_clone: this.value,
        };
    },
    methods: {
        checkValidity() {
            this.$refs.infoUrl.setCustomValidity('');

            if (!this.$refs.infoUrl.checkValidity()) {
                this.$refs.infoUrl.setCustomValidity(
                    this.$gettext('Der eingegebene Link ist nicht korrekt und wird nicht angezeigt werden.')
                );
                this.$refs.infoUrl.reportValidity();
            }
        }
    },
    mounted() {
        this.$refs.infoUrl.focus();
        this.checkValidity();
    },
    watch: {
        value (new_val) {
            this.val_clone = new_val;
        }
    }
}
</script>
