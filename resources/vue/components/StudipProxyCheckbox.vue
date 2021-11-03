<script>
let uuid = 0;
export default {
    name: 'studip-proxy-checkbox',
    model: {
        prop: 'selected',
        event: 'change',
    },
    props: {
        id: String,
        total: {
            type: Array,
            required: true
        },
       selected: {
            type: Array,
            required: true,
        }
    },
    methods: {
        changeProxy () {
            this.$emit('change', this.checked ? [] : [...this.total] );
        }
    },
    computed: {
        proxyId () {
            return this.id ?? `proxy-checkbox-${uuid++}`;
        },
        checked () {
            return this.selected.length === this.total.length;
        },
        indeterminate () {
            return this.selected.length > 0 && this.selected.length !== this.total.length;
        }
    },
    render (createElement) {
        const checkbox = createElement('input', {
            attrs: {
                type: 'checkbox',
                name: this.name,
                id: this.proxyId
            },
            domProps: {
                checked: this.checked,
                indeterminate: this.indeterminate,
            },
            on: {
                change: this.changeProxy,
            }
        });

        return checkbox;
    }
};
</script>
