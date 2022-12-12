<template>
    <div>
        <MountingPortal mount-to="#responsive-toggle-fullscreen" append>
            <portal-target name="toggle-fullscreen-off"></portal-target>
        </MountingPortal>
        <MountingPortal mount-to="#non-responsive-toggle-fullscreen" append>
            <portal-target name="toggle-fullscreen-on"></portal-target>
        </MountingPortal>
        <portal :to="isFullscreen ? 'toggle-fullscreen-off' : 'toggle-fullscreen-on'">
            <button class="styleless" id="toggle-fullscreen"
                    :title="isFullscreen ? $gettext('Vollbildmodus verlassen') : $gettext('Vollbildmodus aktivieren')"
                    @click.prevent="toggleFullscreen"
                    @keydown.prevent.enter="toggleFullscreen"
                    @keydown.prevent.space="toggleFullscreen">
                <studip-icon :shape="isFullscreen ? 'fullscreen-off' : 'fullscreen-on4'"
                             :role="isFullscreen ? 'info_alt' : 'clickable'" :size="iconSize" alt=""></studip-icon>
            </button>
        </portal>
    </div>
</template>

<script>
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'ToggleFullscreen',
    components: { StudipIcon },
    props: {
        isFullscreen: {
            type: Boolean,
            default: false
        },
        iconSize: {
            type: Number,
            default: 24
        }
    },
    methods: {
        toggleFullscreen() {
            STUDIP.Vue.emit('toggle-fullscreen', !this.isFullscreen);
        }
    },
    created() {
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape' && this.isFullscreen) {
                this.toggleFullscreen();
            }
        });
    }
}
</script>
