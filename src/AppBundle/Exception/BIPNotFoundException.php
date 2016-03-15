<?php
namespace AppBundle\Exception;

class BIPNotFoundException extends \Exception implements BIPNotFoundExceptionInterface{
    public $test;
    public $redirectResponse; //is a \Symfony\Component\HttpFoundation\RedirectResponse
}