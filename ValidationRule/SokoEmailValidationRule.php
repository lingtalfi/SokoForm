<?php


namespace SokoForm\ValidationRule;


use Bat\ValidationTool;
use SokoForm\Form\SokoFormInterface;

class SokoEmailValidationRule extends SokoValidationRule
{


    public function __construct()
    {
        parent::__construct();

        $this->setValidationFunction(function ($value, array &$preferences, &$error = null, SokoFormInterface $form) {
            if (true === $this->checkSubmitted($value, $error)) {
                if (false === ValidationTool::isEmail($value)) {
                    $error = "The email is invalid";
                    return false;
                }
            } else {
                return false;
            }
            return true;
        });
    }
}