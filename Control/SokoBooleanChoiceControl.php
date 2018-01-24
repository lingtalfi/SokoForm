<?php


namespace SokoForm\Control;


class SokoBooleanChoiceControl extends SokoControl
{

    protected $choice;


    public function __construct()
    {
        parent::__construct();
        $this->choice = null;
    }

    public function setChoice($choice)
    {
        $this->choice = $choice;
        return $this;
    }

    public function setValue($value)
    {
        $value = (int)$value;
        return parent::setValue($value);
    }

    //--------------------------------------------
    //
    //--------------------------------------------
    protected function getSpecificModel() // override me
    {
        $ret = [
            "type" => "list",
            "choices" => [
                1 => $this->choice,
            ],
        ];
        return $ret;
    }


}