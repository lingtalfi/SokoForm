<?php


namespace SokoForm\ValidationRule;


use SokoForm\Form\SokoFormInterface;

class SokoInArrayValidationRule extends SokoValidationRule
{


    public function __construct()
    {
        parent::__construct();
        $this->preferences['array'] = [];

        $this->setValidationFunction(function ($value, array &$preferences, &$error = null, SokoFormInterface $form) {
            if (true === $this->checkSubmitted($value, $error)) {
                if (!in_array($value, $preferences['array'])) {
                    $preferences['sArray'] = implode(', ', $preferences['array']);
                    $error = "The value must be one of {sArray}";
                    return false;
                }
            } else {
                return false;
            }
            return true;
        });
    }

    public function setArray(array $array)
    {
        $this->preferences['array'] = $array;
        return $this;
    }

}