<?php

namespace ReviewsServicesBundle\Services;


interface ParserInterface
{
    public function execute();

    public function prepareReview($data);

}