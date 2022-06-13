<template>
    <div>
        <div class="formpart">
            <ul class="clean editablelist">
                <li v-for="item in sortedItems" :key="item.id" :data-type="item.type">
                    <studip-icon v-if="item.icon" :shape="item.icon" role="info" size="20" class="text-bottom" alt=""></studip-icon>
                    <input v-if="name" type="hidden" :name="name + '[]'" :value="item.value">
                    <span>{{item.name}}</span>
                    <button v-if="item.deletable" @click.prevent="deleteItem(item)" :title="$gettextInterpolate('%{ name } löschen', {name: item.name})" class="undecorated">
                        <studip-icon shape="trash" role="clickable" size="20" class="text-bottom"></studip-icon>
                    </button>
                </li>
            </ul>
            <quicksearch v-if="quicksearch" :searchtype="quicksearch" name="qs" @input="addRange" :placeholder="$gettext('Suchen')"></quicksearch>
        </div>

        <label v-if="selectable">
            <translate>Oder aus Liste auswählen:</translate>
            <select @change="quickselect" @keydown="navigate_or_select">
                <option value=""><translate>Direkt auswählen ...</translate></option>
                <template v-for="opt in selectable">
                    <optgroup v-if="opt.label && opt.options" :label="opt.label">
                        <option v-for="option in opt.options" :disabled="isSelected(option.value)" :value="JSON.stringify({value: option.value, name: option.name})">{{ option.name + (isSelected(option.value) ? ' ✓' : '') }}</option>
                    </optgroup>
                    <option v-else :disabled="isSelected(opt.value)" @click="quicksearch" :value="JSON.stringify({value: opt.value, name: opt.name})">{{ opt.name + (isSelected(option.value) ? ' ✓' : '') }}</option>
                </template>
            </select>

        </label>
    </div>
</template>

<script>
export default {
    name: 'editable-list',
    props: {
        name: {
            type: String,
            required: false
        },
        items: {
            required: false,
            type: Array
        },
        quicksearch: {
            required: false
        },
        selectable: {
            type: Array,
            required: false
        },
        category_order: {
            type: Array,
            required: false,
            default: []
        }
    },
    data () {
        return {
            resort: false, //this is just for triggering the computed property sortedItems to be sorted again
            preventChangeOfQuickselect: false
        };
    },
    methods: {
        addRange (id, name) {
            let icon = null;
            if (id.includes('__')) {
                icon = id.split('__')[1];
                id = id.split('__')[0];
            }
            let insert = true;
            for (let i in this.items) {
                if (this.items[i].value === id) {
                    insert = false;
                    break;
                }
            }

            if (insert) {
                this.items.push({
                    value: id,
                    name: name,
                    icon: icon,
                    deletable: true
                });
                this.changed();
            }
        },
        changed () {
            this.resort = !this.resort;
            this.$emit('input', this.items.map(function (item) {
                return item.value;
            }));
            this.$emit('items', this.items.map(function (item) {
                return {
                    value: item.value,
                    name: item.name,
                    icon: item.icon,
                    deletable: item.deletable
                };
            }));
        },
        quickselect (event) {
            if (event.target.value && !this.preventChangeOfQuickselect) {
                let new_value = JSON.parse(event.target.value);
                this.addRange(new_value.value, new_value.name);
                event.target.value = '';
            }
        },
        navigate_or_select (event) {
            if (['ArrowDown', 'ArrowUp', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
                //don't trigger change for 250 ms
                this.preventChangeOfQuickselect = true;
                window.setTimeout(() => {
                    this.preventChangeOfQuickselect = false;
                }, 250);
            } else if (event.key === 'Enter') {
                //select current selection
                let new_value = JSON.parse(event.target.value);
                this.addRange(new_value.value, new_value.name);
                event.target.value = '';
            }
        },
        deleteItem (item) {
            for (let i in this.items) {
                if (this.items[i].value === item.value) {
                    this.$delete(this.items, i);
                }
            }
            this.changed();
        },
        isSelected (id) {
            if (id.includes('__')) {
                id = id.split('__')[0];
            }
            for (let i in this.items) {
                if (this.items[i].value === id) {
                    return true;
                }
            }
            return false;
        }
    },
    computed: {
        sortedItems () {
            let v = this;
            let i = this.resort;
            let items = this.items.sort(function (a, b) {
                if (a.icon === b.icon) {
                    return a.name.localeCompare(b.name);
                } else {
                    let a_icon = a.icon || '';
                    let b_icon = b.icon || '';
                    if (v.category_order.indexOf(a_icon) > -1 && v.category_order.indexOf(b_icon) > -1) {
                        return v.category_order.indexOf(a_icon) < v.category_order.indexOf(b_icon) ? -1 : 1;
                    } else {
                        return a_icon.localeCompare(b_icon);
                    }
                }
            });
            return items;
        }
    },
    mounted () {
        this.$emit('input', this.items.map(function (item) {
            return item.value;
        }));
    }
}
</script>