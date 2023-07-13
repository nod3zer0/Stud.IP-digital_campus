<template>
    <div class="i18n_group" v-if="languages.length > 1">
        <div class="i18n"
             v-if="selectedLanguage !== null"
             :data-lang="selectedLanguage.name"
             :data-icon="'url(' + assetsURL + 'images/languages/' + selectedLanguage.picture + ')'">
            <input type=text
                   ref="inputfield"
                   :name="nameOfInput(selectedLanguage.id)"
                   v-model="values[selectedLanguage.id]"
                   :required="required && defaultLanguage === selectedLanguage.id"
                   v-bind="$attrs"
                   v-on="$listeners"
                   v-if="type === 'text'">
            <textarea :name="nameOfInput(selectedLanguage.id)"
                      ref="inputfield"
                      v-bind="$attrs"
                      v-on="$listeners"
                      v-model="values[selectedLanguage.id]"
                      :required="required && defaultLanguage === selectedLanguage.id"
                      v-else-if="type === 'textarea'"></textarea>
            <studip-wysiwyg :name="nameOfInput(selectedLanguage.id)"
                            ref="inputfield"
                            v-model="values[selectedLanguage.id]"
                            v-bind="$attrs"
                            v-on="$listeners"
                            :required="required && defaultLanguage === selectedLanguage.id"
                            v-else-if="type === 'wysiwyg' && !wysiwyg_disabled"></studip-wysiwyg>
            <textarea-with-toolbar :name="nameOfInput(selectedLanguage.id)"
                                   ref="inputfield"
                                   v-else
                                   v-model="values[selectedLanguage.id]"
                                   v-bind="$attrs"
                                   :required="required && defaultLanguage === selectedLanguage.id"
                                   v-on="$listeners"></textarea-with-toolbar>
        </div>
        <input type="hidden"
               v-for="language in otherLanguages"
               :key="`hidden-${language.id}`"
               v-model="values[language.id]"
               :required="required && defaultLanguage === language.id"
               :name="nameOfInput(language.id)">
        <select class="i18n"
                tabindex="0"
                @change="selectLanguage"
                :aria-label="$gettext('Sprache des Textfeldes auswählen.')"
                :style="'background-image: url(' + assetsURL + 'images/languages/' + selectedLanguage.picture + ')'">
            <option v-for="language in languages" :value="language.id" :key="`option-${language.id}`">
                {{language.name}}
            </option>
        </select>
    </div>
    <div v-else>
        <input type=text
               ref="inputfield"
               :name="name"
               v-model="values[selectedLanguage.id]"
               v-bind="$attrs"
               v-on="$listeners"
               :required="required"
               v-if="type === 'text'">
        <textarea :name="name"
                  ref="inputfield"
                  v-model="values[selectedLanguage.id]"
                  v-bind="$attrs"
                  v-on="$listeners"
                  :required="required"
                  v-else-if="type === 'textarea'"></textarea>
        <studip-wysiwyg :name="name"
                        ref="inputfield"
                        v-model="values[selectedLanguage.id]"
                        v-bind="$attrs"
                        v-on="$listeners"
                        :required="required"
                        v-else-if="type === 'wysiwyg' && !wysiwyg_disabled"></studip-wysiwyg>
        <textarea-with-toolbar :name="name"
                               ref="inputfield"
                               v-else
                               v-model="values[selectedLanguage.id]"
                               v-bind="$attrs"
                               :required="required"
                               v-on="$listeners"></textarea-with-toolbar>
    </div>
</template>

<script>
import StudipWysiwyg from './StudipWysiwyg.vue';
export default {
    name: 'i18n-textarea',
    components: {
        StudipWysiwyg
    },
    props: {
        name: {
            type: String,
            required: false
        },
        wysiwyg: {
            type: Boolean,
            required: false,
            default: false
        },
        type: {
            type: String,
            required: false,
            default: "text"
        },
        value: {
            required: false,
            default: ""
        },
        wysiwyg_disabled: {
            type: Boolean,
            required: false,
            default: false
        },
        required: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    data () {
        return {
            selectedLanguage: {},
            values: {}
        };
    },
    mounted () {
        for (let i in this.languages) {
            this.selectedLanguage = this.languages[i];
            break;
        }
        let jsonvalue = false;
        try {
            jsonvalue = JSON.parse(this.value);
        } catch (except) {
            // No fallback
        }
        if (jsonvalue !== false) {
            this.values = jsonvalue;
        } else {
            let values = {};
            values[this.selectedLanguage.id] = this.value;
            this.values = values;
        }
        this.$emit('selectlanguage', this.selectedLanguage.id);
    },
    methods: {
        selectLanguage (e) {
            for (let i in this.languages) {
                if (e.target.value === this.languages[i].id) {
                    this.selectedLanguage = this.languages[i];
                    this.$emit('selectlanguage', this.languages[i].id);
                    this.$nextTick(() => {
                        if (typeof this.$refs.inputfield.focus === "function") {
                            this.$refs.inputfield.focus();
                        } else if (typeof this.$refs.inputfield.prefill === 'function') {
                            this.$refs.inputfield.prefill();
                        }
                    });
                    break;
                }
            }
        },
        nameOfInput (language_id) {
            return this.name + (this.defaultLanguage === language_id ? '' : '_i18n[' + language_id + ']')
        }
    },
    computed: {
        assetsURL () {
            return STUDIP.ASSETS_URL;
        },
        defaultLanguage () {
            return this.languages[0].id;
        },
        languages () {
            let languages = [];
            let language = {};
            for (let i in STUDIP.CONTENT_LANGUAGES) {
                if (STUDIP.INSTALLED_LANGUAGES[STUDIP.CONTENT_LANGUAGES[i]] !== undefined) {
                    language = STUDIP.INSTALLED_LANGUAGES[STUDIP.CONTENT_LANGUAGES[i]];
                    language.id = STUDIP.CONTENT_LANGUAGES[i];
                    languages.push(language);
                }
            }
            return languages;
        },
        otherLanguages () {
            return this.languages.filter(language => language.id !== this.selectedLanguage.id);
        }
    },
    inheritAttrs: false,
    watch: {
        values: {
            handler(newValue, oldValue) {
                this.$emit('input', newValue[this.defaultLanguage]);
                let input_all = {};
                for (let i in this.languages) {
                    let name = this.nameOfInput(this.languages[i].id);
                    let value = newValue[this.languages[i].id];

                    if (this.type === 'wysiwyg' && STUDIP.editor_enabled && value !== null) {
                        value = STUDIP.wysiwyg.markAsHtml(value);
                    }

                    input_all[name] = value;
                }
                this.$emit('allinputs', input_all);
            },
            deep: true
        }
    }
}
</script>
