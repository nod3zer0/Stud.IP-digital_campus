/**
 * @jest-environment jsdom
 */

import extractCallback from "../../resources/assets/javascripts/lib/extract_callback";

describe('extract_callback()', () => {
    test('simple', () => {
        const callback = jest.fn();
        const extracted = extractCallback('callback', [], {
            callback
        });

        expect(typeof extracted).toBe('function');

        extracted();

        expect(callback).toHaveBeenCalled();
    })

    test('simple with payload', () => {
        const callback = jest.fn();
        const extracted = extractCallback('callback', ['foo', 23], {
            callback
        });

        expect(typeof extracted).toBe('function');

        extracted();

        expect(callback).toHaveBeenCalledWith(['foo', 23]);
    })

    test('nested', () => {
        const callback = jest.fn();
        const extracted = extractCallback('foo.bar.baz.callback', {}, {
            foo: {
                bar: {
                    baz: {
                        callback
                    }
                }
            }
        });

        expect(typeof extracted).toBe('function');

        extracted();

        expect(callback).toHaveBeenCalled();
    })

    test('nested with payload', () => {
        const callback = jest.fn();
        const extracted = extractCallback('foo.bar.baz.callback', ['foo', 23], {
            foo: {
                bar: {
                    baz: {
                        callback
                    }
                }
            }
        });

        expect(typeof extracted).toBe('function');

        extracted();

        expect(callback).toHaveBeenCalledWith(['foo', 23]);
    })

    test('complex', () => {
        const callback = jest.fn();
        const extracted = extractCallback('foo(42.23).callback', [], {
            foo () {
                return {callback};
            }
        });

        expect(typeof extracted).toBe('function');

        extracted();

        expect(callback).toHaveBeenCalled();
    });

    test('parameters', () => {
        const callback = jest.fn();
        const extracted = extractCallback('callback("foo", 23)', [], {
            callback
        });

        expect(typeof extracted).toBe('function');

        extracted();

        expect(callback).toHaveBeenCalledWith('foo', 23);
    });

    test('json with payload', () => {
        const callback = jest.fn();
        const extracted = extractCallback('{"func":"callback","payload":["foo",23]}', [], {callback});

        expect(typeof extracted).toBe('function');

        extracted();

        expect(callback).toHaveBeenCalledWith(['foo', 23]);
    })


    test('invalid', () => {
        expect(() => {
            extractCallback('callback', {}, {});
        }).toThrow();

        expect(() => {
            extractCallback('{}', {}, {});
        }).toThrow();
    })
});
