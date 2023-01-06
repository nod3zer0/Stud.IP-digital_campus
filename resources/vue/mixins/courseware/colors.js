const colorMixin = {
    computed: {
        mixinColors() {
            const colors = [
                {
                    name: this.$gettext('Schwarz'),
                    class: 'black',
                    hex: '#000000',
                    level: 100,
                    icon: 'black',
                    darkmode: true,
                },
                {
                    name: this.$gettext('Weiß'),
                    class: 'white',
                    hex: '#ffffff',
                    level: 100,
                    icon: 'white',
                    darkmode: false,
                },

                {
                    name: this.$gettext('Blau'),
                    class: 'studip-blue',
                    hex: '#28497c',
                    level: 100,
                    icon: 'blue',
                    darkmode: true,
                },
                {
                    name: this.$gettext('Hellblau'),
                    class: 'studip-lightblue',
                    hex: '#e7ebf1',
                    level: 40,
                    icon: 'lightblue',
                    darkmode: false,
                },
                {
                    name: this.$gettext('Rot'),
                    class: 'studip-red',
                    hex: '#d60000',
                    level: 100,
                    icon: 'red',
                    darkmode: false,
                },
                {
                    name: this.$gettext('Grün'),
                    class: 'studip-green',
                    hex: '#008512',
                    level: 100,
                    icon: 'green',
                    darkmode: true,
                },
                {
                    name: this.$gettext('Gelb'),
                    class: 'studip-yellow',
                    hex: '#ffbd33',
                    level: 100,
                    icon: 'yellow',
                    darkmode: false,
                },
                {
                    name: this.$gettext('Grau'),
                    class: 'studip-gray',
                    hex: '#636a71',
                    level: 100,
                    icon: 'grey',
                    darkmode: true,
                },

                {
                    name: this.$gettext('Holzkohle'),
                    class: 'charcoal',
                    hex: '#3c454e',
                    level: 100,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Königliches Purpur'),
                    class: 'royal-purple',
                    hex: '#8656a2',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Leguangrün'),
                    class: 'iguana-green',
                    hex: '#66b570',
                    level: 60,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Königin blau'),
                    class: 'queen-blue',
                    hex: '#536d96',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Helles Seegrün'),
                    class: 'verdigris',
                    hex: '#41afaa',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Maulbeere'),
                    class: 'mulberry',
                    hex: '#bf5796',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Kürbis'),
                    class: 'pumpkin',
                    hex: '#f26e00',
                    level: 100,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Sonnenschein'),
                    class: 'sunglow',
                    hex: '#ffca5c',
                    level: 80,
                    icon: false,
                    darkmode: false,
                },
                {
                    name: this.$gettext('Apfelgrün'),
                    class: 'apple-green',
                    hex: '#8bbd40',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
            ];
            return colors;
        }
    },
};

export default colorMixin;