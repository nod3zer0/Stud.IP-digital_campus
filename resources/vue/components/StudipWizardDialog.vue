<template>
    <studip-dialog
        :height="height"
        :width="width"
        :title="title"
        :confirmText="confirmText"
        :confirmClass="confirmClass"
        :confirmDisabled="!showConfirm"
        :closeText="closeText"
        :closeClass="closeClass"
        @close="$emit('close')"
        @confirm="confirm"
    >
        <template v-slot:dialogContent>
            <div class="wizard-wrapper">
                <div class="wizard-meta">
                    <studip-icon :shape="activeSlot.icon" :size="96"/>
                    <p class="wizard-description">
                        {{ activeSlot.description }}
                    </p>
                    <p v-if="requirements.length > 0" class="wizard-requirements">
                        <span>{{ $gettext('Bitte geben Sie die folgenden Informationen an:') }}</span>
                        <ul>
                            <li v-for="(requirement, index) in requirements" :key="requirement.slot.name + '_' + index">
                                <button @click="selectSlot(requirement.slot.id)">
                                    <studip-icon
                                        :shape="requirement.slot.icon"
                                        :size="16"
                                        role="clickable"
                                    />{{ requirement.text }}
                                </button>
                            </li>
                        </ul>
                    </p>
                </div>
                <div class="wizard-content-wrapper">
                    <h2>
                        {{ activeSlot.title }}<span v-if="activeSlotRequiered" aria-hidden="true" class="required">*</span>
                    </h2>
                    <ul class="wizard-progress">
                        <li
                            v-for="progress in slots"
                            :key="progress.id"
                            :class="[
                                isValid(progress.id) ? 'valid' : 'invalid',
                                activeId === progress.id ? 'active' : 'inactive',
                                isOptional(progress.id) ? 'optional' : ''
                            ]"
                        >
                            <button
                                ref="tabs"
                                :title="progress.title"
                                role="tab"
                                :aria-selected="activeId === progress.id"
                                :aria-controls="progress.name"
                                :tabindex="0"
                                @click="selectSlot(progress.id)"
                                @keydown.right="nextContent"
                                @keydown.left="prevContent"
                            >
                                <studip-icon
                                    :shape="progress.icon"
                                    :size="24"
                                    :role="isValid(progress.id) ? 'info_alt' : 'clickable'"
                                />
                            </button>
                        </li>
                    </ul>
                    <ul class="wizard-list">
                        <li v-for="slot in slots" :key="slot.id">
                            <div
                                v-show="slot.id === activeId"
                                class="wizard-item"
                                role="tabpanel"
                                :aria-labelledby="slot.name"
                            >
                                <div class="wizard-content">
                                    <slot :name="slot.name" ></slot>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </template>
        <template v-slot:dialogButtonsBefore>
            <button :style="{visibility: hasPrevContent ? 'visible' : 'hidden'}" class="button arr_left" @click="prevContent">
                {{ $gettext('zurück') }}
            </button>
        </template>
        <template v-slot:dialogButtonsAfter>
            <button :style="{visibility: hasNextContent ? 'visible' : 'hidden'}" class="button arr_right" @click="nextContent">
                {{ $gettext('weiter') }}
            </button>
        </template>
    </studip-dialog>
</template>

<script>
import StudipDialog from './StudipDialog.vue'
import StudipIcon from './StudipIcon.vue';
export default {
    name: 'studip-wizard-dialog',
    components: {
        StudipDialog,
        StudipIcon
    },
    props: {
        title: {
            type: String
        },
        confirmText: {
            type: String
        },
        closeText: {
            type: String
        },
        confirmClass: {
            type: String,
            default: 'accept'
        },
        closeClass: {
            type: String,
            default: 'cancel'
        },
        height: {
            type: String,
            default: '640'
        },
        width: {
            type: String,
            default: '880'
        },
        slots: {
            type: Array,
            required: true
        },
        lastRequiredSlotId: {
            type: Number
        },
        requirements: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            activeId: 1,
            visitedIds: [1]
        }
    },
    computed: {
        hasPrevContent() {
            if (this.activeId === 1) {
                return false;
            }

            return true;
        },
        hasNextContent() {
            if (this.activeId === this.slots.length) {
                return false;
            }

            return true;
        },
        showConfirm() {
            let valid = true;
            if (this.lastRequiredSlotId !== undefined) {
                this.slots.every(slot => {
                    if (slot.id > this.lastRequiredSlotId) {
                        return false;
                    }
                    if (!slot.valid) {
                        valid = false;
                    }

                    return true;
                });

                return valid;
            }

            this.slots.forEach( slot => {
                if (!slot.valid) {
                    valid = false;
                }
            });

            return valid;
        },
        activeSlot() {
            return this.slots.filter(slot => this.activeId === slot.id)[0];
        },
        activeSlotRequiered() {
            if (this.lastRequiredSlotId === undefined) {
                return false;
            }

            return this.lastRequiredSlotId >= this.activeSlot.id;
        },
    },
    methods: {
        prevContent() {
            if (!this.hasPrevContent) {
                return;
            } else {
                this.activeId = this.activeId - 1;
                this.$nextTick(() => {
                    this.$refs.tabs[this.activeId - 1].focus();
                });
            }
        },
        nextContent() {
            if (!this.hasNextContent) {
                return;
            } else {
                this.activeId = this.activeId + 1;
                this.$nextTick(() => {
                    this.$refs.tabs[this.activeId - 1].focus();
                });
            }
        },
        selectSlot(id) {
            this.activeId = id;
        },
        isValid(id) {
            const slot = this.slots.find( slot => slot.id === id);
            if (slot) {
                return slot.valid && this.visitedIds.indexOf(id) !== -1;
            }

            return false;
        },
        isOptional(id) {
            if (this.lastRequiredSlotId === undefined) {
                return false;
            }

            return this.lastRequiredSlotId < id;
        },
        confirm() {
            this.$emit('confirm');
        }
    },
    watch: {
        activeId(newVal) {
            if (this.visitedIds.indexOf(newVal) === -1) {
                this.visitedIds.push(newVal);
            }
        }
    }
}
</script>
