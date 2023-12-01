<?php

class CoursewarePDFCertificate extends TCPDF
{
    protected $background;
    protected $isCustomBackground = false;

    public function __construct($background = false, $orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8')
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, false);

        $fileRef = null;
        if ($background !== false) {
            $fileRef = FileRef::find($background);
        }
        $this->background = $fileRef !== null ? $fileRef->file->getPath() :
            $GLOBALS['STUDIP_BASE_PATH'] . '/public/assets/images/pdf/pdf_default_background.jpg';
        $this->isCustomBackground = $fileRef !== null;

        $this->setDefaults();

        $fontname = TCPDF_FONTS::addTTFfont(
            $GLOBALS['STUDIP_BASE_PATH'] . '/public/assets/fonts/LatoLatin/LatoLatin-Regular.ttf');
        $this->setFont($fontname, '', 50);
    }

    public function Header()
    {
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);
        list($width, $height) = getimagesize($this->background);
        $this->Image($this->background, $this->isCustomBackground ? 10 : 0, $this->isCustomBackground ? 10 : 0,
            min($this->getPageWidth(), $width / 10), min($this->getPageHeight(), $height / 10),
            '', '', '', false, 300, '', false, false, 0);
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        $this->setPageMark();
    }

    private function setDefaults()
    {
        $this->SetTopMargin(50);
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
    }
}
