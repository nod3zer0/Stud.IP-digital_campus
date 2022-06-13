<template>
    <div class="i18n_group" v-if="languages.length > 1">
        <div class="i18n"
             v-for="language in languages"
             v-if="(selectedLanguage !== null) && (language.id === selectedLanguage.id)"
             :data-lang="language.name"
             :data-icon="'url(' + assetsURL + 'images/languages/' + language.picture + ')'">
            <input type=text
                   :name="nameOfInput(language.id)"
                   v-model="values[selectedLanguage.id]"
                   :required="required && defaultLanguage === language.id"
                   v-bind="$attrs"
                   v-on="$listeners"
                   v-if="type === 'text'">
            <textarea :name="nameOfInput(language.id)"
                      v-bind="$attrs"
                      v-on="$listeners"
                      v-model="values[language.id]"
                      :required="required && defaultLanguage === language.id"
                      v-else-if="type === 'textarea'">{{ values[language.id] }}</textarea>
            <studip-wysiwyg :name="nameOfInput(language.id)"
                            v-model="values[selectedLanguage.id]"
                            v-bind="$attrs"
                            v-on="$listeners"
                            :required="required && defaultLanguage === language.id"
                            v-else-if="type === 'wysiwyg' && !wysiwyg_disabled"></studip-wysiwyg>
            <textarea-with-toolbar :name="nameOfInput(language.id)"
                      v-else
                      v-model="values[selectedLanguage.id]"
                      v-bind="$attrs"
                      :required="required && defaultLanguage === language.id"
                      v-on="$listeners"></textarea-with-toolbar>
        </div>
        <input type="hidden"
               v-for="language in languages"
               v-if="(selectedLanguage !== null) && (language.id !== selectedLanguage.id)"
               v-model="values[language.id]"
               :required="required && defaultLanguage === language.id"
               :name="nameOfInput(language.id)">
        <select class="i18n"
                tabindex="-1"
                @change="selectLanguage"
                :style="'background-image: url(' + assetsURL + 'images/languages/' + selectedLanguage.picture + ')'">
            <option v-for="language in languages" :value="language.id">{{language.name}}</option>
        </select>
    </div>
    <div v-else>
        <input type=text
               :name="name"
               v-model="values[selectedLanguage.id]"
               v-bind="$attrs"
               v-on="$listeners"
               :required="required"
               v-if="type === 'text'">
        <textarea :name="name"
                  v-model="values[selectedLanguage.id]"
                  v-bind="$attrs"
                  v-on="$listeners"
                  :required="required"
                  v-else-if="type === 'textarea'"></textarea>
        <studip-wysiwyg :name="name"
                        v-model="values[selectedLanguage.id]"
                        v-bind="$attrs"
                        v-on="$listeners"
                        :required="required"
                        v-else-if="type === 'wysiwyg' && !wysiwyg_disabled"></studip-wysiwyg>
        <textarea-with-toolbar :name="name"
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
        }
        if (jsonvalue !== false) {
            this.values = jsonvalue;
        } else {
            let values = {};
            values[this.selectedLanguage.id] = this.value;
            this.values = values;
        }
    },
    methods: {
        selectLanguage (e) {
            for (let i in this.languages) {
                if (e.target.value === this.languages[i].id) {
                    this.selectedLanguage = this.languages[i];
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
        }
    },
    inheritAttrs: false,
    watch: {
        values: {
            handler(newValue, oldValue) {
                this.$emit('input', newValue[this.defaultLanguage]);
                let exportValue = {};
                let input_all = {};
                let name = null;
                for (let i in this.languages) {
                    exportValue[this.languages[i].id] = newValue[this.languages[i].id];
                    name = this.nameOfInput(this.languages[i].id);
                    input_all[name] = newValue[this.languages[i].id];
                }
                this.$emit('input_all_languages', exportValue);
                this.$emit('allinputs', input_all);
            },
            deep: true
        }
    }
}
</script>
