<?php


namespace SokoForm\Control;


/**
 * The idea is to display a form control with two sides:
 * one on the left and one on the right.
 *
 * Both sides contain one unfolded (multiple) list,
 * with a button underneath each list to pass items from one side to the other.
 * Each list can be capped by a title.
 *
 */
class SokoTennisListChoiceControl extends SokoChoiceControl
{

    protected $negativeChoices;


    public function __construct()
    {
        parent::__construct();
        $this->negativeChoices = [];
    }

    public function setNegativeChoices(array $negativeChoices)
    {
        $this->negativeChoices = $negativeChoices;
        return $this;
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    protected function getSpecificModel() // override me
    {
        $ret = [
            "type" => "list",
            "choices" => $this->choices,
            "negativeChoices" => $this->negativeChoices,
        ];
        return $ret;
    }


}