<?php

namespace ReviewsServicesBundle\Services\Exceptions;


class ParserException extends \Exception
{
    public function __construct($message = null, $code = 0) {
        if($message === null){
            $message = 'Service '.substr(strrchr(get_called_class(), '\\'), 1).' dosn\'t work';
        };
        parent::__construct($message, $code);
    }

}