<?php


namespace Ling\SokoForm\ValidationRule;


use Ling\Bat\ValidationTool;
use Ling\SokoForm\Control\SokoControlInterface;
use Ling\SokoForm\Form\SokoFormInterface;
use Ling\SokoForm\Translator\SokoValidationRuleTranslator;

class SokoFileNotEmptyValidationRule extends SokoValidationRule
{


    public function __construct()
    {
        parent::__construct();


        $this->setErrorMessage(SokoValidationRuleTranslator::getValidationMessageTranslation("fileNotEmpty"));

        $this->setValidationFunction(function ($value, array &$preferences, &$error = null, SokoFormInterface $form, SokoControlInterface $control) {
            if (true === $this->checkSubmitted($value, $error)) {
                if (is_array($value) && array_key_exists("tmp_name", $value)) {
                    if ('' !== trim($value['tmp_name'])) {
                        return true;
                    }
                }
            } else {
                $error = $this->getErrorMessage();
                return false;
            }
            $error = $this->getErrorMessage();
            return false;
        });
    }
}