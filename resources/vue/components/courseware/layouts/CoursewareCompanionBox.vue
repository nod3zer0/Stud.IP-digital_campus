<template>
    <div class="cw-companion-box" :class="[mood]">
        <div>
            <p v-html="msgCompanion"></p>
            <slot name="companionActions"></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'courseware-companion-box',
    props: {
        msgCompanion: String,
        mood: {
            type: String,
            default: 'default',
            validator: value => {
                return ['default','unsure', 'special', 'sad', 'pointing', 'curious'].includes(value);
            }
        }
    },
};
</script>