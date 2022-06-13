<template>
    <textarea :name="name" ref="textarea"
              v-bind="$attrs" v-on="$listeners"></textarea>
</template>

<script>
export default {
    name: 'textarea-with-toolbar',
    props: {
        name: {
            type: String,
            required: false
        }
    },
    mounted () {
        $(this.$refs.textarea).addToolbar();
    }
}
</script>
