<template>
    <textarea :name="name"
              ref="textarea"
              v-model="content"
    ></textarea>
</template>

<script>
export default {
    name: 'textarea-with-toolbar',
    props: {
        value: String,
        name: {
            type: String,
            required: false
        }
    },
    computed: {
        content: {
            get() {
                return this.value;
            },
            set(newContent) {
                this.$emit('input', newContent);
            }
        }
    },
    mounted () {
        $(this.$refs.textarea).addToolbar();
    }
}
</script>
