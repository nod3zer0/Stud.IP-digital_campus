class Screenreader
{
    static notify(text) {
        $('#notes_for_screenreader').text(text);
    }
}

export default Screenreader;
