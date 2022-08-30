export default function SpecialCharactersEmojiObjects( editor ) {
    editor.plugins.get( 'SpecialCharacters' ).addItems( 'Emoji Objects', [
        { title: '', character: '' },
    ] );
}