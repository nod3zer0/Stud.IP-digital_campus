const customColorPalette = [
    { color: '#000000' },
    { color: '#6c737a' }, //75%
    { color: '#a7abaf' }, //45%
    { color: '#c4c7c9' }, //30%
    { color: '#ffffff', hasBorder: true },

    { color: '#cb1800' }, //red
    { color: '#f26e00' }, //pumpkin
    { color: '#ffbd33' }, //yellow
    { color: '#8bbd40' }, // apple green
    { color: '#00962d' }, //green

    { color: '#41afaa' }, //verdigris
    { color: '#a9b6cb' }, // blue 40%
    { color: '#28497c' }, // blue
    { color: '#bf5796' }, // mulberry
    { color: '#8656a2' }, // royal purple
];

const defaultConfig = {
    fontColor: {
        colors: customColorPalette,
    },
    fontBackgroundColor: {
        colors: customColorPalette,
    },
    image: {
        resizeOptions: [
            {
                name: 'resizeImage:original',
                value: null,
                icon: 'original'
            },
            {
                name: 'resizeImage:25',
                value: '25',
                icon: 'small'
            },
            {
                name: 'resizeImage:50',
                value: '50',
                icon: 'medium'
            },
            {
                name: 'resizeImage:75',
                value: '75',
                icon: 'large'
            }
        ],
        toolbar: [
            'resizeImage:25',
            'resizeImage:50',
            'resizeImage:75',
            'resizeImage:original',
            '|',
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative',
        ],
    },
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
            { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
            { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
            { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' },
        ],
    },
    table: {
        contentToolbar: [
            'toggleTableCaption',
            'tableColumn',
            'tableRow',
            'mergeTableCells',
            'tableCellProperties',
            'tableProperties',
        ],
        tableProperties: {
            borderColors: customColorPalette,
            backgroundColors: customColorPalette,
            defaultProperties: {
                alignment: 'left',
                borderStyle: 'solid',
                borderColor: '#666666',
                borderWidth: '1px',
            },
        },
        tableCellProperties: {
            borderColors: customColorPalette,
            backgroundColors: customColorPalette,
            defaultProperties: {
                borderStyle: 'solid',
                borderColor: '#666666',
                borderWidth: '1px',
            },
        },
    },
    typing: {
        transformations: {
            remove: ['quotes'],
        },
    },
    list: {
        properties: {
            styles: true,
            startIndex: true,
            reversed: true,
        },
    },
    math: {
        engine: 'mathjax',
        outputType: 'span',
    },
    link: {
        defaultProtocol: 'https://',
    },
    // This value must be kept in sync with the language defined in webpack.config.js.
    language: 'de',
    htmlSupport: {
        allow: [
            /* HTML features to allow */
            {
                name: 'div',
                classes: 'author',
            },
            {
                name: 'pre',
                classes: 'usercode',
            },
        ],
        disallow: [
            /* HTML features to disallow */
        ],
    },
};

export { defaultConfig };
