<?php

namespace ReviewsServicesBundle\Services;


use ReviewsServicesBundle\Services\Exceptions\ParserException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ParsesServices
{
    private $services;

    public function __construct($services)
    {
        $this->services = $services;
    }

    public function run()
    {
        foreach ($this->services as $service){
            $this->execute($service);
        }
    }

    public function execute(ParserInterface $service)
    {
        try{
            $service->execute();
            return new Response('Parses!');
        }catch (ParserException $e){
            throw new HttpException(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}