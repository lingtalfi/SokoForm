<?php


namespace SokoForm\Form;


use Bat\ClassTool;
use Bat\StringTool;
use SokoForm\Control\SokoControlInterface;
use SokoForm\Control\SokoFileControl;
use SokoForm\Control\SokoInputControl;
use SokoForm\Exception\SokoFormException;
use SokoForm\ValidationRule\SokoValidationRuleInterface;

class SokoForm implements SokoFormInterface
{
    private $name; // the name of the form
    private $method;
    private $action;
    private $enctype;
    private $controls;
    private $validationRules;
    private $notifications;

    /**
     *
     * The two properties below:
     *
     * - validationRulesLang, string|null, default=eng
     * - validationRulesTranslator, callable|null, default=null
     *
     * define how this soko form translates the validation rules error messages.
     *
     * If both are null, no translation will be used (and the default error messages are in plain english).
     * If the validationRulesTranslator is set, it has precedence over the validationRulesLang property.
     * If the validationRulesTranslator is set, it's a callback responsible for translating the error message
     *      it receives as its first argument.
     *      The second argument is an array of possible tags to use to translate the message.
     *
     *          Here is the signature:
     *          string:translatedErrorMessage  translatorFn ( string:errorMessage, array:tags )
     *
     * If the validationRulesTranslator is null and the validationRules is a string,
     * then this soko form will use its internal soko validation rule translation system.
     * If validationRulesLang is a string, the string represents the ISO 639-2 code (3 letters code)
     *          of the lang to use for the validation rules error messages.
     *
     * Note: if your lang is missing, please consider to the translation and send them me to me,
     * so that soko can help people of your country too.
     *
     *
     *
     *
     * @var null|string, (default=eng)
     */
    private $validationRulesLang;
    /**
     * @var null|callback
     */
    private $validationRulesTranslator;

    /**
     * @var bool, when this flag becomes true, it means that:
     * - an extra control with the form name as its name has been
     *   added to the existing controls.
     *  This extra control is how soko form knows whether or not the form
     *  is submitted.
     */
    private $prepared;
    private $model;

    public function __construct()
    {
        $this->name = "sokoform";
        $this->method = "post";
        $this->action = "";
        $this->enctype = null;
        $this->controls = [];
        $this->notifications = [];
        $this->validationRules = [];
        $this->prepared = false;
        $this->model = null;
    }

    public static function create()
    {
        return new static();
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getEnctype()
    {
        return $this->enctype;
    }

    public function getFormAttributesAsString()
    {
        $attr = [
            'method' => $this->method,
            'action' => $this->action,
        ];
        if (null !== $this->enctype) {
            $attr['enctype'] = $this->enctype;
        }
        return StringTool::htmlAttributes($attr);
    }

    public function getControl($controlName, $throwEx = true, $default = null)
    {
        $this->prepare();
        if (array_key_exists($controlName, $this->controls)) {
            return $this->controls[$controlName];
        }
        if (true === $throwEx) {
            throw new SokoFormException("The control $controlName does not exist");
        }
        return $default;
    }

    public function getControls()
    {
        $this->prepare();
        return $this->controls;
    }

    public function process(callable $onSuccess, array $context = null)
    {
        $this->prepare();

        //--------------------------------------------
        // PREPARE THE CONTEXT
        //--------------------------------------------
        if (null === $context) {
            $context = [];
            if ('post' === $this->method) {
                $context = $_POST;
                /**
                 * Reminder: The enctype attribute can be used only if method="post".
                 * Source: https://www.w3schools.com/tags/att_form_enctype.asp
                 */
                if ('multipart/form-data' === $this->enctype) {
                    $context = array_replace($context, $_FILES);
                }
            } else {
                $context = $_GET;
            }
        }

        //--------------------------------------------
        // CHECKING WHETHER OR NOT THE FORM IS SUBMITTED
        //--------------------------------------------
        if (array_key_exists($this->name, $context)) { // now the form is posted

            /**
             * Note: I'm not sure whether the context should be filtered,
             * but I believe this is not a bad idea
             */
            $filteredContext = [];

            //--------------------------------------------
            // USING VALIDATION RULES TO VALIDATE THE FORM
            //--------------------------------------------
            $formIsValid = true;
            foreach ($this->controls as $name => $control) {

                /**
                 * @var $control SokoControlInterface
                 */
                if (array_key_exists($name, $this->validationRules)) {
                    $validationRules = $this->validationRules[$name];
                    foreach ($validationRules as $validationRule) {
                        /**
                         * @var $validationRule SokoValidationRuleInterface
                         */
                        $validationFn = $validationRule->getValidationFunction();
                        $preferences = $validationRule->getPreferences();
                        if (array_key_exists($name, $context)) {
                            $value = $context[$name];
                        } else {
                            /**
                             * It's important to set non posted value to null
                             * because validation rules rely on this convention.
                             */
                            $value = null;
                        }
                        $error = "";

                        $valueIsValid = call_user_func_array($validationFn, [
                            $value,
                            &$preferences,
                            &$error,
                            $this,
                        ]);

                        // in case of failure, we translate the error message(s) and
                        // attach them to the control so that they are available to the view
                        if (false === $valueIsValid) {

                            /**
                             * tags are like preferences, except that they can only hold scalar values (i.e. not arrays).
                             * Let's filter the non scalar preferences now...
                             */
                            $tags = array_filter($preferences, function ($v) {
                                return is_scalar($v);
                            });

                            $error = $this->translateError($error, $tags);
                            $control->addError($error);
                            $formIsValid = false;
                        }

                    }
                }

                if (array_key_exists($name, $context)) {
                    $filteredContext[$name] = $context[$name];

                    //--------------------------------------------
                    // INJECTING NEW VALUES IN THE CONTROLS (data persistency)
                    //--------------------------------------------
                    $control->setValue($context[$name]);
                }

            }

            //--------------------------------------------
            // NOW THE FORM IS SUBMITTED AND VALID,
            // WE CAN JUST CALL THE SUCCESS CALLBACK
            //--------------------------------------------
            if (true === $formIsValid) {
                call_user_func($onSuccess, $filteredContext, $this);
            }

        }
    }

    public function addNotification($message, $type, $title = null)
    {
        $this->notifications[] = [
            'title' => $title,
            'type' => $type,
            'msg' => $message,
        ];
        return $this;
    }


    public function getModel()
    {
        $this->prepare();
        if (null === $this->model) {

            //--------------------------------------------
            // PREPARING FORM PART
            //--------------------------------------------
            $formAttributes = [
                'method' => $this->method,
                'action' => $this->action,
            ];
            if (null !== $this->enctype) {
                $formAttributes['enctype'] = $this->enctype;
            }
            $attrString = StringTool::htmlAttributes($formAttributes);


            //--------------------------------------------
            // NOW PREPARING CONTROL PARTS
            //--------------------------------------------
            $controls = [];
            foreach ($this->controls as $name => $control) {
                /**
                 * @var $control SokoControlInterface
                 */
                $controls[$name] = $control->getModel();
                $controls[$name]['class'] = ClassTool::getShortName($control);
            }


            $this->model = [
                'form' => [
                    'name' => $this->name,
                    'method' => $this->method,
                    'action' => $this->action,
                    'enctype' => $this->enctype,
                    'attributeString' => $attrString,
                    'errors' => [],
                    'notifications' => $this->notifications,
                ],
                'controls' => $controls,
            ];
        }

        return $this->model;
    }



    //--------------------------------------------
    //
    //--------------------------------------------

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setEnctype($enctype)
    {
        $this->enctype = $enctype;
        return $this;
    }


    /**
     * @param SokoControlInterface $control , a fully configured
     *                          control (i.e. at least name set)
     * @return $this
     */
    public function addControl(SokoControlInterface $control)
    {
        /**
         * Note: this only works if the name of the control is not set after the
         * call to addControl... should be the case in most situations but be aware
         * that this might fail in the future maybe.
         *
         * Or we could just say that this method accepts only fully configured controls
         * (and actually I just did).
         */
        $this->controls[$control->getName()] = $control;

        /**
         * automatically add the enctype if the form contains a regular file control
         */
        if ($control instanceof SokoFileControl) {
            $type = $control->getType();
            if ('static' === $type) {
                $this->setEnctype("multipart/form-data");
            }
        }

        return $this;
    }


    public function addValidationRule($controlName, SokoValidationRuleInterface $validationRule)
    {
        $this->validationRules[$controlName][] = $validationRule;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


    public function setValidationRulesLang($validationRulesLang)
    {
        $this->validationRulesLang = $validationRulesLang;
        return $this;
    }

    public function setValidationRulesTranslator(callable $validationRulesTranslator)
    {
        $this->validationRulesTranslator = $validationRulesTranslator;
        return $this;
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    protected function onTranslationFileNotFound($lang) // override me
    {

    }


    //--------------------------------------------
    //
    //--------------------------------------------
    private function prepare()
    {
        if (false === $this->prepared) {
            $this->prepared = true;
            $this->addControl(SokoInputControl::create()
                ->setName($this->name)
                ->setType("hidden")
                ->setValue(1)
            );
        }
    }

    private function translateError($error, array $tags)
    {
        if (null !== $this->validationRulesTranslator) {
            return call_user_func($this->validationRulesTranslator, $error, $tags);
        } elseif (null !== $this->validationRulesLang) {
            $file = __DIR__ . "/../assets/validation-rules-lang/" . $this->validationRulesLang . ".php";
            if (file_exists($file)) {
                $translations = [];
                include $file;
                if (array_key_exists($error, $translations)) {
                    $translation = $translations[$error];
                    $keys = array_keys($tags);
                    $values = array_values($tags);
                    $keys = array_map(function ($v) {
                        return "{" . $v . "}";
                    }, $keys);
                    return str_replace($keys, $values, $translation);

                } else {
                    $this->onTranslationFileNotFound($this->validationRulesLang);
                }
            }
        }
        return $error;
    }

}