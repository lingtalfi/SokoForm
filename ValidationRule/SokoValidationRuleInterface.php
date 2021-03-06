<?php


namespace Ling\SokoForm\ValidationRule;


interface SokoValidationRuleInterface
{

    /**
     * @return array of name => value,
     */
    public function getPreferences();

    /**
     * @return \Closure, function returning a boolean, and which signature is:
     *
     *                      function ($value, array &$preferences, &$error = null, SokoFormInterface $form, SokoControlInterface $control, array $context)
     */
    public function getValidationFunction();
}