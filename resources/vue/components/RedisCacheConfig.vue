<template>
    <div>
        <label class="col-4">
            <span class="required">
                {{ $gettext('Hostname') }}
            </span>
            <input required type="text" name="hostname" placeholder="localhost" v-model="theHostname">
        </label>
        <label class="col-2">
            <span class="required">
                {{ $gettext('Port') }}
            </span>
            <input required type="text" name="port" placeholder="6379" v-model="thePort">
        </label>
        <label>
            {{ $gettext('Passwort/Token zur Authentifizierung') }}
            <input type="text" name="auth" v-model="theAuth">
        </label>
    </div>
</template>

<script>
export default {
    name: 'RedisCacheConfig',
    props: {
        hostname: {
            type: String,
            default: 'localhost'
        },
        port: {
            type: Number,
            default: 6379
        },
        auth: {
            type: String,
            default: ''
        }
    },
    data () {
        return {
            theHostname: this.hostname,
            thePort: this.port,
            theAuth: this.auth
        }
    },
    methods: {
        isValid () {
            return this.theHostname.trim().length > 0
                && !isNaN(parseInt(this.thePort, 10));
        }
    },
    watch: {
        theHostname: {
            handler (current) {
                this.$emit('is-valid', this.isValid());
            },
            immediate: true
        },
        thePort: {
            handler (current) {
                this.$emit('is-valid', this.isValid());
            },
            immediate: true
        }
    }
}
</script>
