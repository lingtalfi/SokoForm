<?php


namespace SokoForm\Util;


class SokoUtil
{


    /**
     * This is a helper primarily designed for the SokoFormRenderer object.
     *
     * It injects/merges the css class into the attributes property of the
     * given preferences array.
     * If the attributes does not exist, it's created.
     *
     *
     * @param $cssClass
     * @param array $preferences
     */
    public static function addCssClassToPreferencesAttributes($cssClass, array &$preferences)
    {
        /**
         * Adding attributes, or merging if it's already set by the user.
         */
        $attr = [];
        if (array_key_exists("class", $preferences)) {
            if (array_key_exists("attributes", $preferences['class'])) {
                $attr = $preferences['class']['attributes'];
            }
        }
        if (!in_array($cssClass, $attr)) {
            $attr[] = $cssClass;
        }
        $preferences["attributes"] = $attr;
    }
}