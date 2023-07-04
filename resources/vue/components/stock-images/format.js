export const getFormat = (mimeType) => {
    switch (mimeType) {
        case 'image/gif':
            return 'GIF';
        case 'image/jpeg':
            return 'JPEG';
        case 'image/png':
            return 'PNG';
        case 'image/webp':
            return 'WebP';
        default:
            return '???';
    }
};
