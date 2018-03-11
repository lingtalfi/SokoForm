<?php


namespace SokoForm\Control;


/**
 *
 * Based on jqueryUi comboBox
 * https://jqueryui.com/autocomplete/#combobox
 *
 *
 * It works only with simple maps (arrays of key => value)
 *
 *
 * This class will be able to accept a list of options,
 * or work with ajax loaded options.
 *
 */
class SokoComboBoxControl extends SokoControl
{

    protected $choices;

    public function __construct()
    {
        parent::__construct();
        $this->choices = [];
    }

    public function setChoices(array $choices)
    {
        $this->choices = $choices;
        return $this;
    }


    public function getChoices()
    {
        return $this->choices;
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    protected function getSpecificModel() // override me
    {
        $ret = [
            "type" => 'list',
            "choices" => $this->choices,
        ];
        return $ret;
    }

}