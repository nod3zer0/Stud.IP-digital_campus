<template>
    <div class="cw-toolbar-containers">
        <div class="cw-container-style-selector" role="group" aria-labelledby="cw-containeradder-style">
                    <p class="sr-only" id="cw-containeradder-style">{{ $gettext('Abschnitt-Stil') }}</p>
                    <template
                        v-for="style in containerStyles"
                    >
                        <input
                            :key="style.key  + '-input'"
                            type="radio"
                            name="container-style"
                            :id="'style-' + style.colspan"
                            v-model="selectedContainerStyle"
                            :value="style.colspan"
                        />
                        <label
                            :key="style.key + '-label'"
                            :for="'style-' + style.colspan"
                            :class="[selectedContainerStyle === style.colspan ? 'cw-container-style-selector-active' : '', style.colspan]"
                        >
                            {{ style.title }}
                        </label>
                        
                    </template>
                </div>
                <courseware-container-adder-item
                    v-for="container in containerTypes"
                    :key="container.type"
                    :title="container.title"
                    :type="container.type"
                    :colspan="selectedContainerStyle"
                    :description="container.description"
                    :firstSection="$gettext('erstes Element')"
                    :secondSection="$gettext('zweites Element')"
                ></courseware-container-adder-item>
    </div>
</template>

<script>
import CoursewareContainerAdderItem from './CoursewareContainerAdderItem.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-toolbar-containers',
    components: {
        CoursewareContainerAdderItem
    },
    data() {
        return {
            selectedContainerStyle: 'full'
        };
    },
    computed: {
        ...mapGetters({
            containerTypes: 'containerTypes'
        }),
        containerStyles() {
            return [
                { key: 0, title: this.$gettext('Volle Breite'), colspan: 'full'},
                { key: 1, title: this.$gettext('Halbe Breite'), colspan: 'half' },
                { key: 2, title: this.$gettext('Halbe Breite (zentriert)'), colspan: 'half-center' }
            ];
        },
    }
}
</script>