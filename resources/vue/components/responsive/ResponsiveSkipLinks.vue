<template>
    <div>
        <MountingPortal mount-to="#skiplink_list" append>
            <portal-target name="additional-skiplinks"></portal-target>
        </MountingPortal>
        <portal to="additional-skiplinks">
            <li v-for="(link) in links" :key="link.url">
                <button class="skiplink" role="link" @click.prevent="goto(link.url)">
                    {{ link.label }}
                </button>
            </li>
        </portal>
    </div>
</template>

<script>
export default {
    name: 'ResponsiveSkipLinks',
    props: {
        links: {
            type: Array,
            default: () => []
        }
    },
    methods: {
        goto(url) {
            window.location = url;
        }
    },
    created() {
        const allButtons = document.querySelectorAll('button.skiplink');
        const buttons = document.querySelectorAll('button.skiplink:not([data-in-fullscreen="1"])');
        buttons.forEach(button => {
            button.style.display = 'none';
        });
        this.$nextTick(() => {
            allButtons.forEach(button => {
                document.getElementById('skiplink_list').appendChild(button.parentNode);
            });
        });
    },
    beforeDestroy() {
        const buttons = document.querySelectorAll('button.skiplink:not([data-in-fullscreen="1"])');
        buttons.forEach(button => {
            button.style.display = null;
        });
    }
}
</script>
