<?php


namespace SokoForm\Renderer\Ling;


use Bat\ClassTool;
use Module\Ekom\Utils\E;

class WithParsleyUikitSokoFormRenderer extends UikitSokoFormRenderer
{

    protected $validationRules;

    public function __construct()
    {
        parent::__construct();
        $this->validationRules = [];
    }


    public function renderForm(array $form, array $options = [])
    {

        $notification = $options['notification'] ?? [

            ];

        $this->prepareExtraAttributes($form);
//        $this->renderNotifications([$notification]);
        parent::renderForm($form, $options);
    }


    protected function extraAttributes(string $methodName, array $control, $extra = null)
    {
//        E::dlog("$methodName");
        $controlName = $control['name'];
        if (array_key_exists($controlName, $this->validationRules)) {

            /**
             * For now, we only consider the first validation rule.
             */
            $classInstance = $this->validationRules[$controlName][0];

            $className = ClassTool::getShortName($classInstance);

            switch ($className) {
                case "SokoNotEmptyValidationRule":
                case "SokoFileNotEmptyValidationRule":
                    $arr = [
                        "renderInputSokoInputControl",
                        "renderTextareaSokoInputControl",
                        "renderSelectSokoChoiceControl",
                        "renderCheckboxSokoChoiceControl",
                        "renderInputStaticFileSokoInputControl",
                        "renderInputAjaxFileSokoInputControl",
                    ];
                    if (true === in_array($methodName, $arr)) {
                        echo "required";
                    }
                    break;
                default:
                    break;
            }
        }
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    private function prepareExtraAttributes(array $form)
    {
        $this->validationRules = $form['validationRules'];
    }

}