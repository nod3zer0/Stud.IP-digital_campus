<?php
class FeatureDisabledException extends StudipException
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (func_num_args() === 0) {
            $message = _('Diese Funktion ist ausgeschaltet.');
        }
        parent::__construct($message, [], $code, $previous);
    }
}
