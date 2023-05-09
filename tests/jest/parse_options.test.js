import parseOptions from "../../resources/assets/javascripts/lib/parse_options";

describe('parse_options()', () => {
    test('empty', () => {
        expect(parseOptions('')).toStrictEqual({});
    })

    test('boolean', () => {
        expect(parseOptions('bool')).toStrictEqual({bool: true});
        expect(parseOptions('bool=false')).toStrictEqual({bool: false});
        expect(parseOptions('foo;bar=false;baz=true')).toStrictEqual({
            foo: true,
            bar: false,
            baz: true,
        });
    })

    test('string', () => {
        expect(parseOptions('size=auto')).toStrictEqual({size: 'auto'});
        expect(parseOptions('size="auto"')).toStrictEqual({size: 'auto'});
        expect(parseOptions('size=auto;close=none')).toStrictEqual({
            size: 'auto',
            close: 'none',
        });
    })

    test('int', () => {
        expect(parseOptions('foo=42')).not.toStrictEqual({foo: 42});
        expect(parseOptions('foo=+42')).toStrictEqual({foo: 42});
        expect(parseOptions('foo=-42')).toStrictEqual({foo: -42});
        expect(parseOptions('foo=+42;bar=-23')).toStrictEqual({foo: 42, bar: -23});
    })

    test('float', () => {
        expect(parseOptions('foo=42.23')).not.toStrictEqual({foo: 42.23});
        expect(parseOptions('foo=+42.23')).toStrictEqual({foo: 42.23});
        expect(parseOptions('foo=-42.23')).toStrictEqual({foo: -42.23});
        expect(parseOptions('foo=+42.23;bar=-23.42')).toStrictEqual({
            foo: 42.23,
            bar: -23.42,
        });
    })

    test('mixed', () => {
        const parsed = parseOptions('size=auto;reload-on-close;foo=+42;bar=-42.23');
        expect(parsed).toStrictEqual({
            size: 'auto',
            'reload-on-close': true,
            foo: 42,
            bar: -42.23,
        });
    });

    test('invalid', () => {
        expect(() => {
            parseOptions('foo="bar')
        }).toThrow('Invalid data, missing closing quote')
    })
});
