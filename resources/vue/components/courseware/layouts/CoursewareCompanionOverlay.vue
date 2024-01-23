<template>
    <div class="cw-companion-overlay-wrapper">
        <div
            class="cw-companion-overlay"
            :class="[showCompanion ? 'cw-companion-overlay-in' : '', showCompanion ? '' : 'cw-companion-overlay-out', styleCompanion]"
            aria-hidden="true"
        >
            <div class="cw-companion-overlay-content" v-html="msgCompanion"></div>
            <button class="cw-compantion-overlay-close" @click="hideCompanion"></button>
        </div>
        <div
            class="sr-only"
            aria-live="polite"
            role="log"
        >
            <p>{{ msgCompanion }}</p>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-companion-overlay',
    computed: {
        ...mapGetters({
            showCompanion: 'showCompanionOverlay',
            msgCompanion: 'msgCompanionOverlay',
            styleCompanion: 'styleCompanionOverlay',
            showToolbar: 'showToolbar',
        }),
    },
    methods: {
        ...mapActions({
            coursewareShowCompanionOverlay: 'coursewareShowCompanionOverlay'
        }),
        hideCompanion() {
            this.coursewareShowCompanionOverlay(false);
        },
    },
    watch: {
        showCompanion(newValue, oldValue) {
            let view = this;
            if (newValue === true && oldValue === false) {
                setTimeout(() => {
                    view.hideCompanion();
                }, 4000);
            }
        },
        showToolbar(newValue, oldValue) {
            // hide companion when toolbar is closed 
            if (oldValue === true && newValue === false) {
                this.hideCompanion();
            }
        }
    },
};
</script>
