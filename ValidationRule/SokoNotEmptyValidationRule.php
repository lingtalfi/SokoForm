<?php


namespace SokoForm\ValidationRule;


use Bat\ValidationTool;
use SokoForm\Form\SokoFormInterface;

class SokoNotEmptyValidationRule extends SokoValidationRule
{


    public function __construct()
    {
        parent::__construct();

        $this->setValidationFunction(function ($value, array &$preferences, &$error = null, SokoFormInterface $form) {
            if (true === $this->checkSubmitted($value, $error)) {
                if (empty($value)) {
                    $error = "The field is required";
                    return false;
                }
            } else {
                return false;
            }
            return true;
        });
    }
}