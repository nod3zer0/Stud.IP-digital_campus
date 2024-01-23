import { $gettext } from '@/assets/javascripts/lib/gettext';
import { fromHex, rgbToCIELab, cie94 } from 'colorpare';

const SQUARE_DELTA = 1.1;

const isLandscape = ({ attributes: { width, height } }) => {
    if (!(width > 0 && height > 0)) {
        return false;
    }
    return width > SQUARE_DELTA * height;
};

const isPortrait = ({ attributes: { width, height } }) => {
    if (!(width > 0 && height > 0)) {
        return false;
    }
    return height > SQUARE_DELTA * width;
};

const isSquare = ({ attributes: { width, height } }) => {
    if (!(width > 0 && height > 0)) {
        return false;
    }
    return Math.max(width / height, height / width) <= SQUARE_DELTA;
};

export const orientations = {
    any: {
        text: $gettext('Beliebige Ausrichtung'),
        filter: () => true,
    },
    landscape: { text: $gettext('Querformat'), filter: isLandscape },
    portrait: { text: $gettext('Hochformat'), filter: isPortrait },
    square: { text: $gettext('Quadrat'), filter: isSquare },
};

const SIMILARITY = 15;
const toHex = (hashHex) => hashHex.substr(1);
const toLab = (color) => color.lab();
const toRGB = ([r, g, b]) => ({ r, g, b });

export const similarColors = (filterColors) => {
    if (!filterColors.length) {
        return () => true;
    }
    const labColors = filterColors.map((color) => toLab(fromHex(toHex(color))));
    const isSimilar = (color) => labColors.some((labColor) => cie94(labColor, color) < SIMILARITY);

    return ({ attributes: { palette } }) => palette.map(toRGB).map(rgbToCIELab).some(isSimilar);
};

const filter = (stockImages, filters) => {
    const orientation = orientations[filters.orientation] ?? orientations.any;
    const colors = similarColors(filters.colors ?? []);

    return stockImages.filter(orientation.filter).filter(colors);
};

const search = (stockImages, query) => {
    if (!query.trim().length) {
        return stockImages;
    }

    return stockImages.filter(({ attributes: { title, description, author, tags } }) => {
        return [title, description, author, tags].some((field) => field.includes(query));
    });
};

const sort = (stockImages) => _.sortBy([...stockImages], 'attributes.title');

export const searchFilterAndSortImages = (stockImages, query, filters) => sort(filter(search(stockImages, query), filters));
