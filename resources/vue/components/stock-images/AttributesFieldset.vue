<template>
    <div>
        <label class="studiprequired">
            {{ $gettext('Titel') }}<span :title="$gettext('Dies ist ein Pflichtfeld')" aria-hidden="true" class="asterisk">*</span>
            <input type="text" required v-model="title" />
        </label>
        <label class="studiprequired">
            {{ $gettext('Beschreibung') }}<span :title="$gettext('Dies ist ein Pflichtfeld')" aria-hidden="true" class="asterisk">*</span>
            <textarea required v-model="description" />
        </label>
        <label class="studiprequired">
            {{ $gettext('Erstellt durch') }}<span :title="$gettext('Dies ist ein Pflichtfeld')" aria-hidden="true" class="asterisk">*</span>
            <input type="text" required v-model="author" />
        </label>
        <label class="studiprequired">
            {{ $gettext('Lizenz') }}<span :title="$gettext('Dies ist ein Pflichtfeld')" aria-hidden="true" class="asterisk">*</span>
            <textarea required v-model="license" />
        </label>
        <label>
            {{ $gettext('Tags') }}
            <TagsInput v-model="tags" :suggestions="suggestedTags" />
        </label>
    </div>
</template>
<script>
import TagsInput from './TagsInput.vue';

export default {
    props: ['metadata', 'suggestedTags'],
    components: { TagsInput },
    computed: {
        author: {
            get() {
                return this.metadata.author;
            },
            set(author) {
                this.$emit('change', { ...this.metadata, author });
            },
        },
        description: {
            get() {
                return this.metadata.description;
            },
            set(description) {
                this.$emit('change', { ...this.metadata, description });
            },
        },
        license: {
            get() {
                return this.metadata.license;
            },
            set(license) {
                this.$emit('change', { ...this.metadata, license });
            },
        },
        tags: {
            get() {
                return this.metadata.tags;
            },
            set(tags) {
                this.$emit('change', { ...this.metadata, tags });
            },
        },
        title: {
            get() {
                return this.metadata.title;
            },
            set(title) {
                this.$emit('change', { ...this.metadata, title });
            },
        },
    },
};
</script>
