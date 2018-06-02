<?php


namespace SokoForm\Renderer\Ling;


use Bat\StringTool;

class UikitSokoFormRenderer
{


    public function __construct()
    {
        //
    }

    public static function create()
    {
        return new static();
    }


    /**
     * The uikit css library has to been called prior to displaying this form.
     */
    public function renderForm(array $form, array $options = [])
    {


        //--------------------------------------------
        // OPTIONS
        //--------------------------------------------
        $title = $options['title'] ?? null;
        $size = $options['size'] ?? null; // small, medium, large
        $style = $options['style'] ?? "stacked"; // stacked, horizontal
        $cssClass = $options['class'] ?? null;
        $submitButtonText = $options['submitButtonText'] ?? "Submit";
        $noValidate = $options['noValidate'] ?? false;
        $topContent = $options['topContent'] ?? "";


        //--------------------------------------------
        // SCRIPT
        //--------------------------------------------
        $formProps = $form['form'];
        $controls = $form['controls'];
        $notifications = $formProps['notifications'];
        $attributes = $formProps['attributes'];
        $curClass = "";
        if ($cssClass) {
            $curClass = $attributes['class'] ?? "";
            $curClass .= " " . $cssClass;
        }
        $curClass .= " uk-form-$style";
        if (null !== $size) {
            $curClass .= " uk-form-width-$size";
        }
        $attributes['class'] = $curClass;

        // success, info, error, warning
        if ($notifications) {
            $this->renderNotifications($notifications);
        }
        ?>

        <?php echo $topContent; ?>

        <form
                method="<?php echo $formProps['method']; ?>"
                action="<?php echo $formProps['action']; ?>"
            <?php if (null !== $formProps['enctype']): ?>
                enctype="<?php echo $formProps['enctype']; ?>"
            <?php endif; ?>
            <?php echo StringTool::htmlAttributes($attributes) ?>

            <?php if (true === $noValidate): ?>
                novalidate
            <?php endif; ?>

        >


            <fieldset class="uk-fieldset">

                <?php if ($title): ?>
                    <legend class="uk-legend"><?php echo $title; ?></legend>
                <?php endif; ?>


                <?php foreach ($controls as $control):
                    $controlClass = $control['class'];
                    $cssControlClass = "";
                    if ("SokoChoiceControl" === $controlClass) {

                        $properties = $control['properties'];
                        $style = $properties['style'] ?? 'select';
                        if ("radio" === $style) {
                            $cssControlClass = "myuk-radio-container";
                        }

                        $errors = $control['errors'];
                        $hasError = (count($errors) > 0);
                        if ($hasError) {
                            $cssControlClass .= " uk-form-danger";
                        }
                    }


                    ?>
                    <div class="uk-margin">
                        <div class="uk-form-label"><?php echo $control['label']; ?></div>
                        <div class="uk-form-controls <?php echo $cssControlClass; ?>">
                            <?php
                            switch ($controlClass) {
                                case "SokoInputControl":
                                    $this->renderSokoInputControl($control);
                                    break;
                                case "SokoChoiceControl":
                                    $this->renderSokoChoiceControl($control);
                                    break;
                                case "SokoFileControl":
                                    $this->renderInputFileSokoInputControl($control);
                                    break;
                                default:
                                    echo "Unknown control renderer method for controlClass=$controlClass";
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>


                <!-- SUBMIT BUTTON -->


                <div uk-margin>
                    <button class="uk-button uk-button-primary"><?php echo $submitButtonText; ?></button>
                </div>


            </fieldset>
        </form>
        <?php
    }


    protected function renderSokoInputControl(array $control)
    {
        $type = $control['type'];
        $label = $control['label'];
        $errors = $control['errors'];
        $hasError = (count($errors) > 0);

        if ("textarea" === $type) {
            $this->renderTextareaSokoInputControl($control);
        } elseif ("file" === $type) {
            $this->renderInputFileSokoInputControl($control);
        } elseif ("hidden" === $type) {
            $this->renderHiddenSokoInputControl($control);
        } else {
            $this->renderInputSokoInputControl($control);
        }
    }


    protected function renderInputSokoInputControl(array $control)
    {
        $label = $control['label'];
        $errors = $control['errors'];
        $value = $control['value'];
        $name = $control['name'];
        $properties = $control['properties'];
        $hasError = (count($errors) > 0);

        $icon = $properties['icon'] ?? null;
        $iconPosition = $properties['iconPosition'] ?? "left";
        $iconIsClickable = $properties['iconIsClickable'] ?? true;
        $iconTag = (true === $iconIsClickable) ? 'a' : 'span';

        ?>
        <?php if ($icon): ?>
        <div class="uk-inline">
        <<?php echo $iconTag; ?> class="uk-form-icon
        <?php if ("right" === $iconPosition): ?>
            uk-form-icon-flip
        <?php endif; ?>
        <?php if ($hasError):
            /**
             * I would have liked to do that, but it's not available yet,
             * https://github.com/uikit/uikit/issues/3408
             */
            ?>
            uk-form-danger
        <?php endif; ?>
        "
        <?php if ($hasError): ?>
            style="color: red"
        <?php endif; ?>
        uk-icon="icon: <?php echo $icon; ?>"></<?php echo $iconTag; ?>>
    <?php endif; ?>
        <input class="uk-input
                    <?php if ($hasError): ?>
                    uk-form-danger
                    <?php endif; ?>
" type="text"
               name="<?php echo htmlspecialchars($name); ?>"
               value="<?php echo htmlspecialchars($value); ?>"
            <?php $this->extraAttributes('renderInputSokoInputControl', $control); ?>
               placeholder="<?php echo htmlspecialchars($label); ?>"
        >

        <?php if ($icon): ?>
        </div>
    <?php endif; ?>

        <?php
    }

    protected function renderInputFileSokoInputControl(array $control)
    {
        $type = $control['type'];
        if ("ajax" === $type) {
            $this->renderInputAjaxFileSokoInputControl($control);
        } else {
            $this->renderInputStaticFileSokoInputControl($control);
        }
    }


    protected function renderInputStaticFileSokoInputControl(array $control)
    {
        $uploadFileText = $control['uploadFileText'] ?? "Upload a file";
        $value = $control['value'];
        $name = $control['name'];
        $errors = $control['errors'];
        $hasError = (count($errors) > 0);
        ?>
        <div class="uk-form-custom">
            <input type="file"
                <?php $this->extraAttributes('renderInputStaticFileSokoInputControl', $control); ?>
                   name="<?php echo htmlspecialchars($name); ?>"
                   value="<?php echo htmlspecialchars($value); ?>">

            <button class="uk-button
            <?php if (true === $hasError): ?>
            uk-button-danger
            <?php endif; ?>
" type="button" tabindex="-1"><?php echo $uploadFileText; ?>
            </button>
        </div>
        <?php
    }


    protected function renderInputAjaxFileSokoInputControl(array $control)
    {
        $uploadFileTextPart1 = $control['uploadFileTextPart1'] ?? "Attach binaries by dropping them here or";
        $uploadFileTextPart2 = $control['uploadFileTextPart2'] ?? "selecting one";
        $cssId = StringTool::getUniqueCssId("uikit-soko-ajax-upload-");


        $value = $control['value'];
        $name = $control['name'];
        $errors = $control['errors'];
        $hasError = (count($errors) > 0);

        /**
         * @todo-ling: add hidden input holding the ajax loaded file (for form submission)
         */
        ?>
        <div
                id="<?php echo $cssId; ?>"
                class="js-upload uk-placeholder uk-text-center
<?php if (true === $hasError): ?>
uk-form-danger
<?php endif; ?>
">
            <span uk-icon="icon: cloud-upload"></span>
            <span class="uk-text-middle"><?php echo $uploadFileTextPart1; ?></span>
            <div uk-form-custom>
                <input type="file"
                    <?php
                    // note: this will probably be moved to another hidden input representing the real postable value of this control
                    $this->extraAttributes('renderInputAjaxFileSokoInputControl', $control); ?>
                       multiple>
                <span class="uk-link"><?php echo $uploadFileTextPart2; ?></span>
            </div>
        </div>


        <progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>

        <script>

            var bar = document.getElementById('js-progressbar');

            UIkit.upload('#<?php echo $cssId; ?>', {

                url: '',
                multiple: true,

                beforeSend: function () {
                    console.log('beforeSend', arguments);
                },
                beforeAll: function () {
                    console.log('beforeAll', arguments);
                },
                load: function () {
                    console.log('load', arguments);
                },
                error: function () {
                    console.log('error', arguments);
                },
                complete: function () {
                    console.log('complete', arguments);
                },

                loadStart: function (e) {
                    console.log('loadStart', arguments);

                    bar.removeAttribute('hidden');
                    bar.max = e.total;
                    bar.value = e.loaded;
                },

                progress: function (e) {
                    console.log('progress', arguments);

                    bar.max = e.total;
                    bar.value = e.loaded;
                },

                loadEnd: function (e) {
                    console.log('loadEnd', arguments);

                    bar.max = e.total;
                    bar.value = e.loaded;
                },

                completeAll: function () {
                    console.log('completeAll', arguments);

                    setTimeout(function () {
                        bar.setAttribute('hidden', 'hidden');
                    }, 1000);

                    alert('Upload Completed');
                }

            });

        </script>
        <?php
    }

    protected function renderHiddenSokoInputControl(array $control)
    {
        $value = $control['value'];
        $name = $control['name'];
        ?>
        <input type="hidden"
               name="<?php echo htmlspecialchars($name); ?>"
            <?php $this->extraAttributes('renderHiddenSokoInputControl', $control); ?>
               value="<?php echo htmlspecialchars($value); ?>">
        <?php
    }

    protected function renderTextareaSokoInputControl(array $control)
    {
        $label = $control['label'];
        $value = $control['value'];
        $controlName = $control['name'];
        $errors = $control['errors'];
        $hasError = (count($errors) > 0);
        ?>
        <textarea class="uk-textarea
            <?php if ($hasError): ?>
            uk-form-danger
            <?php endif; ?>
" rows="5"
                  name="<?php echo htmlspecialchars($controlName); ?>"
                  placeholder="<?php echo htmlspecialchars($label); ?>"
            <?php $this->extraAttributes('renderTextareaSokoInputControl', $control); ?>
        ><?php echo $value; ?></textarea>
        <?php
    }


    protected function renderSokoChoiceControl(array $control)
    {

        $properties = $control['properties'];
        $style = $properties['style'] ?? 'select';

        if ("select" === $style) {
            $this->renderSelectSokoChoiceControl($control);
        } elseif ("radio" === $style) {
            $this->renderRadioSokoChoiceControl($control);
        } elseif ("checkbox" === $style) {
            $this->renderCheckboxSokoChoiceControl($control);
        }
    }


    protected function renderSelectSokoChoiceControl(array $control)
    {
        $errors = $control['errors'];
        $choices = $control['choices'];
        $controlValue = $control['value'];
        $controlName = $control['name'];
        $hasError = (count($errors) > 0);
        ?>

        <select
                class="uk-select
<?php if ($hasError): ?>
uk-form-danger
<?php endif; ?>
"
                name="<?php echo htmlspecialchars($controlName); ?>"
            <?php $this->extraAttributes('renderSelectSokoChoiceControl', $control); ?>
        >
            <?php foreach ($choices as $value => $label):
                $sSel = ((string)$value === (string)$controlValue) ? 'selected="selected"' : "";
                ?>
                <option <?php echo $sSel; ?>
                        value="<?php echo htmlspecialchars($value); ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>


        <?php
    }


    protected function renderRadioSokoChoiceControl(array $control)
    {
        $choices = $control['choices'];
        $controlValue = $control['value'];
        $controlName = $control['name'];
        $properties = $control['properties'] ?? [];
        $br = $properties['br'] ?? false;

        ?>
        <?php
        $cpt = 0;
        foreach ($choices as $value => $label):
            $sChecked = ((string)$value === (string)$controlValue) ? 'checked' : '';
            ?>
            <label class="uk-text-small"><input class="uk-radio" type="radio" name="<?php echo htmlspecialchars($controlName); ?>"
                          value="<?php echo htmlspecialchars($value); ?>"
                    <?php $this->extraAttributes('renderRadioSokoChoiceControl', $control, $cpt); ?>
                    <?php echo $sChecked; ?>> <?php echo $label; ?></label>
            <?php if (true === $br): ?>
            <br>
        <?php endif; ?>
            <?php
            $cpt++;
        endforeach; ?>
        <?php
    }

    protected function renderCheckboxSokoChoiceControl(array $control)
    {
        $choices = $control['choices'];
        $controlValues = $control['value']; // an array of the selected values
        if (null === $controlValues) {
            $controlValues = [];
        }
        if (is_string($controlValues)) {
            $controlValues = [$controlValues];
        }
        $controlName = $control['name'];
        ?>
        <?php
        $cpt = 0;
        foreach ($choices as $value => $label):
            if (in_array($value, $controlValues, true)) {
                $sChecked = 'checked';
            } else {
                $sChecked = "";
            }
            ?>
            <label><input class="uk-checkbox"
                          type="checkbox"
                          name="<?php echo htmlspecialchars($controlName); ?>"
                          value="<?php echo htmlspecialchars($value); ?>"
                    <?php $this->extraAttributes('renderCheckboxSokoChoiceControl', $control, $cpt); ?>
                    <?php echo $sChecked; ?>> <?php echo $label; ?></label>


            <?php
            $cpt++;
        endforeach; ?>
        <?php
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    protected function renderNotifications(array $notifications, array $options = [])
    {

        $cssClass = $options['cssClass'] ?? null;

        foreach ($notifications as $notification):

            $type = $notification['type'];
            if ('info' === $type) {
                $type = "primary";
            } elseif ('error' === $type) {
                $type = "danger";
            }

            ?>
            <div class="uk-alert uk-alert-<?php echo $type; ?>
<?php if ($cssClass): ?>
<?php echo " " . $cssClass; ?>
<?php endif; ?>

">
                <!--                <a class="uk-alert-close" uk-close></a>-->
                <?php if ($notification['title']): ?>
                    <h6 class="uk-text-lead"><?php echo $notification['title']; ?></h6>
                <?php endif; ?>
                <p><?php echo $notification['msg']; ?></p>
            </div>
        <?php endforeach;

    }



    //--------------------------------------------
    //
    //--------------------------------------------
    protected function extraAttributes(string $methodName, array $control, $extra = null)
    {

    }
}