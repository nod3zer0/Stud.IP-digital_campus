<template>
    <div class="pagination-wrapper-flex">
        <p :id="pagination_id" class="audible">
            {{ $gettext('Blättern') }}
        </p>
        <ul class="pagination" role="navigation" :aria-labelledby="pagination_id">
            <li class="prev" v-if="currentOffset > 0">
                <button class="pagination--link" @click.prevent="goBack" rel="prev" :title="$gettext('Zurück')">
                    <span class="audible">{{ $gettext('Eine Seite') }}</span>
                    {{ $gettext('zurück') }}
                </button>
            </li>
            <template v-for="offset of offsets">
                <li :key="'end-dots-' + offset" class="divider"
                    v-if="offset === (total_offsets - 1) && currentOffset < (total_offsets - 1) - (range + 1)">
                    &hellip;
                </li>
                <li :key="'offset-' + offset" :class="{'current': offset === currentOffset, 'no-divider': offset === 0}">
                    <button class="pagination--link" @click.prevent="goTo(offset)">
                        <span class="audible">{{ $gettext('Seite') }}</span>
                        {{ offset + 1 }}
                    </button>
                </li>
                <li :key="'start-dots' + offset" class="divider"
                    v-if="offset === 0 && currentOffset > range + 1">
                    &hellip;
                </li>
            </template>
            <li class="next no-divider" v-if="currentOffset < total_offsets - 1">
                <button class="pagination--link" @click.prevent="goNext" rel="next" :title="$gettext('Weiter')">
                    <span class="audible">{{ $gettext('Eine Seite') }}</span>
                    {{ $gettext('weiter') }}
                </button>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: 'studip-pagination',
    props: {
        currentOffset: {
            type: Number,
            required: true
        },
        totalItems: {
            type: Number,
            required: true
        },
        itemsPerPage: {
            type: Number,
            required: true
        },
        range: {
            type: Number,
            default: 2,
            min: 1
        }
    },
    computed: {
        pagination_id() {
            return 'pagination-label-' + this._uid;
        },
        total_offsets() {
            let total = Math.ceil(this.totalItems / this.itemsPerPage);
            return total;
        },
        offsets() {
            let offsets = [0, this.currentOffset, (this.total_offsets - 1)];
            for (let i = 1; i <= this.range; i++) {
                offsets.push(this.currentOffset - i);
                offsets.push(this.currentOffset + i);
            }
            offsets = offsets.map(item => parseInt(item, 10));
            offsets = [...new Set(offsets)];
            offsets = offsets.filter(item => item >= 0 && item < this.total_offsets);
            offsets.sort((a, b) => a - b);
            return offsets;
        },

    },
    methods: {
        goBack() {
            this.updateOffset(this.currentOffset - 1);
        },
        goNext() {
            this.updateOffset(this.currentOffset + 1);
        },
        goTo(selected) {
            if (selected === this.currentOffset) {
                return;
            }
            this.updateOffset(selected);
        },
        updateOffset(offset) {
            this.$emit('updateOffset', parseInt(offset, 10));
        }
    }
}
</script>
