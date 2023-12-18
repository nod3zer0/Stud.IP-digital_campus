<template>
    <div class="cw-containeradder-item-wrapper">
        <span class="cw-sortable-handle cw-sortable-handle-containeradder"></span>
        <a href="#" @click.prevent="addNewContainer">
            <div class="cw-containeradder-item" :class="['cw-containeradder-item-' + type]">
                <header class="cw-containeradder-item-title">
                    {{ title }}
                </header>
                <p class="cw-containeradder-item-description">
                    {{ description }}
                </p>
            </div>
        </a>
        <li class="cw-container-dragitem cw-container-item-sortable" ref="container-drag-item">
            <div class="cw-container cw-container-list cw-container-item" :class="['cw-container-colspan-' + colspan]">
                <div class="cw-container-content">
                    <header class="cw-container-header">{{ title }}</header>
                    <div class="cw-block-wrapper">{{ description }}</div>
                </div>
            </div>
        </li>
    </div>
</template>
<script>
import containerMixin from '@/vue/mixins/courseware/container';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-container-adder-item',
    mixins: [containerMixin],
    props: {
        title: String,
        description: String,
        type: String,
        colspan: String,
        firstSection: String,
        secondSection: String,
        newPosition: Number,
    },
    methods: {
        ...mapActions({
            createContainer: 'createContainer',
            companionSuccess: 'companionSuccess',
        }),
        addNewContainer() {
            this.addContainer({
                type: this.type,
                colspan: this.colspan,
                sections: {
                    firstSection: this.firstSection, 
                    secondSection: this.secondSection
                },
                newPosition: null
            });
        },
    },
};
</script>
