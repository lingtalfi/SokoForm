<?php


namespace SokoForm\ValidationRule;


use SokoForm\Form\SokoFormInterface;

class SokoSameAsValidationRule extends SokoValidationRule
{


    public function __construct()
    {
        parent::__construct();
        $this->preferences['otherControl'] = null;

        $this->setValidationFunction(function ($value, array &$preferences, &$error = null, SokoFormInterface $form) {
            if (true === $this->checkSubmitted($value, $error)) {
                $otherControl = $preferences['otherControl'];
                if (false !== $control = $form->getControl($otherControl, false, false)) {
                    $otherValue = $control->getValue();
                    if ($otherValue !== $value) {
                        $error = "The two values aren't identical";
                        return false;
                    }
                } else {
                    // the expected control doesn't exist
                    $error = "The control {otherControl} does not exist";
                    return false;
                }
            } else {
                return false;
            }
            return true;
        });
    }


    public function setSameAs($otherControl)
    {
        $this->preferences["otherControl"] = $otherControl;
        return $this;
    }
}