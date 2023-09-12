<template>
    <div class="cw-block cw-block-biography cw-block-biography-goals">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @closeEdit="initCurrentData"
            @showEdit="setShowEdit"
            @storeEdit="storeBlock"
        >
            <template #content>
                <div class="cw-block-biography-content" >
                    <div class="cw-block-biography-type" :class="'cw-block-biography-goals-type-' + currentData.type">
                        <h2>{{ goalTypeName }}</h2>
                    </div>
                    <div class="cw-block-biography-details formatted-content ck-content" v-html="currentData.description">
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default">
                    <label for="type">
                        {{ $gettext('Ziel') }}
                        <select name="type" class="type" v-model="currentData.type">
                            <option value="personal">{{ $gettext('Persönliches Ziel') }}</option>
                            <option value="school">{{ $gettext('Schulisches Ziel') }}</option>
                            <option value="academic">{{ $gettext('Akademisches Ziel') }}</option>
                            <option value="professional">{{ $gettext('Berufliches Ziel') }}</option>
                        </select>
                    </label>
                    <div class="label-text">
                       {{ $gettext('Beschreibung') }}
                    </div>
                    <studip-wysiwyg v-model="currentData.description"></studip-wysiwyg>
                </form>
            </template>
            <template #info>{{ $gettext('Informationen zum Ziele-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions } from 'vuex';
import { blockMixin } from './block-mixin.js';
import StudipWysiwyg from '../StudipWysiwyg.vue';

export default {
    name: 'courseware-biography-goals-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
        StudipWysiwyg,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            showEdit: false,
            currentData: {
                type: '',
                description: ''
            },
        }
    },
    computed: {
        type() {
            return this.block?.attributes?.payload?.type;
        },
        description() {
            return this.block?.attributes?.payload?.description;
        },
        goalTypeName() {
            switch (this.currentData.type) {
                case 'personal':
                    return this.$gettext('Persönliches Ziel');
                case 'school':
                    return this.$gettext('Schulisches Ziel');
                case 'academic':
                    return this.$gettext('Akademisches Ziel');
                case 'professional':
                    return this.$gettext('Berufliches Ziel');
            }

            throw new Error('Undefined data type ' + this.currentData.type);
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentData = {
                type: this.type,
                description: this.description
            };
        },
        setShowEdit(state) {
            this.showEdit = state;
        },
        storeBlock() {
            let attributes = {
                payload: {
                    type: this.currentData.type,
                    description: this.currentData.description
                }
            };

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
    watch: {
        type() {
            if (!this.showEdit) {
                this.currentData.type = this.type;
            }
        },
        description() {
            if (!this.showEdit) {
                this.currentData.description = this.description;
            }
        },
    }
};
</script>
