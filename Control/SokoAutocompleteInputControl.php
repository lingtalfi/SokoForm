<?php


namespace SokoForm\Control;


class SokoAutocompleteInputControl extends SokoInputControl
{

    protected $uri;
    protected $queryParam;


    public function __construct()
    {
        parent::__construct();
        $this->uri = null; // required
        $this->queryParam = "q";
    }
}