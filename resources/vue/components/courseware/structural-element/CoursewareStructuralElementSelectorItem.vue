<template>
    <li class="courseware-structural-element-selector-item">
        <span
            class="radiobutton"
            :tabindex="tabindex"
            :ref="'radiobutton-' + element.id"
            role="radio"
            :aria-checked="selected ? 'true' : 'false'"
            :aria-labelledby="labelId"
            @click="handleClickInput(element.id)"
            @keydown="handleKeyInput($event, element.id)"
        >
            <template v-if="selectable">
                <studip-icon v-if="selected" shape="radiobutton-checked" />
                <studip-icon v-else shape="radiobutton-unchecked" />
            </template>
            <studip-icon v-else shape="decline" role="inactive" />    
        </span>
        <template v-if="hasChildren">
            <a href="#" :aria-expanded="isOpen ? 'true' : 'false'" @click.prevent="toggleChildrenVisibility">
                <studip-icon v-if="!isOpen" shape="arr_1right" />
                <studip-icon v-if="isOpen" shape="arr_1down" />
                <label :id="labelId">
                    {{ element.attributes.title }}
                    <span v-if="!selectable" class="sr-only">{{ $gettext('nicht wählbar') }}</span>
                </label>
            </a>
            <ul v-if="isOpen">
                <courseware-structural-element-selector-item
                    v-for="child in children"
                    :key="child.id"
                    :element="child"
                    :siblings="children"
                    :selectedId="selectedId"
                    :focusedElementId="focusedElementId"
                    :rootId="rootId"
                    :validateAncestors="validateAncestors"
                    :targetId="targetId"
                    :targetAncestors="targetAncestors"
                    :selectablePurposes="selectablePurposes"
                    @input="handleInput"
                    @focus="handleFocus"
                    @selectable="updateSelectable"
                />
            </ul>
        </template>
        <template v-else>
            <studip-icon shape="arr_1right" role="inactive" class="inactive"/>
            <label :id="labelId">
                {{ element.attributes.title }}
                <span v-if="!selectable" class="sr-only">{{ $gettext('nicht wählbar') }}</span>
            </label>
        </template>
    </li>
</template>
<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'courseware-structural-element-selector-item',
    props: {
        element: {
            type: Object
        },
        siblings: {
            type: Array
        },
        selectedId: {
            type: String,
            required: true
        },
        focusedElementId: {
            type: String
        },
        rootId: {
            type: String,
            required: true
        },  
        validateAncestors: {
            type: Boolean,
            default: false
        },
        targetId: {
            type: String,
            default: null
        },
        targetAncestors: {
            type: Array
        },
        selectablePurposes: {
            type: Array
        }
    },
    data() {
        return {
            isOpen: false,
            selectable: true,
        }
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            coursewareUnits: 'courseware-units/all',
            structuralElementById: 'courseware-structural-elements/byId',
            context: 'context',
            currentElement: 'currentElement'
        }),
        children() {
            const children = this.element?.relationships?.children?.data?.map(child => child.id);
            if (!children) {
                return [];
            }

            return children.map((id) => this.structuralElementById({ id })).filter(Boolean);
        },
        hasChildren() {
            return this.children.length > 0;
        },
        selected() {
            return this.selectedId === this.element?.id;
        },
        focused() {
            return this.focusedElementId === this.element?.id;
        },
        labelId() {
            return this.element.id + '_checkbox-label';
        },
        isRoot() {
            return this.rootId === this.element.id;
        },
        tabindex() {
            if (this.focusedElementId !== '') {
                return this.focused ? 0 : -1;
            }
            return this.isRoot ? 0 : -1;
        },
        nextElementId() {
            if (this.hasChildren && this.isOpen) {
                return this.children[0].id;
            }

            return this.nextSiblingId;
        },
        nextSiblingId() {
            if (this.isRoot) {
                return null;
            }
            const index = this.siblings.findIndex(element => element.id === this.element.id) + 1;
            if (this.siblings.length > index) {
                return this.siblings[index].id;
            } else {
                return this.$parent.nextSiblingId;
            }
        },
        previousElementId() {
            if (this.isRoot) {
                return null;
            }
            const index = this.siblings.findIndex(element => element.id === this.element.id) - 1;
            if (index > -1) {
                const childrenCount = this.siblings[index].relationships.children.data.length;
                const previousElement = this.$parent.$children.find(child => child.element?.id === this.siblings[index].id);
                if (childrenCount > 0 && previousElement.isOpen) {
                        const element = this.structuralElementById({ id: this.siblings[index].relationships.children.data[childrenCount - 1].id });
                        if (
                            element.relationships.children.data.length > 0
                            && previousElement.$children.find(child => child.element?.id === element.id).isOpen
                        ) {
                            return element.relationships.children.data[element.relationships.children.data.length -1].id;
                        }
                        return element.id;
                } else {
                    return this.siblings[index].id;
                } 
            } else {
                return this.$parent.element.id;
            }
        },
    },
    methods: {
        ...mapActions({
            loadStructuralElement: 'courseware-structural-elements/loadById',
            companionError: 'companionError',
            companionSuccess: 'companionSuccess',
        }),
        loadChildren() {
            const children = this.element?.relationships?.children?.data?.map(child => child.id) ?? [];
            children.forEach((id) => this.loadStructuralElement({id: id,  options: {include: 'children'}}));
        },
        toggleChildrenVisibility() {
            if (!this.isOpen) {
                this.loadChildren();
            }
            this.isOpen = !this.isOpen;
        },
        handleInput(id) {
            this.$emit('input', id);
        },
        handleFocus(id) {
            this.$emit('focus', id);
        },
        validate() {
            if (
                this.element.id === this.targetId
                || this.targetAncestors.find(ancestor => ancestor.id === this.element.id)
            ) {
                this.selectable = false;
                this.$emit('selectable', false);
            }
        },
        updateSelectable() {
            this.selectable = false;
            this.$emit('selectable', false);
        },
        filterSelectablePurposes() {
            if (this.selectablePurposes.length === 0) {
                return;
            }
            this.selectable = this.selectablePurposes.includes(this.element.attributes.purpose);
        },
        handleClickInput(id) {
            if (this.selectable) {
                this.handleInput(id);
            }
        },
        handleKeyInput(event, id) {
            switch(event.keyCode) {
                case 37: // arrow left
                case 38: // arrow up
                    event.preventDefault();
                    if (this.previousElementId !== null) {
                        this.$emit('focus', this.previousElementId);    
                    }
                    break;
                case 39: // arrow right
                case 40: // arrow down
                    event.preventDefault();
                    if (this.nextElementId !== null) {
                        this.$emit('focus', this.nextElementId);
                    }
                    break;
            }
        },
        selectRoot() {
            if (this.focusedElementId === '' && this.isRoot && this.selectable) {
                this.handleInput(this.element.id);
            }
        }
    },
    mounted() {
        this.loadChildren();
        if (this.validateAncestors) {
            this.validate();
        }
        this.filterSelectablePurposes();
        this.selectRoot();
    },
    watch: {
        focusedElementId(newId) {
            if (this.focused) {
                this.$refs['radiobutton-'+ this.element.id].focus();
                if (this.selectable) {
                    this.handleInput(newId);
                } else {
                    this.handleInput('');
                }
            }
            this.selectRoot();
        }
    }
}
</script>
