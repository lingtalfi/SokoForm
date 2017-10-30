<?php


namespace SokoForm\ValidationRule;


use SokoForm\Form\SokoFormInterface;

class SokoValidationRule implements SokoValidationRuleInterface
{
    protected $preferences;
    private $validationFunction;


    public function __construct()
    {
        $this->preferences = [];
        $this->validationFunction = function ($value, array &$preferences, &$error = null, SokoFormInterface $form) {
            return true;
        };
    }

    public static function create()
    {
        return new static();
    }

    /**
     * @return array, the factory preferences.
     *              Note: when the SokoForm validates the control,
     *              it deals with a more dynamic type of preferences,
     *              which is the factory preferences, plus the potential dynamic/runtime tags
     *              added to it via the validationFunction.
     *
     */
    public function getPreferences()
    {
        return $this->preferences;
    }

    /**
     * @return \Closure
     */
    public function getValidationFunction()
    {
        return $this->validationFunction;
    }

    //--------------------------------------------
    //
    //--------------------------------------------
    public function setPreferences($preferences)
    {
        $this->preferences = $preferences;
        return $this;
    }


    public function setValidationFunction($validationFunction)
    {
        $this->validationFunction = $validationFunction;
        return $this;
    }

    //--------------------------------------------
    //
    //--------------------------------------------
    protected function checkSubmitted($value, &$error)
    {
        if (null === $value) {
            $error = "This field is mandatory";
            return false;
        }
        return true;
    }
}